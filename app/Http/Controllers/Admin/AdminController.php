<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AdminController extends Controller
{

  public function adminLoginForm()
  {
    if (auth()->check()) {
      // Redirect to the dashboard
      return redirect()->route('admin.dashboard');
    }
    return view('Admin.auth.login');
  }

  public function adminLoginSubmit(Request $request)
  {

    $valid = Validator::make($request->all(), [
      'email' => 'required|exists:users,email',
    ]);
    if ($valid->fails()) {
      return redirect()->back()->withInput()->withErrors($valid);
    }

    $credentials = ['email' => $request->email, 'password' => $request->password];

    if (Auth::attempt($credentials) && (Auth::user()->role == 'Admin' || Auth::user()->is_sub_admin == '1')) {
      $request->session()->regenerate();
      return redirect()->route('admin.dashboard');
    } else {
      return redirect()->back()->withInput()->with('error_msg', 'Invalid Password');
    }
  }

  public function adminLogout(Request $request)
  {
    Auth::logout(); // Log the user out

    $request->session()->invalidate(); // Invalidate the user's session

    $request->session()->regenerateToken(); // Regenerate the CSRF token

    return redirect()->route('admin.login.form');
  }
}
