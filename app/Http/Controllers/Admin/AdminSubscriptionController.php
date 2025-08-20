<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\subscription;
use App\Models\sub_points;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminSubscriptionController extends Controller
{
    public function getSubscriptionsv2(Request $request)
    {
        try {
            $subscriptions = subscription::with('sub_points')->orderBy('id', 'desc')->get();

            $formattedSubscriptions = $subscriptions->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'name' => $subscription->name ?? '---',
                    'type' => $subscription->type ?? '---',
                    'price' => $subscription->price ?? '---',
                    'description' => $subscription->description ?? null,
                    'billing_cycle' => $subscription->billing_cycle ?? null,
                    'is_popular' => $subscription->is_popular ?? false,
                    'on_show' => $subscription->on_show ?? true,
                    'sort_order' => $subscription->sort_order ?? 0,
                    'is_depreciated' => $subscription->is_depreciated ? 'Yes' : 'No',
                    'status' => $subscription->is_depreciated ? 'Deprecated' : 'Active',
                    'sub_points' => $subscription->sub_points->pluck('point')->toArray(),
                    'created_at' => $subscription->created_at,
                    'updated_at' => $subscription->updated_at
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Subscriptions retrieved successfully',
                'data' => $formattedSubscriptions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving subscriptions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSubscriptionByIdv2($id)
    {
        try {
            $subscription = subscription::with('sub_points')->find($id);

            if (!$subscription) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Subscription retrieved successfully',
                'data' => [
                    'id' => $subscription->id,
                    'name' => $subscription->name,
                    'type' => $subscription->type,
                    'price' => $subscription->price,
                    'description' => $subscription->description ?? null,
                    'billing_cycle' => $subscription->billing_cycle ?? null,
                    'is_popular' => $subscription->is_popular ?? false,
                    'on_show' => $subscription->on_show ?? true,
                    'sort_order' => $subscription->sort_order ?? 0,
                    'is_depreciated' => $subscription->is_depreciated,
                    'sub_points' => $subscription->sub_points->pluck('point')->toArray(),
                    'created_at' => $subscription->created_at,
                    'updated_at' => $subscription->updated_at
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addSubscriptionv2(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'billing_cycle' => 'nullable|string|max:255',
                'is_popular' => 'boolean',
                'on_show' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
                'is_depreciated' => 'boolean',
                'sub_points' => 'array',
                'sub_points.*' => 'string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $subscription = subscription::create([
                'name' => $request->name,
                'type' => $request->type,
                'price' => $request->price,
                'description' => $request->description,
                'billing_cycle' => $request->billing_cycle,
                'is_popular' => $request->is_popular ?? false,
                'on_show' => $request->on_show ?? true,
                'sort_order' => $request->sort_order ?? 0,
                'is_depreciated' => $request->is_depreciated ?? false
            ]);

            // Create sub_points if provided
            if ($request->has('sub_points') && is_array($request->sub_points)) {
                foreach ($request->sub_points as $point) {
                    if (!empty(trim($point))) {
                        sub_points::create([
                            'point' => trim($point),
                            'sub_id' => $subscription->id
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Subscription created successfully',
                'data' => [
                    'id' => $subscription->id,
                    'name' => $subscription->name,
                    'type' => $subscription->type,
                    'price' => $subscription->price,
                    'description' => $subscription->description,
                    'billing_cycle' => $subscription->billing_cycle,
                    'is_popular' => $subscription->is_popular,
                    'on_show' => $subscription->on_show,
                    'sort_order' => $subscription->sort_order,
                    'is_depreciated' => $subscription->is_depreciated,
                    'status' => $subscription->is_depreciated ? 'Deprecated' : 'Active',
                    'sub_points' => $subscription->sub_points->pluck('point')->toArray()
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error creating subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSubscriptionv2(Request $request, $id)
    {
        try {
            $subscription = subscription::find($id);

            if (!$subscription) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'billing_cycle' => 'nullable|string|max:255',
                'is_popular' => 'boolean',
                'on_show' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
                'is_depreciated' => 'boolean',
                'sub_points' => 'array',
                'sub_points.*' => 'string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $subscription->update([
                'name' => $request->name,
                'type' => $request->type,
                'price' => $request->price,
                'description' => $request->description,
                'billing_cycle' => $request->billing_cycle,
                'is_popular' => $request->is_popular ?? false,
                'on_show' => $request->on_show ?? true,
                'sort_order' => $request->sort_order ?? 0,
                'is_depreciated' => $request->is_depreciated ?? false
            ]);

            // Update sub_points if provided
            if ($request->has('sub_points')) {
                // Delete existing sub_points
                sub_points::where('sub_id', $subscription->id)->delete();

                // Create new sub_points
                if (is_array($request->sub_points)) {
                    foreach ($request->sub_points as $point) {
                        if (!empty(trim($point))) {
                            sub_points::create([
                                'point' => trim($point),
                                'sub_id' => $subscription->id
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Subscription updated successfully',
                'data' => [
                    'id' => $subscription->id,
                    'name' => $subscription->name,
                    'type' => $subscription->type,
                    'price' => $subscription->price,
                    'description' => $subscription->description,
                    'billing_cycle' => $subscription->billing_cycle,
                    'is_popular' => $subscription->is_popular,
                    'on_show' => $subscription->on_show,
                    'sort_order' => $subscription->sort_order,
                    'is_depreciated' => $subscription->is_depreciated,
                    'status' => $subscription->is_depreciated ? 'Deprecated' : 'Active',
                    'sub_points' => $subscription->fresh()->sub_points->pluck('point')->toArray()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteSubscriptionv2($id)
    {
        try {
            // Prevent deletion of protected subscription IDs
            $protectedIds = [1, 2, 3, 5];
            if (in_array($id, $protectedIds)) {
                return response()->json([
                    'status' => false,
                    'message' => 'This subscription cannot be deleted as it is protected'
                ], 403);
            }

            $subscription = subscription::find($id);

            if (!$subscription) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }

            $subscription->delete();

            return response()->json([
                'status' => true,
                'message' => 'Subscription deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting subscription: ' . $e->getMessage()
            ], 500);
        }
    }
}
