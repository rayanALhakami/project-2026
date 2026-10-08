<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateContactRequestPlan;
use App\Models\ContactRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;

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
     * Report the AI itinerary for a planning request, kicking off generation when needed.
     */
    public function plan(ContactRequest $contactRequest): JsonResponse
    {
        if ($contactRequest->plan !== null) {
            return response()->json([
                'status' => 'ready',
                'plan' => $contactRequest->plan,
            ]);
        }

        if (Cache::add(GenerateContactRequestPlan::lockKey($contactRequest), true, 300)) {
            dispatch(new GenerateContactRequestPlan($contactRequest))->afterResponse();
        }

        return response()->json(['status' => 'pending'], 202);
    }
}
