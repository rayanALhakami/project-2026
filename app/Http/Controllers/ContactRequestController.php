<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TouristGuide;
use App\Models\ContactRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Throwable;

class ContactRequestController extends Controller
{
    /**
     * Store a planning request submitted from the landing page.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'travelers' => ['nullable', 'integer', 'min:1', 'max:30'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $contactRequest = ContactRequest::query()->create([
            ...$validated,
            'token' => (string) Str::uuid(),
        ]);

        Inertia::flash('planRequest', ['token' => $contactRequest->token]);

        return back();
    }

    /**
     * Generate (once) the AI itinerary for a submitted planning request.
     */
    public function plan(ContactRequest $contactRequest): JsonResponse
    {
        set_time_limit(300);

        if ($contactRequest->plan !== null) {
            return response()->json(['plan' => $contactRequest->plan]);
        }

        try {
            $plan = trim((string) (new TouristGuide)->prompt($this->planPrompt($contactRequest)));
        } catch (Throwable $exception) {
            Log::error('Plan request generation failed.', [
                'exception' => $exception,
                'contact_request_id' => $contactRequest->id,
            ]);

            return $this->generationError();
        }

        if ($plan === '') {
            return $this->generationError();
        }

        $contactRequest->forceFill([
            'plan' => $plan,
            'plan_generated_at' => now(),
        ])->save();

        return response()->json(['plan' => $plan]);
    }

    /**
     * Build the prompt that asks the guide to plan the requested trip.
     */
    private function planPrompt(ContactRequest $contactRequest): string
    {
        $city = $contactRequest->city;
        $cityName = $city === null
            ? ($this->isArabic() ? 'غير محددة' : 'Not specified')
            : trim($city->name.' / '.$city->name_en, ' /');

        $details = $this->isArabic()
            ? [
                'اسم الزائر: '.$contactRequest->name,
                'المدينة: '.$cityName,
            ]
            : [
                'Visitor name: '.$contactRequest->name,
                'City: '.$cityName,
            ];

        if ($contactRequest->start_date !== null) {
            $details[] = ($this->isArabic() ? 'تاريخ الوصول: ' : 'Arrival date: ')
                .$contactRequest->start_date->toDateString();
        }

        if ($contactRequest->travelers !== null) {
            $details[] = ($this->isArabic() ? 'عدد المسافرين: ' : 'Travelers: ')
                .$contactRequest->travelers;
        }

        if ($contactRequest->budget !== null) {
            $details[] = ($this->isArabic() ? 'الميزانية الإجمالية: ' : 'Total budget: ')
                .number_format((float) $contactRequest->budget)
                .($this->isArabic() ? ' ريال سعودي' : ' SAR');
        }

        if (is_string($contactRequest->notes) && $contactRequest->notes !== '') {
            $details[] = ($this->isArabic() ? 'ملاحظات الزائر: ' : 'Visitor notes: ')
                .$contactRequest->notes;
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

    /**
     * JSON response used when the itinerary could not be generated.
     */
    private function generationError(): JsonResponse
    {
        return response()->json([
            'error' => $this->isArabic()
                ? 'عذراً، تعذر إنشاء خطتك الآن. جرّب مرة أخرى بعد قليل.'
                : 'Sorry, we could not generate your plan right now. Please try again shortly.',
        ], 503);
    }
}
