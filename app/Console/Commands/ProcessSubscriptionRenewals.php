<?php

namespace App\Console\Commands;

use App\Models\Receipt;
use App\Models\subscription;
use App\Models\User;
use App\Services\AuthorizeNetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:renew';

    protected $description = 'Process subscription renewals and manage strikes.';

    public function handle(AuthorizeNetService $authService)
    {
        $receipts = Receipt::select(DB::raw('MAX(id) as id'))
            ->whereRaw('payment_date > DATE_SUB(NOW(), INTERVAL duration DAY)')
            ->where('strikes', '<', 3)
            ->groupBy('user_id');

        foreach ($receipts as $receipt) {
            $user = User::find($receipt->user_id);
            if ($receipt->cancelled == 1) {
                if ($user->sub_id == null) {
                    continue;
                } else {
                    $user->update(['sub_id' => null]);
                    $receipt->update(['strikes' => 3]);
                    continue;
                }
            } else {
                $id = 0;
                if ($receipt->subscription_id == 5) {
                    $id = 2;
                } else {
                    $id = $receipt->subscription_id;
                }
                $subscription = subscription::find($id);

                if ($user & $subscription) {
                    // Retrieve the user's payment profile ID
                    $customerProfileId = $user->customer_profile_id;
                    $paymentProfileId = $user->payment_profile_id;

                    if ($customerProfileId == null || $paymentProfileId == null) {
                        $user->update(['sub_id' => null]);
                        $receipt->update(['strikes' => 3]);
                        continue;
                    } else {
                        // Attempt to process payment
                        $paymentResult = $authService->processPayment(
                            $customerProfileId,
                            $paymentProfileId,
                            $subscription->price
                        );

                        if (in_array('success', $paymentResult)) {
                            // Update receipt with new payment date
                            // $receipt->update(['payment_date' => now(), 'strikes' => 0]);
                            $duration = 0;
                            if ($subscription->type == 'Monthly') {
                                $duration = 30; // 30 days for monthly subscription
                            } elseif ($subscription->type == 'Annually') {
                                $duration = 365; // 365 days for annual subscription
                            }

                            Receipt::create([
                                'user_id' => $user->id,
                                'payment_date' => now(),
                                'subscription_id' => $subscription->id,
                                'amount' => $subscription->price,
                                'duration' => $duration,
                                'strikes' => 0
                            ]);
                        } else {
                            // Increment strike count
                            $receipt->increment('strikes');

                            // Check if strikes exceed limit
                            if ($receipt->strikes == 3) {
                                // Disable subscription for the user
                                $user->update(['sub_id' => null]);
                            }
                        }
                    }
                }
            }
        }
    }
}
