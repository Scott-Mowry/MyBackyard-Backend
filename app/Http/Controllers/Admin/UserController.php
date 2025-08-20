<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
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

    // API v2 Functions
    public function getUsersv2(Request $request)
    {
        $user = Auth::user();

        // Get role filter from request
        $roleFilter = $request->get('role', 'all');

        $query = User::query();

        // Apply role filter if specified
        if ($roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        $data = $user->isSubAdmin() ?
            $query->join('zip_codes', function ($join) use ($user) {
                $join->on('zip_codes.user_id', '=', DB::raw($user->id));
            })
                ->whereRaw('3959 * acos(cos(radians(zip_codes.latitude)) * cos(radians(users.latitude)) * cos(radians(users.longitude) - radians(zip_codes.longitude)) + sin(radians(zip_codes.latitude)) * sin(radians(users.latitude))) <= 7')
                ->select('users.*')
                ->groupBy('users.id')
                ->get()
            :
            $query->orderBy('id', 'desc')->get();

        $users = $data->map(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'email' => $row->email,
                'phone' => $row->phone,
                'role' => $row->role,
                'status' => ($row->is_blocked == 1) ? 'Block' : 'Active',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'email_otp' => $row->email_otp,
                'email_verified_at' => $row->email_verified_at,
                'device_type' => $row->device_type,
                'device_token' => $row->device_token,
                'social_type' => $row->social_type,
                'social_token' => $row->social_token,
                'last_name' => $row->last_name,
                'zip_code' => $row->zip_code,
                'address' => $row->address,
                'latitude' => $row->latitude,
                'longitude' => $row->longitude,
                'category_id' => $row->category_id,
                'description' => $row->description,
                'customer_profile_id' => $row->customer_profile_id,
                'payment_profile_id' => $row->payment_profile_id,
                'profile_image' => $row->profile_image,
                'is_forgot' => $row->is_forgot,
                'is_verified' => $row->is_verified,
                'is_blocked' => $row->is_blocked,
                'is_push_notify' => $row->is_push_notify,
                'is_profile_completed' => $row->is_profile_completed,
                'sub_id' => $row->sub_id,
                'is_sub_admin' => $row->is_sub_admin,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Users retrieved successfully',
            'data' => $users
        ], 200);
    }

    public function getSubscribedUsersv2(Request $request)
    {
        // Get all users with subscriptions (both User and Business roles)
        $data = User::whereNotNull('sub_id')
            ->orderBy('id', 'desc')
            ->get();

        $subscribedUsers = $data->map(function ($row) {
            $type = '---';
            $price = '---';

            switch ($row->sub_id) {
                case 1:
                    $type = 'Annual';
                    $price = '$1.99';
                    break;
                case 2:
                    $type = 'Monthly';
                    $price = '$99.99';
                    break;
                case 3:
                    $type = 'Annual';
                    $price = '$1.99';
                    break;
                case 4:
                    $type = 'Monthly';
                    $price = '$99.99';
                    break;
            }

            return [
                'id' => $row->id,
                'role' => $row->role ?? '---',
                'name' => $row->name ?? '---',
                'email' => $row->email ?? '---',
                'price' => $price,
                'type' => $type,
                'status' => ($row->is_blocked == 1) ? 'InActive' : 'Active',
                'sub_id' => $row->sub_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Subscribed users retrieved successfully',
            'data' => $subscribedUsers
        ], 200);
    }

    public function getUserByIdv2($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'User details retrieved successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => ($user->is_blocked == 1) ? 'Block' : 'Active',
                'role' => $user->role,
                'sub_id' => $user->sub_id,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ], 200);
    }

    public function actionUserByIdv2($id, $status)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $update = $user->update(['is_blocked' => $status]);

        if ($update) {
            if ($status == 1) {
                $user->tokens()->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Account has been blocked successfully'
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Account has been unblocked successfully'
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Server Error'
            ], 500);
        }
    }

    public function createUserByIdv2(Request $request)
    {
        $valid = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'sometimes|in:User,Business,Admin',
            'is_blocked' => 'sometimes|boolean'
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $valid->errors()
            ], 422);
        }

        $userData = $request->only(['name', 'email', 'phone', 'password', 'role', 'is_blocked']);

        // Set default role if not provided
        if (!isset($userData['role'])) {
            $userData['role'] = 'User';
        }

        // Set default blocked status if not provided
        if (!isset($userData['is_blocked'])) {
            $userData['is_blocked'] = 0;
        }

        // Handle phone number - set to null if empty
        if (isset($userData['phone']) && empty(trim($userData['phone']))) {
            $userData['phone'] = null;
        }

        // Hash the password
        $userData['password'] = \Hash::make($userData['password']);

        // Set verification status
        $userData['is_verified'] = 1;
        $userData['email_verified_at'] = now();

        $user = User::create($userData);

        if ($user) {
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => ($user->is_blocked == 1) ? 'Block' : 'Active',
                    'role' => $user->role,
                    'sub_id' => $user->sub_id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create user'
            ], 500);
        }
    }

    public function updateUserByIdv2(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $valid = \Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $id,
            'address' => 'nullable|string|max:500',
            'zip_code' => 'nullable|string|max:20',
            'profile_image' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|in:User,Business,Admin',
            'is_blocked' => 'sometimes|boolean'
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $valid->errors()
            ], 422);
        }

        $updateData = $request->only([
            'name',
            'email',
            'phone',
            'address',
            'zip_code',
            'profile_image',
            'description',
            'role',
            'is_blocked'
        ]);

        // Handle phone number - set to null if empty
        if (isset($updateData['phone']) && empty(trim($updateData['phone']))) {
            $updateData['phone'] = null;
        }

        // Handle nullable fields - set to null if empty
        if (isset($updateData['address']) && empty(trim($updateData['address']))) {
            $updateData['address'] = null;
        }
        if (isset($updateData['zip_code']) && empty(trim($updateData['zip_code']))) {
            $updateData['zip_code'] = null;
        }
        if (isset($updateData['profile_image']) && empty(trim($updateData['profile_image']))) {
            $updateData['profile_image'] = null;
        }
        if (isset($updateData['description']) && empty(trim($updateData['description']))) {
            $updateData['description'] = null;
        }

        // Handle password if provided
        if ($request->has('password') && !empty($request->password)) {
            $updateData['password'] = \Hash::make($request->password);
        }

        // Convert is_blocked to integer if provided
        if (isset($updateData['is_blocked'])) {
            $updateData['is_blocked'] = $updateData['is_blocked'] ? 1 : 0;
        }

        $update = $user->update($updateData);

        if ($update) {
            // Return profile image path as is
            $profileImageUrl = $user->profile_image;

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'zip_code' => $user->zip_code,
                    'profile_image' => $profileImageUrl,
                    'description' => $user->description,
                    'status' => ($user->is_blocked == 1) ? 'Block' : 'Active',
                    'role' => $user->role,
                    'sub_id' => $user->sub_id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update user'
            ], 500);
        }
    }

    public function uploadProfileImagev2(Request $request)
    {
        try {
            $user = Auth::user();



            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if user has Admin role
            if ($user->role !== 'Admin') {
                return response()->json([
                    'status' => false,
                    'message' => 'Access denied. Only administrators can access this resource.'
                ], 403);
            }

            // Check if user account is blocked
            if ($user->is_blocked == 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Access denied. Your account has been blocked. Please contact the administrator.'
                ], 403);
            }

            $valid = \Validator::make($request->all(), [
                'profile_image' => 'required|image|mimes:jpeg,jpg,jpe,png,svg,svgz,tiff,tif,webp|max:5120'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $valid->errors()
                ], 422);
            }

            if ($request->hasFile('profile_image')) {
                $img = $request->profile_image;
                $path = $img->store('public/user/profile');
                $file_path = \Storage::url($path);

                // Update user's profile image
                $user->update(['profile_image' => $file_path]);

                return response()->json([
                    'status' => true,
                    'message' => 'Profile image uploaded successfully',
                    'data' => [
                        'profile_image' => $file_path
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No image file provided'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to upload profile image: ' . $e->getMessage()
            ], 500);
        }
    }
}
