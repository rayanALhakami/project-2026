<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Trip;
use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

#[Signature('notifications:dispatch')]
#[Description('Generate in-app notifications for upcoming trips, weather alerts, and nearby events.')]
class DispatchNotifications extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(WeatherService $weather): int
    {
        $today = Carbon::today('Asia/Riyadh');
        $reminders = 0;
        $weatherAlerts = 0;
        $nearbyEvents = 0;

        $eventsByCity = $this->upcomingEventsByCity($today);

        foreach (User::query()->whereHas('trips')->with(['trips.city'])->lazyById(100) as $user) {
            $reminders += $this->dispatchTripReminders($user, $today);
            $weatherAlerts += $this->dispatchWeatherAlerts($user, $today, $weather);
            $nearbyEvents += $this->dispatchNearbyEvents($user, $today, $eventsByCity);
        }

        $this->info("Dispatched notifications — trip reminders: {$reminders}, weather alerts: {$weatherAlerts}, nearby events: {$nearbyEvents}.");

        return self::SUCCESS;
    }

    /**
     * Notify the user about trips starting tomorrow through two days out.
     */
    private function dispatchTripReminders(User $user, Carbon $today): int
    {
        $created = 0;
        $windowStart = $today->copy()->addDay()->toDateString();
        $windowEnd = $today->copy()->addDays(2)->toDateString();

        foreach ($user->trips as $trip) {
            $startDate = $trip->start_date->toDateString();

            if ($startDate < $windowStart || $startDate > $windowEnd) {
                continue;
            }

            $alreadyNotified = Notification::query()
                ->whereBelongsTo($user)
                ->where('type', 'trip_reminder')
                ->where('created_at', '>=', now('Asia/Riyadh')->subDays(3))
                ->where('data->trip_id', $trip->id)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            Notification::query()->create([
                'user_id' => $user->id,
                'type' => 'trip_reminder',
                'title' => 'رحلتك قريبة ✈️',
                'body' => sprintf(
                    'رحلتك «%s» إلى %s تبدأ في %s.',
                    $trip->title,
                    $trip->city->name ?? 'المملكة',
                    $trip->start_date->toDateString(),
                ),
                'data' => ['trip_id' => $trip->id, 'url' => '/trips'],
                'read_at' => null,
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * Notify the user about extreme heat or storms in the cities of trips starting within three days.
     */
    private function dispatchWeatherAlerts(User $user, Carbon $today, WeatherService $weather): int
    {
        $created = 0;
        $checkedCityIds = [];
        $todayDate = $today->toDateString();
        $windowEnd = $today->copy()->addDays(3)->toDateString();

        foreach ($user->trips as $trip) {
            $startDate = $trip->start_date->toDateString();

            if ($startDate < $todayDate || $startDate > $windowEnd) {
                continue;
            }

            $cities = City::query()->whereIn('id', $this->cityIdsForTrip($trip))->get();

            foreach ($cities as $city) {
                if (isset($checkedCityIds[$city->id])) {
                    continue;
                }

                $checkedCityIds[$city->id] = true;

                try {
                    $reading = $weather->forCity($city);
                } catch (Throwable) {
                    continue;
                }

                $isStormy = in_array($reading['condition'], ['rain', 'thunder'], true);

                if ($reading['temperature'] < 42 && ! $isStormy) {
                    continue;
                }

                $alreadyNotified = Notification::query()
                    ->whereBelongsTo($user)
                    ->where('type', 'weather_alert')
                    ->where('created_at', '>=', now('Asia/Riyadh')->subHours(12))
                    ->where('data->city_id', $city->id)
                    ->exists();

                if ($alreadyNotified) {
                    continue;
                }

                Notification::query()->create([
                    'user_id' => $user->id,
                    'type' => 'weather_alert',
                    'title' => 'تنبيه طقس ⚠️',
                    'body' => sprintf(
                        'الطقس في %s: %d°م و%s.',
                        $city->name,
                        $reading['temperature'],
                        $this->arabicCondition($reading['condition']),
                    ),
                    'data' => ['city_id' => $city->id],
                    'read_at' => null,
                ]);

                $created++;
            }
        }

        return $created;
    }

    /**
     * Notify the user about events happening in the cities their upcoming trips cover.
     *
     * @param  array<int, list<Event>>  $eventsByCity
     */
    private function dispatchNearbyEvents(User $user, Carbon $today, array $eventsByCity): int
    {
        $created = 0;

        foreach ($this->tripCityIds($user, $today) as $cityId) {
            foreach ($eventsByCity[$cityId] ?? [] as $event) {
                if ($this->eventNotificationExists($user, $event)) {
                    continue;
                }

                Notification::query()->create([
                    'user_id' => $user->id,
                    'type' => 'nearby_event',
                    'title' => 'فعالية قريبة: '.$event->name,
                    'body' => sprintf(
                        '%s — تبدأ %s',
                        $event->city->name ?? 'المملكة',
                        $event->start_date->toDateString(),
                    ),
                    'data' => ['event_id' => $event->id],
                    'read_at' => null,
                ]);

                $created++;
            }
        }

        return $created;
    }

    /**
     * Get the upcoming events for the next seven days grouped by city.
     *
     * @return array<int, list<Event>>
     */
    private function upcomingEventsByCity(Carbon $today): array
    {
        $events = Event::query()
            ->whereDate('start_date', '>=', $today->toDateString())
            ->whereDate('start_date', '<=', $today->copy()->addDays(7)->toDateString())
            ->with('city')
            ->get();

        $eventsByCity = [];

        foreach ($events as $event) {
            if ($event->city_id !== null) {
                $eventsByCity[$event->city_id][] = $event;
            }
        }

        return $eventsByCity;
    }

    /**
     * Get the unique city ids covered by the user's upcoming or active trips.
     *
     * @return list<int>
     */
    private function tripCityIds(User $user, Carbon $today): array
    {
        /** @var array<int, true> $cityIds */
        $cityIds = [];
        $todayDate = $today->toDateString();

        foreach ($user->trips as $trip) {
            if ($trip->end_date->toDateString() < $todayDate) {
                continue;
            }

            foreach ($this->cityIdsForTrip($trip) as $cityId) {
                $cityIds[$cityId] = true;
            }
        }

        return array_keys($cityIds);
    }

    /**
     * Get the unique city ids covered by a trip.
     *
     * @return list<int>
     */
    private function cityIdsForTrip(Trip $trip): array
    {
        /** @var array<int, true> $cityIds */
        $cityIds = [];

        foreach ($trip->city_ids ?? [] as $cityId) {
            $cityIds[(int) $cityId] = true;
        }

        if ($trip->city_id !== null) {
            $cityIds[$trip->city_id] = true;
        }

        return array_keys($cityIds);
    }

    /**
     * Determine whether the user was ever notified about the event.
     */
    private function eventNotificationExists(User $user, Event $event): bool
    {
        return Notification::query()
            ->whereBelongsTo($user)
            ->where('type', 'nearby_event')
            ->where('data->event_id', $event->id)
            ->exists();
    }

    /**
     * Map a weather condition to its Arabic label.
     */
    private function arabicCondition(string $condition): string
    {
        return match ($condition) {
            'rain' => 'أمطار',
            'thunder' => 'عواصف رعدية',
            'hot' => 'حرارة مرتفعة',
            'cloudy' => 'غائم',
            'partly-cloudy' => 'غائم جزئياً',
            'fog' => 'ضباب',
            default => 'صحو',
        };
    }
}
