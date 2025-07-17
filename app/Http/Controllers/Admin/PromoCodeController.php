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
}
