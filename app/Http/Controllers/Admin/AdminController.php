<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\Receipt;
use App\Models\User;
use App\Models\subscription;

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

    // API v2 Functions
    public function adminLoginSubmitv2(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $valid->errors()
            ], 422);
        }

        $credentials = ['email' => $request->email, 'password' => $request->password];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check if user has Admin role
            if ($user->role !== 'Admin') {
                Auth::logout(); // Logout the user
                return response()->json([
                    'status' => false,
                    'message' => 'Access denied. Only administrators can access this panel.'
                ], 403);
            }

            // Check if user account is blocked
            if ($user->is_blocked == 1) {
                Auth::logout(); // Logout the user
                return response()->json([
                    'status' => false,
                    'message' => 'Access denied. Your account has been blocked. Please contact the administrator.'
                ], 403);
            }

            // Create Sanctum token for API authentication
            $user->tokens()->delete(); // Delete existing tokens
            $token = $user->createToken('AdminToken')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'is_sub_admin' => $user->is_sub_admin
                    ],
                    'token' => $token
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
    }

    public function adminLogoutv2(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }


    public function getCurrentAdminUser(Request $request)
    {
        try {
            $user = $request->user();



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

            // Return profile image path as is
            $profileImageUrl = $user->profile_image;

            return response()->json([
                'status' => true,
                'message' => 'User data retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'zip_code' => $user->zip_code,
                        'profile_image' => $profileImageUrl,
                        'description' => $user->description,
                        'role' => $user->role,
                        'is_blocked' => $user->is_blocked,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to get user data'
            ], 500);
        }
    }

    /**
     * Get receipts with pagination for admin panel
     */
    public function getReceiptsv2(Request $request)
    {
        try {
            Log::info('Receipts endpoint called', [
                'request' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            $user = $request->user();
            Log::info('User retrieved', ['user' => $user ? $user->id : null]);

            if (!$user) {
                Log::warning('User not authenticated for receipts endpoint');
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if user has Admin role
            Log::info('Checking user role', ['role' => $user->role]);
            if ($user->role !== 'Admin') {
                Log::warning('User does not have Admin role', ['user_id' => $user->id, 'role' => $user->role]);
                return response()->json([
                    'status' => false,
                    'message' => 'Access denied. Only administrators can access this resource.'
                ], 403);
            }

            // Get query parameters
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            $search = $request->get('search', '');
            $status = $request->get('status', '');
            $paymentType = $request->get('payment_type', '');
            $dateFrom = $request->get('date_from', '');
            $dateTo = $request->get('date_to', '');

            // Build query
            $query = Receipt::with(['user:id,name,email,role', 'subscription:id,name,price,type'])
                ->select([
                    'id',
                    'user_id',
                    'subscription_id',
                    'payment_date',
                    'amount',
                    'duration',
                    'strikes',
                    'cancelled',
                    'is_recurring',
                    'recurring_subscription_id',
                    'authorize_transaction_id',
                    'payment_type',
                    'billing_cycle_number',
                    'next_billing_date',
                    'created_at',
                    'updated_at'
                ]);

            // Apply filters
            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($status !== '') {
                $query->where('cancelled', $status === 'cancelled' ? 1 : 0);
            }

            if ($paymentType) {
                $query->where('payment_type', $paymentType);
            }

            if ($dateFrom) {
                $query->where('payment_date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('payment_date', '<=', $dateTo);
            }

            // Order by latest first
            $query->orderBy('payment_date', 'desc');

            // Get paginated results
            Log::info('Executing receipts query');
            $receipts = $query->paginate($perPage, ['*'], 'page', $page);
            Log::info('Receipts query executed', ['count' => $receipts->count()]);

            // Transform data for frontend
            Log::info('Transforming receipts data');
            $receipts->getCollection()->transform(function ($receipt) {
                $user = $receipt->user;            // may be null
                $sub = $receipt->subscription;    // may be null

                return [
                    'id' => $receipt->id,
                    'user' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role
                    ] : null,
                    'subscription' => $sub ? [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'price' => $sub->price,
                        'type' => $sub->type
                    ] : null,
                    'payment_date' => $receipt->payment_date,
                    'amount' => $receipt->amount,
                    'duration' => $receipt->duration,
                    'strikes' => $receipt->strikes,
                    'cancelled' => (bool) $receipt->cancelled,
                    'is_recurring' => (bool) $receipt->is_recurring,
                    'recurring_subscription_id' => $receipt->recurring_subscription_id,
                    'authorize_transaction_id' => $receipt->authorize_transaction_id,
                    'payment_type' => $receipt->payment_type,
                    'billing_cycle_number' => $receipt->billing_cycle_number,
                    'next_billing_date' => $receipt->next_billing_date,
                    'created_at' => $receipt->created_at,
                    'updated_at' => $receipt->updated_at
                ];
            });


            return response()->json([
                'status' => true,
                'message' => 'Receipts retrieved successfully',
                'data' => [
                    'receipts' => $receipts->items(),
                    'pagination' => [
                        'current_page' => $receipts->currentPage(),
                        'last_page' => $receipts->lastPage(),
                        'per_page' => $receipts->perPage(),
                        'total' => $receipts->total(),
                        'from' => $receipts->firstItem(),
                        'to' => $receipts->lastItem()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching receipts: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve receipts'
            ], 500);
        }
    }

    /**
     * Assign subscription to user
     */
    public function assignSubscriptionToUser(Request $request)
    {
        try {
            $user = $request->user();

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

            // Validate request
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'sub_id' => 'required|integer|exists:subscriptions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->input('user_id');
            $subscriptionId = $request->input('sub_id');

            // Find the user
            $targetUser = User::find($userId);
            if (!$targetUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Find the subscription
            $subscription = \App\Models\subscription::find($subscriptionId);
            if (!$subscription) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }

            // Update the user's subscription
            $targetUser->sub_id = $subscriptionId;
            $targetUser->save();

            Log::info('Subscription assigned to user', [
                'admin_user_id' => $user->id,
                'target_user_id' => $userId,
                'subscription_id' => $subscriptionId,
                'subscription_name' => $subscription->name
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Subscription assigned successfully',
                'data' => [
                    'user' => [
                        'id' => $targetUser->id,
                        'name' => $targetUser->name,
                        'email' => $targetUser->email,
                        'sub_id' => $targetUser->sub_id
                    ],
                    'subscription' => [
                        'id' => $subscription->id,
                        'name' => $subscription->name,
                        'price' => $subscription->price,
                        'type' => $subscription->type
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error assigning subscription: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to assign subscription'
            ], 500);
        }
    }
}
