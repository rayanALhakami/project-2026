<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        ContactRequest::query()->create($validated);

        return back();
    }
}
