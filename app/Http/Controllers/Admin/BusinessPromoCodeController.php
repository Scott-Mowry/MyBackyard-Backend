<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessPromoCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class BusinessPromoCodeController extends Controller
{
    /**
     * Get all promo codes with pagination
     */
    public function getPromoCodes(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $status = $request->get('status', '');
            $targetRole = $request->get('target_role', '');

            $query = BusinessPromoCode::with(['creator']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply target role filter
            if ($targetRole) {
                $query->where('target_role', $targetRole);
            }

            // Get paginated results
            $promoCodes = $query->ordered()->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Promo codes retrieved successfully',
                'data' => $promoCodes->items(),
                'pagination' => [
                    'current_page' => $promoCodes->currentPage(),
                    'last_page' => $promoCodes->lastPage(),
                    'per_page' => $promoCodes->perPage(),
                    'total' => $promoCodes->total(),
                    'from' => $promoCodes->firstItem(),
                    'to' => $promoCodes->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve promo codes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get promo code by ID
     */
    public function getPromoCodeById($id)
    {
        try {
            $promoCode = BusinessPromoCode::with(['creator', 'subscriptions'])->find($id);

            if (!$promoCode) {
                return response()->json([
                    'status' => false,
                    'message' => 'Promo code not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Promo code retrieved successfully',
                'data' => $promoCode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve promo code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new promo code
     */
    public function createPromoCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:promo_codes,code',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'discount_type' => 'required|in:percentage,fixed_amount,free_trial',
                'discount_value' => 'nullable|numeric|min:0',
                'free_days' => 'nullable|integer|min:1',
                'usage_limit' => 'nullable|integer|min:1',
                'per_user_limit' => 'nullable|integer|min:1',
                'starts_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after:starts_at',
                'target_role' => 'required|in:Business,User,Both',
                'applicable_subscriptions' => 'nullable|array',
                'status' => 'required|in:active,inactive,expired,draft',
                'is_featured' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
                'admin_notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate discount value based on type
            if ($request->discount_type === 'percentage' && (!$request->discount_value || $request->discount_value > 100)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Percentage discount must be between 0 and 100'
                ], 422);
            }

            if ($request->discount_type === 'fixed_amount' && (!$request->discount_value || $request->discount_value <= 0)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Fixed amount discount must be greater than 0'
                ], 422);
            }

            if ($request->discount_type === 'free_trial' && (!$request->free_days || $request->free_days <= 0)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Free trial must have at least 1 day'
                ], 422);
            }

            $promoCode = BusinessPromoCode::create([
                'code' => strtoupper($request->code),
                'name' => $request->name,
                'description' => $request->description,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'free_days' => $request->free_days,
                'usage_limit' => $request->usage_limit,
                'per_user_limit' => $request->per_user_limit,
                'starts_at' => $request->starts_at,
                'expires_at' => $request->expires_at,
                'target_role' => $request->target_role,
                'applicable_subscriptions' => $request->applicable_subscriptions,
                'status' => $request->status,
                'is_featured' => $request->boolean('is_featured'),
                'sort_order' => $request->sort_order ?? 0,
                'admin_notes' => $request->admin_notes,
                'created_by' => Auth::id()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Promo code created successfully',
                'data' => $promoCode
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create promo code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update promo code
     */
    public function updatePromoCode(Request $request, $id)
    {
        try {
            $promoCode = BusinessPromoCode::find($id);

            if (!$promoCode) {
                return response()->json([
                    'status' => false,
                    'message' => 'Promo code not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'sometimes|required|string|max:50|unique:promo_codes,code,' . $id,
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'discount_type' => 'sometimes|required|in:percentage,fixed_amount,free_trial',
                'discount_value' => 'nullable|numeric|min:0',
                'free_days' => 'nullable|integer|min:1',
                'usage_limit' => 'nullable|integer|min:1',
                'per_user_limit' => 'nullable|integer|min:1',
                'starts_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after:starts_at',
                'target_role' => 'sometimes|required|in:Business,User,Both',
                'applicable_subscriptions' => 'nullable|array',
                'status' => 'sometimes|required|in:active,inactive,expired,draft',
                'is_featured' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
                'admin_notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate discount value based on type
            if ($request->has('discount_type') || $request->has('discount_value') || $request->has('free_days')) {
                $discountType = $request->discount_type ?? $promoCode->discount_type;
                $discountValue = $request->discount_value ?? $promoCode->discount_value;
                $freeDays = $request->free_days ?? $promoCode->free_days;

                if ($discountType === 'percentage' && (!$discountValue || $discountValue > 100)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Percentage discount must be between 0 and 100'
                    ], 422);
                }

                if ($discountType === 'fixed_amount' && (!$discountValue || $discountValue <= 0)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Fixed amount discount must be greater than 0'
                    ], 422);
                }

                if ($discountType === 'free_trial' && (!$freeDays || $freeDays <= 0)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Free trial must have at least 1 day'
                    ], 422);
                }
            }

            $promoCode->update([
                'code' => $request->has('code') ? strtoupper($request->code) : $promoCode->code,
                'name' => $request->name ?? $promoCode->name,
                'description' => $request->description ?? $promoCode->description,
                'discount_type' => $request->discount_type ?? $promoCode->discount_type,
                'discount_value' => $request->discount_value ?? $promoCode->discount_value,
                'free_days' => $request->free_days ?? $promoCode->free_days,
                'usage_limit' => $request->usage_limit ?? $promoCode->usage_limit,
                'per_user_limit' => $request->per_user_limit ?? $promoCode->per_user_limit,
                'starts_at' => $request->starts_at ?? $promoCode->starts_at,
                'expires_at' => $request->expires_at ?? $promoCode->expires_at,
                'target_role' => $request->target_role ?? $promoCode->target_role,
                'applicable_subscriptions' => $request->applicable_subscriptions ?? $promoCode->applicable_subscriptions,
                'status' => $request->status ?? $promoCode->status,
                'is_featured' => $request->has('is_featured') ? $request->boolean('is_featured') : $promoCode->is_featured,
                'sort_order' => $request->sort_order ?? $promoCode->sort_order,
                'admin_notes' => $request->admin_notes ?? $promoCode->admin_notes
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Promo code updated successfully',
                'data' => $promoCode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update promo code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete promo code
     */
    public function deletePromoCode($id)
    {
        try {
            $promoCode = BusinessPromoCode::find($id);

            if (!$promoCode) {
                return response()->json([
                    'status' => false,
                    'message' => 'Promo code not found'
                ], 404);
            }

            // Check if promo code has been used
            if ($promoCode->usage_count > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete promo code that has been used'
                ], 422);
            }

            $promoCode->delete();

            return response()->json([
                'status' => true,
                'message' => 'Promo code deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete promo code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle promo code status
     */
    public function toggleStatus($id)
    {
        try {
            $promoCode = BusinessPromoCode::find($id);

            if (!$promoCode) {
                return response()->json([
                    'status' => false,
                    'message' => 'Promo code not found'
                ], 404);
            }

            $newStatus = $promoCode->status === 'active' ? 'inactive' : 'active';
            $promoCode->update(['status' => $newStatus]);

            return response()->json([
                'status' => true,
                'message' => 'Promo code status updated successfully',
                'data' => ['status' => $newStatus]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update promo code status: ' . $e->getMessage()
            ], 500);
        }
    }
}
