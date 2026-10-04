<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminEventController extends Controller
{
    /**
     * List the events available for administration.
     */
    public function index(): Response
    {
        $events = Event::query()
            ->with('city')
            ->orderByDesc('start_date')
            ->get()
            ->map(fn (Event $event): array => [
                'id' => $event->id,
                'city_id' => $event->city_id,
                'city_name' => $event->city?->name,
                'name' => $event->name,
                'description' => $event->description,
                'start_date' => $event->start_date->toDateString(),
                'end_date' => $event->end_date?->toDateString(),
                'image' => $event->image,
                'url' => $event->url,
            ])
            ->all();

        return Inertia::render('Admin/Events', [
            'events' => $events,
        ]);
    }

    /**
     * Store a new event.
     */
    public function store(Request $request): RedirectResponse
    {
        Event::query()->create($this->validated($request));

        return back();
    }

    /**
     * Update an existing event.
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $event->update($this->validated($request));

        return back();
    }

    /**
     * Delete an event.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return back();
    }

    /**
     * Validate the event payload.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'image' => ['nullable', 'string', 'max:2048'],
            'url' => ['nullable', 'url', 'max:2048'],
        ]);
    }
}
