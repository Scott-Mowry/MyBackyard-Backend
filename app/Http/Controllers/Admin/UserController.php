<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Notifications\UserNotification;
class UserController extends Controller
{
    public function getUsers(Request $request)
    {

        if ($request->ajax()) {
            $user = Auth::user();
            $data =
                $user->isSubAdmin() ?

                User::join('zip_codes', function ($join) use ($user) {
                    $join->on('zip_codes.user_id', '=', DB::raw($user->id));
                })
                    ->whereRaw('3959 * acos(cos(radians(zip_codes.latitude)) * cos(radians(users.latitude)) * cos(radians(users.longitude) - radians(zip_codes.longitude)) + sin(radians(zip_codes.latitude)) * sin(radians(users.latitude))) <= 7')
                    ->select('users.*')
                    ->groupBy('users.id')
                    ->get()
                :

                User::select('*')->whereRole('User')->orderBy('id', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $block = route('admin.users.action', ['id' => $row->id, 'status' => 1]);
                    $unblock = route('admin.users.action', ['id' => $row->id, 'status' => 0]);
                    $view = route('admin.users.details', ['id' => $row->id]);

                    if ($row->is_blocked == 0) {
                        $actionBtn = '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                        Actions
                    </button>
                    <div class="dropdown-menu">
                        <a href="' . $block . '" class="dropdown-item">Block</a>
                        <a href="' . $view . '" class="dropdown-item">View</a>
                    </div>
                </div>';
                    } else {
                        $actionBtn = '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" " type="button" data-toggle="dropdown">
                        Actions
                    </button>
                    <div class="dropdown-menu">
                        <a href="' . $unblock . '" class="dropdown-item">Unblock</a>
                        <a href="' . $view . '" class="dropdown-item">View</a>
                    </div>
                </div>';
                    }
                    return $actionBtn;
                })->addColumn('status', function ($row) {
                    return ($row->is_blocked == 1) ? 'Block' : 'Active';
                })
                ->addColumn('name', function ($row) {
                    return $row->name ?? '---';
                })
                // ->filterColumn('name', function ($query, $keyword) {
                //     $query->where('name', 'like', '%' . $keyword . '%');
                // })
                ->addColumn('email', function ($row) {
                    return $row->email ?? '---';
                })
                // ->filterColumn('email', function ($query, $keyword) {
                //     $query->where('email', 'like', '%' . $keyword . '%');
                // })
                ->addColumn('phone', function ($row) {
                    return $row->phone ?? '---';
                })
                // ->filterColumn('phone', function ($query, $keyword) {
                //     $query->where('phone', 'like', '%' . $keyword . '%');
                // })

                ->rawColumns(['action', 'status'])
                ->make(true);
        }


