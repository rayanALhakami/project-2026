<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Support\TripPayload;
use Inertia\Inertia;
use Inertia\Response;

class SharedTripController extends Controller
{
    /**
     * Show a publicly shared trip by its share token.
     */
    public function show(string $token): Response
    {
        $trip = Trip::query()
            ->where('share_token', $token)
            ->where('is_public', true)
            ->firstOrFail();

        return Inertia::render('SharedTrip', [
            'trip' => TripPayload::forTrip($trip),
        ]);
    }
}
