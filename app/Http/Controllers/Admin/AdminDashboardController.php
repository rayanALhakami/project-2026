<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Event;
use App\Models\Place;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Show the admin overview with content counts.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'places' => Place::query()->count(),
                'cities' => City::query()->count(),
                'events' => Event::query()->count(),
                'reviews' => Review::query()->count(),
                'users' => User::query()->count(),
                'trips' => Trip::query()->count(),
            ],
        ]);
    }
}
