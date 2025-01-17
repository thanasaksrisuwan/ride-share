<?php

namespace App\Http\Controllers;

use App\Events\TripAccepted;
use App\Events\TripEnded;
use App\Events\TripLocationUpdated;
use App\Events\TripStarted;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function show(Request $request, Trip $trip)
    {
        if ($trip->user_id === $request->user()->id) {
            return $trip;
        }

        if ($trip->driver && $request->user()->driver) {
            if ($trip->driver->id === $request->user()->driver->id) {
                return $trip;
            }
        }

        return response()->json(['mesage' => 'Trip is not found'], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_location' => 'required|json',
            'end_location' => 'required|json',
            'destination_name' => 'required'
        ]);

        return $request->user()->trips()->create(
            $request->only([
                'start_location',
                'end_location',
                'destination_name'
            ])
        );
    }

    public function accept(Request $request, Trip $trip)
    {
        $request->validate([
            'driver_location' => 'required|json'
        ]);

        $trip->update([
            'driver_id' => $request->user()->id,
            'driver_location' => $request->driver_location
        ]);

        $trip->load('driver.user');

        TripAccepted::dispatch($trip, $request->user());

        return $trip;
    }

    public function start(Request $request, Trip $trip)
    {
        $trip->update([
            'is_started' => true
        ]);

        $trip->load('driver.user');

        TripStarted::dispatch($trip, $request->user());

        return $trip;
    }

    public function end(Request $request, Trip $trip)
    {
        $trip->update([
            'is_completed' => true
        ]);

        $trip->load('driver.user');

        TripEnded::dispatch($trip, $request->user());

        return $trip;
    }

    public function location(Request $request, Trip $trip)
    {
        $request->validate([
            'driver_location' => 'required|json'
        ]);

        $trip->update([
            'driver_location' => $request->driver_location
        ]);

        $trip->load('driver.user');

        TripLocationUpdated::dispatch($trip, $request->user());

        return $trip;
    }
}
