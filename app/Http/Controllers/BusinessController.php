<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class BusinessController extends BaseController
{
    //
    // public function getBus(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'bus_id' => 'required'
    //     ]);
    //     if ($validator->fails()) {
    //         return $this->sendError($validator->errors()->first());
    //     }
    //     $businesses = User::find($request->bus_id);
    //     if ($businesses != null) {
    //         return $this->sendResponse(array(
    //             "businesses" => $businesses
    //         ), 'All Businesses Fetched');
    //     } else {
    //         return $this->sendError("Unable to process your request at the moment.");
    //     }
    // }

    public function getBuses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'long' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        $page = $request->page;
        $radius = $request->radius ?? 10.0;
        $latitude = $request->lat;
        $longitude = $request->long;
        $businesses = User::nearbyBusinesses($latitude, $longitude, $radius, $page)->get();

        $businesses = $businesses->map(function ($bus) {
            $days = Schedule::where('owner_id', $bus->id)->get();
            $bus["days"] = $days;
            return $bus;
        });

        if ($businesses != null) {
            return $this->sendResponse(array(
                "businesses" => $businesses
            ), 'All Businesses Fetched');
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }
}