        return view('Admin.Pages.User.Listing');
    }

    public function getSubscribedUsers(Request $request)
    {

        if ($request->ajax()) {
            $user = Auth::user();
            $data =
                $user->isSubAdmin() ?

                User::join('zip_codes', function ($join) use ($user) {
                    $join->on('zip_codes.user_id', '=', DB::raw($user->id));
                })
                    ->whereRaw('3959 * acos(cos(radians(zip_codes.latitude)) * cos(radians(users.latitude)) * cos(radians(users.longitude) - radians(zip_codes.longitude)) + sin(radians(zip_codes.latitude)) * sin(radians(users.latitude))) <= 7')
                    ->select('users.*')
                    ->whereNotNull('users.sub_id')
                    ->groupBy('users.id')
                    ->get()
                :
                User::select('*')->whereNotNull('sub_id')->whereNotNull('email')->orderBy('id')
            ;
            return DataTables::of($data)
                ->addIndexColumn()
                // ->addColumn('action', function ($row) {
                //     $actionBtn = '<button class="btn btn-secondary" type="button">
                //         Delete
                //     </button>';
                //     return $actionBtn;
                // })
                ->addColumn('status', function ($row) {
                    return ($row->is_blocked == 1) ? 'InActive' : 'Active';
                })
                ->addColumn('role', function ($row) {
                    return $row->role ?? '---';
                })
                // ->filterColumn('role', function ($query, $keyword) {
                //     $query->where('role', 'like', '%' . $keyword . '%');
                // })
                ->addColumn('name', function ($row) {
                    switch ($row->role) {
                        case 'User':
                            $name = ($row->name ?? "---") . " " . ($row->last_name ?? "");
                            return $name;
                        default:
                            return $row->name ?? "---";
                    }
                })
                // ->filterColumn('name', function ($query, $keyword) {
                //     // $query->where('name', 'like', '%' . $keyword . '%');
                //     $query->where(function ($q) use ($keyword) {
                //         $q->where(function ($subQuery) use ($keyword) {
                //             $subQuery->where('role', 'User')
                //                 ->whereRaw("CONCAT(COALESCE(name, '---'), ' ', COALESCE(last_name, '')) LIKE ?", ["%{$keyword}%"]);
                //         })
                //             ->orWhere(function ($subQuery) use ($keyword) {
                //                 $subQuery->where('role', '!=', 'User')
                //                     ->where('name', 'LIKE', "%{$keyword}%");
                //             });
                //     });
                // })
                ->addColumn('email', function ($row) {
                    return $row->email ?? '---';
                })
                // ->filterColumn('email', function ($query, $keyword) {
                //     $query->where('email', 'like', '%' . $keyword . '%');
                // })
                ->addColumn('price', function ($row) {
                    switch ($row->sub_id) {
                        case 1:
                            return '$1.99';
                        case 2:
                            return '$99.99';
                        case 3:
                            return '$999.99';
                        case 4:
                            return '$9.99';
                        default:
                            return '---';
                    }
                })
                // ->filterColumn('price', function ($query, $keyword) {
                //     $subUserId = 0;
                //     switch ($keyword) {
                //         case 1.99:
                //             $subUserId = 1;
                //             break;
                //         case 99.99:
                //             $subUserId = 2;
                //             break;
                //         case 999.99:
                //             $subUserId = 3;
                //             break;
                //         case 9.99:
                //             $subUserId = 4;
                //             break;
                //         default:
                //             $subUserId = 0;
                //             break;
                //     }
                //     $query->where('sub_id', $subUserId);
                // })
                ->addColumn('type', function ($row) {
                    switch ($row->sub_id) {
                        case 1:
                            return 'Annual';
                        case 2:
                            return 'Monthly';
                        case 3:
                            return 'Annual';
                        case 4:
                            return 'Monthly';
                        default:
                            return '---';
                    }
                })
                // ->filterColumn('type', function ($query, $keyword) {
                //     $subUserId = 0;
                //     switch ($keyword) {
                //         case 1.99:
                //             $subUserId = 1;
                //             break;
                //         case 99.99:
                //             $subUserId = 2;
                //             break;
                //         case 999.99:
                //             $subUserId = 3;
                //             break;
                //         case 9.99:
                //             $subUserId = 4;
                //             break;
                //         default:
                //             $subUserId = 0;
                //             break;
                //     }
                //     $query->where('sub_id', $subUserId);
                // })
                ->rawColumns([
                    // 'action',
                    'status',
                    'type'
                ])
                ->make(true);
        }
        return view('Admin.Pages.SubscribedUsers.Listing');
    }


    public function actionPlaceById($id, $status)
    {
        $user = User::find($id);
        $update = $user->update(['is_blocked' => $status]);
        if ($update) {
            if ($status == 1) {
                $user->tokens()->delete();
                return redirect()->back()->with('success', 'Account has been blocked.');
            } else {
                return redirect()->back()->with('success', 'Account has been unblocked.');
            }
        } else {
            return redirect()->back()->with('error', 'Server Error.');
        }
    }



    public function getUserById($id)
    {
        $user = User::find($id);
        return view('Admin.Pages.User.Detail', compact('user'));
    }









}
