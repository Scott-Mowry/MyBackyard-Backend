<?php

namespace App\Console\Commands;

use App\Models\Receipt;
use App\Models\Subscription;
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

            $receipts = Receipt::whereIn('id', $latestReceiptIds)
                ->where('strikes', '<', 3) // Optional: remove if you want to retry all
                ->get();

            foreach ($receipts as $receipt) {
                $user = $receipt->user;

                // Check if already cancelled
                if ($receipt->cancelled == 1) {
                    if ($user->sub_id !== null) {
                        $user->update(['sub_id' => null]);
                    }
                    $receipt->update(['strikes' => 3]);
                    continue;
                }

                // Calculate expiration
                $expiry = Carbon::parse($receipt->payment_date)->addDays($receipt->duration);
                if (!now()->greaterThan($expiry)) {
                    continue; // Not expired yet
                }

                $subscriptionId = ($receipt->subscription_id == 5) ? 2 : $receipt->subscription_id;
                $subscription = Subscription::find($subscriptionId);

                if (!$subscription || !$user) {
                    continue;
                }

                $customerProfileId = $user->customer_profile_id;
                $paymentProfileId = $user->payment_profile_id;

                if (!$customerProfileId || !$paymentProfileId) {
                    $user->update(['sub_id' => null]);
                    $receipt->update(['strikes' => 3]);
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

                        Receipt::create([
                            'user_id' => $user->id,
                            'payment_date' => now(),
                            'subscription_id' => $subscription->id,
                            'amount' => $subscription->price,
                            'duration' => $duration,
                            'strikes' => 0
                        ]);
                    } else {
                        // Payment failed, increment strike
                        $receipt->increment('strikes');
                        if ($receipt->strikes >= 3) {
                            $user->update(['sub_id' => null]);
                        }
                    }
                } catch (\Throwable $th) {
                    // Handle failure
                    $receipt->increment('strikes');
                    if ($receipt->strikes >= 3) {
                        $user->update(['sub_id' => null]);
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
