<?php

namespace App\Console\Commands;

use App\Models\Receipt;
use App\Models\User;
use App\Services\AuthorizeNetService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:renew';

    protected $description = 'Process subscription renewals and manage strikes.';

    public function handle(AuthorizeNetService $authService)
    {
        $receipts = Receipt::where('payment_date', '<', now()->subDays(30))
            ->where('strikes', '<', 3)
            ->get();

        foreach ($receipts as $receipt) {
            $user = User::find($receipt->user_id);
            $subscription = $receipt->subscription;

            // Retrieve the user's payment profile ID
            $customerProfileId = $user->customer_profile_id;
            $paymentProfileId = $user->payment_profile_id;

            if (!$paymentProfileId) {
                continue;
            }

            // Attempt to process payment
            $paymentResult = $authService->processPayment(
                $customerProfileId,
                $paymentProfileId,
                $subscription->amount
            );

            if ($paymentResult['success']) {
                // Update receipt with new payment date
                $receipt->update(['payment_date' => now(), 'strikes' => 0]);
            } else {
                // Increment strike count
                $receipt->increment('strikes');

                // Check if strikes exceed limit
                if ($receipt->strikes >= 3) {
                    // Disable subscription for the user
                    $user->update(['subscription_id' => null]);
                }
            }
        }
    }
}
