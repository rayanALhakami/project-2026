<?php

namespace App\Jobs;

use App\Ai\Agents\TouristGuide;
use App\Models\ContactRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateContactRequestPlan implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private ContactRequest $contactRequest) {}

    /**
     * Cache key guarding a single plan generation per request.
     */
    public static function lockKey(ContactRequest $contactRequest): string
    {
        return 'plan-request:'.$contactRequest->getKey();
    }

    /**
     * Generate the itinerary and store it on the planning request.
     */
    public function handle(): void
    {
        set_time_limit(300);

        if ($this->contactRequest->fresh()?->plan !== null) {
            Cache::forget(self::lockKey($this->contactRequest));

            return;
        }

        try {
            $plan = trim((string) (new TouristGuide)->prompt($this->planPrompt()));

            if ($plan !== '') {
                $this->contactRequest->forceFill([
                    'plan' => $plan,
                    'plan_generated_at' => now(),
                ])->save();
            }
        } catch (Throwable $exception) {
            Log::error('Plan request generation failed.', [
                'exception' => $exception,
                'contact_request_id' => $this->contactRequest->id,
            ]);
        } finally {
            Cache::forget(self::lockKey($this->contactRequest));
        }
    }

    /**
     * Build the prompt that asks the guide to plan the requested trip.
     */
    private function planPrompt(): string
    {
        $city = $this->contactRequest->city;
        $cityName = $city === null
            ? ($this->isArabic() ? 'غير محددة' : 'Not specified')
            : trim($city->name.' / '.$city->name_en, ' /');

        $details = $this->isArabic()
            ? [
                'اسم الزائر: '.$this->contactRequest->name,
                'المدينة: '.$cityName,
            ]
            : [
                'Visitor name: '.$this->contactRequest->name,
                'City: '.$cityName,
            ];

        if ($this->contactRequest->start_date !== null) {
            $details[] = ($this->isArabic() ? 'تاريخ الوصول: ' : 'Arrival date: ')
                .$this->contactRequest->start_date->toDateString();
        }

        if ($this->contactRequest->travelers !== null) {
            $details[] = ($this->isArabic() ? 'عدد المسافرين: ' : 'Travelers: ')
                .$this->contactRequest->travelers;
        }

        if ($this->contactRequest->budget !== null) {
            $details[] = ($this->isArabic() ? 'الميزانية الإجمالية: ' : 'Total budget: ')
                .number_format((float) $this->contactRequest->budget)
                .($this->isArabic() ? ' ريال سعودي' : ' SAR');
        }

        if (is_string($this->contactRequest->notes) && $this->contactRequest->notes !== '') {
            $details[] = ($this->isArabic() ? 'ملاحظات الزائر: ' : 'Visitor notes: ')
                .$this->contactRequest->notes;
        }

        $bulletList = implode("\n", array_map(
            fn (string $line): string => '- '.$line,
            $details,
        ));

        if ($this->isArabic()) {
            return <<<PROMPT
            وصلك طلب تخطيط رحلة من زائر عبر موقع "next trip". أنشئ له خطة رحلة سياحية في السعودية:

            {$bulletList}

            تعليمات مهمة:
            - استخدم أدواتك (البحث عن الأماكن، بناء الخطة اليومية، الطقس، الأسعار، أوقات الصلاة) للحصول على بيانات حقيقية، ولا تخترع أماكن أو أسعاراً.
            - إن لم يُحدد عدد الأيام في ملاحظات الزائر، اجعل الخطة ٣ أيام.
            - اكتب الخطة كاملة بالعربية، وابدأ بسطر ترحيبي قصير باسم الزائر.
            - رتّب الخطة بعنوان لكل يوم ثم أنشطة قصيرة مرقّمة (الوقت، المكان، السعر التقريبي)، وراعِ أوقات الصلاة والإغلاق يوم الجمعة والطقس.
            - اختم بتقدير مختصر للتكلفة الإجمالية ومقارنتها بالميزانية إن وُجدت.
            - اضبط طول الرد ليكون مناسباً للعرض في بطاقة على الموقع (لا تزد على ٤٠٠ كلمة تقريباً).
            PROMPT;
        }

        return <<<PROMPT
        A visitor submitted a planning request through the "next trip" website. Create a Saudi tourism itinerary for them:

        {$bulletList}

        Important instructions:
        - Use your tools (place search, itinerary builder, weather, prices, prayer times) to fetch real data, and never invent places or prices.
        - If the visitor notes do not specify a trip length, plan 3 days.
        - Write the entire plan in {$this->planLanguage()}, and start with a short welcoming line using the visitor's name.
        - Structure the plan with a heading per day and short numbered activities (time, place, approximate price), respecting prayer times, Friday closures, and the weather.
        - End with a brief total cost estimate and compare it with the budget when provided.
        - Keep the reply suitable for a card on the website (around 400 words maximum).
        PROMPT;
    }

    /**
     * Name of the language the generated plan should be written in.
     */
    private function planLanguage(): string
    {
        return match (app()->getLocale()) {
            'ar' => 'Arabic',
            'fr' => 'French',
            'es' => 'Spanish',
            'de' => 'German',
            'ru' => 'Russian',
            'tr' => 'Turkish',
            'zh' => 'Chinese',
            'hi' => 'Hindi',
            'ur' => 'Urdu',
            default => 'English',
        };
    }

    /**
     * Whether the visitor is browsing the Arabic interface.
     */
    private function isArabic(): bool
    {
        return app()->getLocale() === 'ar';
    }
}
