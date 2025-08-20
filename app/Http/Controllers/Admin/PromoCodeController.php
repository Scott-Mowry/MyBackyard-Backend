<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use App\Models\User;
use Illuminate\Http\Request;
use DataTables;

class PromoCodeController extends Controller
{
    public function getCodes(Request $request)
    {

        if ($request->ajax()) {
            $data = Promocode::select('*')->orderBy('id', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $delete = route('admin.promocodes.destroy', ['id' => $row->id]);
                    $actionBtn = '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                        Actions
                    </button>
                    <div class="dropdown-menu">
                        <a href="' . $delete . '" class="dropdown-item">Delete</a>
                    </div>
                </div>';
                    return $actionBtn;
                })
                ->addColumn('claimed_by', function ($row) {
                    if ($row->claimed_by == null) {
                        return 'No';
                    } else {
                        return User::findOrFail($row->claimed_by)->name ?? 'Unknown';
                    }
                })
                ->addColumn('sub_duration', function ($row) {
                    return $row->sub_duration ?? '---';
                })
                ->addColumn('code', function ($row) {
                    return $row->code ?? '---';
                })->filterColumn('code', function ($query, $keyword) {
                    $query->where('code', 'like', '%' . $keyword . '%');
                })->rawColumns(['action', 'sub_duration'])
                ->make(true);
        }

        return view('Admin.Pages.PromoCodes.Listing');
    }

    public function addPromoCodeForm()
    {
        return view('Admin.Pages.PromoCodes.Add');
    }

    public function addPromoCode(Request $request)
    {

        try {
            $add = new Promocode();
            $add->code = $request->code;
            $add->sub_duration = $request->sub_duration;
            switch ($request->role) {
                case 'user':
                    $add->subscription_id = 1;
                    break;

                case 'business':
                    $add->subscription_id = 2;
                    break;

                default:
                    $add->subscription_id = 1; // Default to user if no role is specified
                    break;
            }
            $add->save();
            return redirect()->back()->with('success', 'Promo Code has been added Successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error in adding Promo Code.');
        }
    }

    public function promoCodeDelete($id)
    {
        $promoCode = Promocode::findOrFail($id);
        $promoCode->delete();
        return redirect()->back()->with('success', 'PromoCode deleted successfully.');
    }

    // API v2 Functions
    public function getCodesv2(Request $request)
    {
        $data = Promocode::select('*')->orderBy('id', 'desc')->get();

        $promoCodes = $data->map(function ($row) {
            $claimedBy = 'No';
            if ($row->claimed_by != null) {
                $user = User::find($row->claimed_by);
                $claimedBy = $user ? $user->name : 'Unknown';
            }

            return [
                'id' => $row->id,
                'code' => $row->code ?? '---',
                'sub_duration' => $row->sub_duration ?? '---',
                'subscription_id' => $row->subscription_id,
                'claimed_by' => $claimedBy,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Promo codes retrieved successfully',
            'data' => $promoCodes
        ], 200);
    }

    public function addPromoCodev2(Request $request)
    {
        try {
            $add = new Promocode();
            $add->code = $request->code;
            $add->sub_duration = $request->sub_duration;

            switch ($request->role) {
                case 'user':
                    $add->subscription_id = 1;
                    break;
                case 'business':
                    $add->subscription_id = 2;
                    break;
                default:
                    $add->subscription_id = 1; // Default to user if no role is specified
                    break;
            }

            $add->save();

            return response()->json([
                'status' => true,
                'message' => 'Promo Code has been added successfully',
                'data' => [
                    'id' => $add->id,
                    'code' => $add->code,
                    'sub_duration' => $add->sub_duration,
                    'subscription_id' => $add->subscription_id,
                    'claimed_by' => null
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error in adding Promo Code'
            ], 500);
        }
    }

    public function promoCodeDeletev2($id)
    {
        $promoCode = Promocode::find($id);

        if (!$promoCode) {
            return response()->json([
                'status' => false,
                'message' => 'Promo code not found'
            ], 404);
        }

        try {
            $promoCode->delete();
            return response()->json([
                'status' => true,
                'message' => 'Promo code deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting promo code'
            ], 500);
        }
    }
}
