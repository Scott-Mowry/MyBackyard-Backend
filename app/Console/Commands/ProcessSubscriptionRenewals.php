<?php

namespace App\Console\Commands;

use App\Models\Receipt;
use App\Models\subscription;
use App\Models\User;
use App\Services\AuthorizeNetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:renew';
    protected $description = 'Process subscription renewals and manage strikes.';

    public function handle()
    {
        $authService = new AuthorizeNetService();

        try {
            $latestReceiptIds = Receipt::select(DB::raw('MAX(id) as id'))
                ->groupBy('user_id')
                ->pluck('id');

            $receipts = Receipt::with('user') // Eager load user relationship
                ->whereIn('id', $latestReceiptIds)
                ->where('strikes', '<', 3)
                ->where('payment_type', '!=', 'promo') // Skip promo code receipts (free trials)
                ->where('cancelled', '!=', 1) // Skip cancelled receipts
                ->get();

            foreach ($receipts as $receipt) {
                $user = $receipt->user;

                // Skip if no user found
                if (!$user) {
                    Log::warning("No user found for receipt {$receipt->id}");
                    continue;
                }

                // Check if already cancelled
                if ($receipt->cancelled == 1) {
                    if ($user->sub_id !== null) {
                        $user->update(['sub_id' => null]);
                    }
                    $receipt->update(['strikes' => 3]);
                    continue;
                }

                // Calculate expiration based on next_billing_date if available, otherwise use payment_date + duration
                $expiry = null;
                if ($receipt->next_billing_date) {
                    $expiry = Carbon::parse($receipt->next_billing_date);
                } else {
                    $expiry = Carbon::parse($receipt->payment_date)->addDays($receipt->duration);
                }

                if (!now()->greaterThan($expiry)) {
                    continue; // Not expired yet
                }

                $subscriptionId = ($receipt->subscription_id == 5) ? 2 : $receipt->subscription_id;
                $subscription = subscription::find($subscriptionId);

                if (!$subscription) {
                    Log::warning("No subscription found for ID {$subscriptionId} in receipt {$receipt->id}");
                    continue;
                }

                $customerProfileId = $user->customer_profile_id;
                $paymentProfileId = $user->payment_profile_id;

                if (!$customerProfileId || !$paymentProfileId) {
                    $user->update(['sub_id' => null]);
                    $receipt->update(['strikes' => 3]);
                    Log::info("User {$user->id} has no payment profile, subscription cancelled");
                    continue;
                }

                // Attempt to process payment
                try {
                    $paymentResult = $authService->processPayment(
                        $customerProfileId,
                        $paymentProfileId,
                        $subscription->price
                    );

                    if (isset($paymentResult['status']) && $paymentResult['status'] === 'success') {
                        // Determine duration again
                        $duration = ($subscription->type === 'Monthly') ? 30 : 365;
                        $nextBillingDate = now()->addDays($duration);

                        Receipt::create([
                            'user_id' => $user->id,
                            'payment_date' => now(),
                            'subscription_id' => $subscription->id,
                            'amount' => $subscription->price,
                            'duration' => $duration,
                            'strikes' => 0,
                            'cancelled' => false,
                            'is_recurring' => false,
                            'recurring_subscription_id' => null,
                            'authorize_transaction_id' => $paymentResult['transaction_id'] ?? 'RENEWAL_' . time(),
                            'payment_type' => 'recurring',
                            'billing_cycle_number' => ($receipt->billing_cycle_number ?? 0) + 1,
                            'next_billing_date' => $nextBillingDate,
                        ]);

                        Log::info("Successfully renewed subscription for user {$user->id}, subscription {$subscription->id}");
                    } else {
                        // Payment failed, increment strike
                        $receipt->increment('strikes');
                        if ($receipt->strikes >= 3) {
                            $user->update(['sub_id' => null]);
                            Log::warning("User {$user->id} subscription cancelled after 3 failed attempts");
                        }
                        Log::warning("Payment failed for user {$user->id}: " . ($paymentResult['message'] ?? 'Unknown error'));
                    }
                } catch (\Throwable $th) {
                    // Handle failure
                    $receipt->increment('strikes');
                    if ($receipt->strikes >= 3) {
                        $user->update(['sub_id' => null]);
                        Log::warning("User {$user->id} subscription cancelled after 3 failed attempts due to exception");
                    }

                    Log::error("Error processing payment for user {$user->id}: " . $th->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Subscription renewal error: ' . $e->getMessage(), [
                'exception' => $e,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
