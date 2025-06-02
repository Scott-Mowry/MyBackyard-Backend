<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
class DashboardController extends Controller
{
    public function getDashboard()
    {
        $user = Auth::user();
        $totalUsers =
            $user->isSubAdmin() ?
            User::join('zip_codes', function ($join) use ($user) {
                $join->on('zip_codes.user_id', '=', DB::raw($user->id)); })->whereRaw('3959 * acos(cos(radians(zip_codes.latitude)) * cos(radians(users.latitude)) * cos(radians(users.longitude) - radians(zip_codes.longitude)) + sin(radians(zip_codes.latitude)) * sin(radians(users.latitude))) <= 7')->select('users.*')->groupBy('users.id')->get()->count() :
            User::count();
        $totalActiveUsers =
            $user->isSubAdmin() ?
            User::join('zip_codes', function ($join) use ($user) {
                $join->on('zip_codes.user_id', '=', DB::raw($user->id)); })->whereRaw('3959 * acos(cos(radians(zip_codes.latitude)) * cos(radians(users.latitude)) * cos(radians(users.longitude) - radians(zip_codes.longitude)) + sin(radians(zip_codes.latitude)) * sin(radians(users.latitude))) <= 7')->select('users.*')->where('users.is_blocked', '0')->groupBy('users.id')->get()->count() :
            User::whereIsBlocked(0)->count();
        $totalInactiveUsers =
            $user->isSubAdmin() ?
            User::join('zip_codes', function ($join) use ($user) {
                $join->on('zip_codes.user_id', '=', DB::raw($user->id)); })->whereRaw('3959 * acos(cos(radians(zip_codes.latitude)) * cos(radians(users.latitude)) * cos(radians(users.longitude) - radians(zip_codes.longitude)) + sin(radians(zip_codes.latitude)) * sin(radians(users.latitude))) <= 7')->select('users.*')->where('users.is_blocked', '1')->groupBy('users.id')->get()->count() :
            User::whereIsBlocked(1)->count();
        $totalpayingCustomer =
            $user->isSubAdmin() ?
            User::join('zip_codes', function ($join) use ($user) {
                $join->on('zip_codes.user_id', '=', DB::raw($user->id)); })->whereRaw('3959 * acos(cos(radians(zip_codes.latitude)) * cos(radians(users.latitude)) * cos(radians(users.longitude) - radians(zip_codes.longitude)) + sin(radians(zip_codes.latitude)) * sin(radians(users.latitude))) <= 7')->select('users.*')->where('users.is_blocked', '0')->whereNotNull('users.sub_id')->groupBy('users.id')->get()->count() :
            User::whereNotNull('sub_id')->whereIsBlocked(0)->count();
        return view('Admin.Pages.Dashboard.index', compact('totalUsers', 'totalActiveUsers', 'totalInactiveUsers', 'totalpayingCustomer'));
    }
}

/*
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
class DashboardController extends Controller
{
    public function getDashboard()
    {
        $totalUsers = User::whereRole('User')->count();
        $totalActiveUsers = User::whereRole('User')->whereIsBlocked(0)->count();
        $totalInactiveUsers = User::whereRole('User')->whereIsBlocked(1)->count();
        $totalpayingCustomer = User::whereNotNull('sub_id')->whereIsBlocked(0)->count();
        return view('Admin.Pages.Dashboard.index', compact('totalUsers', 'totalActiveUsers', 'totalInactiveUsers', 'totalpayingCustomer'));
    }
}

*/
