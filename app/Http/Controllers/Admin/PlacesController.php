<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use App\Models\places;

class PlacesController extends Controller
{
    public function getPlaces(Request $request)
    {

        if ($request->ajax()) {
            $data = places::select('*')->orderBy('id', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $allowed = route('admin.places.action', ['id' => $row->id, 'status' => 1]);
                    $notAllowed = route('admin.places.action', ['id' => $row->id, 'status' => 0]);
                    $delete = route('admin.places.destroy', ['id' => $row->id]);

                    if ($row->is_allowed == 0) {
                        $actionBtn = '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                        Actions
                    </button>
                    <div class="dropdown-menu">
                        <a href="' . $allowed . '" class="dropdown-item">Allowed</a>
                        <a href="' . $delete . '" class="dropdown-item">Delete</a>
                    </div>
                </div>';
                    } else {
                        $actionBtn = '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" " type="button" data-toggle="dropdown">
                        Actions
                    </button>
                    <div class="dropdown-menu">
                        <a href="' . $notAllowed . '" class="dropdown-item">Block</a>
                        <a href="' . $delete . '" class="dropdown-item">Delete</a>
                    </div>
                </div>';
                    }
                    return $actionBtn;
                })->addColumn('status', function ($row) {
                    return ($row->is_allowed == 1) ? 'Allowed' : 'Not Allowed';
                })->addColumn('name', function ($row) {
                    return $row->name ?? '---';
                })->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%');
                })->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('Admin.Pages.Places.Listing');
    }

    public function actionPlaceById($id, $status)
    {
        $place = places::find($id);
        $update = $place->update(['is_allowed' => $status]);
        if ($update) {
            if ($status == 1) {
                return redirect()->back()->with('success', 'Place has been Allowed.');
            } else {
                return redirect()->back()->with('success', 'Place has been Blocked.');
            }
        } else {
            return redirect()->back()->with('error', 'Server Error.');
        }
    }

    public function addPlaceForm()
    {
        return view('Admin.Pages.Places.Add');
    }

    public function addPlace(Request $request)
    {

        try {
            $add = new places();
            $add->name = $request->name;
            $add->top_left_latitude = $request->top_left_latitude;
            $add->top_left_longitude = $request->top_left_longitude;
            $add->bottom_right_latitude = $request->bottom_right_latitude;
            $add->bottom_right_longitude = $request->bottom_right_longitude;
            $add->is_allowed = 1;
            $add->save();
            return redirect()->back()->with('success', 'Place has been added Successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error in adding place.');
        }
    }

    public function placeDelete($id)
    {
        $place = places::findOrFail($id);
        $place->delete();
        return redirect()->back()->with('success', 'Place deleted successfully.');
    }

    // API v2 Functions
    public function getPlacesv2(Request $request)
    {
        $data = places::select('*')->orderBy('id', 'desc')->get();

        $places = $data->map(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name ?? '---',
                'top_left_latitude' => $row->top_left_latitude,
                'top_left_longitude' => $row->top_left_longitude,
                'bottom_right_latitude' => $row->bottom_right_latitude,
                'bottom_right_longitude' => $row->bottom_right_longitude,
                'status' => ($row->is_allowed == 1) ? 'Allowed' : 'Not Allowed',
                'is_allowed' => $row->is_allowed,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Places retrieved successfully',
            'data' => $places
        ], 200);
    }

    public function actionPlaceByIdv2($id, $status)
    {
        $place = places::find($id);

        if (!$place) {
            return response()->json([
                'status' => false,
                'message' => 'Place not found'
            ], 404);
        }

        $update = $place->update(['is_allowed' => $status]);

        if ($update) {
            if ($status == 1) {
                return response()->json([
                    'status' => true,
                    'message' => 'Place has been allowed successfully'
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Place has been blocked successfully'
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Server Error'
            ], 500);
        }
    }

    public function addPlacev2(Request $request)
    {
        try {
            $add = new places();
            $add->name = $request->name;
            $add->top_left_latitude = $request->top_left_latitude;
            $add->top_left_longitude = $request->top_left_longitude;
            $add->bottom_right_latitude = $request->bottom_right_latitude;
            $add->bottom_right_longitude = $request->bottom_right_longitude;
            $add->is_allowed = 1;
            $add->save();

            return response()->json([
                'status' => true,
                'message' => 'Place has been added successfully',
                'data' => [
                    'id' => $add->id,
                    'name' => $add->name,
                    'top_left_latitude' => $add->top_left_latitude,
                    'top_left_longitude' => $add->top_left_longitude,
                    'bottom_right_latitude' => $add->bottom_right_latitude,
                    'bottom_right_longitude' => $add->bottom_right_longitude,
                    'is_allowed' => $add->is_allowed
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error in adding place'
            ], 500);
        }
    }

    public function placeDeletev2($id)
    {
        $place = places::find($id);

        if (!$place) {
            return response()->json([
                'status' => false,
                'message' => 'Place not found'
            ], 404);
        }

        try {
            $place->delete();
            return response()->json([
                'status' => true,
                'message' => 'Place deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting place'
            ], 500);
        }
    }
}
