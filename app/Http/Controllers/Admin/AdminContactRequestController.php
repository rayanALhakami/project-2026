<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminContactRequestController extends Controller
{
    /**
     * List the planning requests submitted from the landing page.
     */
    public function index(): Response
    {
        $requests = ContactRequest::query()
            ->with('city')
            ->latest()
            ->get()
            ->map(fn (ContactRequest $contactRequest): array => [
                'id' => $contactRequest->id,
                'name' => $contactRequest->name,
                'phone' => $contactRequest->phone,
                'city_name' => $contactRequest->city?->name,
                'start_date' => $contactRequest->start_date?->toDateString(),
                'travelers' => $contactRequest->travelers,
                'budget' => $contactRequest->budget,
                'notes' => $contactRequest->notes,
                'handled' => $contactRequest->handled_at !== null,
                'created_at' => $contactRequest->created_at?->toDateString(),
            ])
            ->all();

        return Inertia::render('Admin/Requests', [
            'requests' => $requests,
        ]);
    }

    /**
     * Toggle the handled state of a planning request.
     */
    public function toggleHandled(ContactRequest $contactRequest): RedirectResponse
    {
        $contactRequest->handled_at = $contactRequest->handled_at === null ? now() : null;
        $contactRequest->save();

        return back();
    }

    /**
     * Delete a planning request.
     */
    public function destroy(ContactRequest $contactRequest): RedirectResponse
    {
        $contactRequest->delete();

        return back();
    }
}
