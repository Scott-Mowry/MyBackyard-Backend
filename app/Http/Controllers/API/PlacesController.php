<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseController;
use App\Models\places;
use Illuminate\Http\Request;

class PlacesController extends BaseController
{
    public function index(Request $request)
    {
        $places = places::where('is_allowed', '1')->get();
        if ($places != null) {
            return $this->sendResponse(
                $places
                ,
                'All Places Fetched'
            );
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }
}
