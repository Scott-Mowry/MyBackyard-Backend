<?php

namespace App\Http\Controllers\API\V2;

use App\Http\Requests\StoresubscriptionRequest;
use App\Models\sub_points;
use App\Models\subscription;
use App\Models\User;
use App\Models\Receipt;
use App\Models\BusinessPromoCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Services\AuthorizeNetService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class SubscriptionController extends BaseController
{
    public function addCardToProfile(Request $request)
    {
        $authService = new AuthorizeNetService();
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'card_number' => 'required|digits:16',
            'expiration_date' => 'required|date_format:m/y',
            'ccv' => 'required|digits:3',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:20',
            'country' => 'required|string|max:100'
        ]);

        try {
            // Check if the user already has a payment profile
            $user = User::findOrFail($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => "User not found"], 400);
            } else {
                $customerProfileId = $user->customer_profile_id;

                if (!$customerProfileId) {
                    // Create a new customer profile and payment profile
                    $paymentProfileId = $authService->createCustomerProfile(
                        $user,
                        $request->card_number,
                        $request->expiration_date,
                        $request->ccv,
                        $request->firstName,
                        $request->lastName,
                        $request->company ?? "",
                        $request->address,
                        $request->city,
                        $request->state,
                        $request->zip,
                        $request->country
                    );
                    // Save the payment profile ID to the user's record
                    if (in_array('error', $paymentProfileId)) {
                        return response()->json(['success' => false, 'message' => $paymentProfileId['error']], 400);
                    } else {
                        $user->update($paymentProfileId);
                    }
                } else {
                    // Add the new card to the existing payment profile
                    $paymentProfileId = $authService->updateCustomerProfile(
                        $user->customer_profile_id,
                        $user->payment_profile_id,
                        $user,
                        $request->card_number,
                        $request->expiration_date,
                        $request->ccv,
                        $request->firstName,
                        $request->lastName,
                        $request->company ?? "",
                        $request->address,
                        $request->city,
                        $request->state,
                        $request->zip,
                        $request->country
                    );
                    if (in_array('error', $paymentProfileId)) {
                        return response()->json(['success' => false, 'message' => $paymentProfileId['error']], 400);
                    } else {
                        $user->update($paymentProfileId);
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Card added successfully.', 'data' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), $e->getTrace(), $e->getLine()], 500);
        }
    }

    public function getCardInfo(Request $request)
    {
        $authService = new AuthorizeNetService();
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            // Check if the user already has a payment profile
            $user = User::findOrFail($request->user_id);
            $paymentProfileId = $user->payment_profile_id;

            if (!$paymentProfileId) {
                return response()->json(['success' => false, 'message' => "No Payment Profile Found"], 500);
            } else {
                // Add the new card to the existing payment profile
                $paymentProfileId = $authService->getCustomerPaymentProfile(
                    $user->customer_profile_id,
                    $user->payment_profile_id
                );
                return response()->json(['success' => true, 'message' => "Info Fetch Successfully", "data" => $paymentProfileId], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), $e->getTrace(), $e->getLine()], 500);
        }
    }

    public function checkCredit(Request $request)
    {
        $card = $request->query('card');
        $exp = $request->query('exp');
        $cvv = $request->query('cvv');
        $firstName = $request->query('firstName');
        $lastName = $request->query('lastName');
        $company = $request->query('company');
        $address = $request->query('address');
        $city = $request->query('city');
        $state = $request->query('state');
        $zip = $request->query('zip');
        $country = $request->query('country');
        $email = $request->query('email');
        $amount = $request->query('amount');

        /* Create a merchantAuthenticationType object with authentication details
           retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName("5k4nCLA7U");
        $merchantAuthentication->setTransactionKey("9b6Bv748nfWbG2Hw");

        // Set the transaction's refId
        $refId = 'ref' . time();

        // Create the payment data for a credit card
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber((string) $card);
        $creditCard->setExpirationDate((string) $exp);
        $creditCard->setCardCode((string) $cvv);
        // $creditCard->setCardNumber($request->query('card'));
        // $creditCard->setExpirationDate($request->query('exp'));
        // $creditCard->setCardCode($request->query('cvv'));

        // Add the payment data to a paymentType object
        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setCreditCard($creditCard);

        // Create order information
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber((string) time());
        $order->setDescription("Demo Payment");

        // Set the customer's Bill To address
        $customerAddress = new AnetAPI\CustomerAddressType();
        $customerAddress->setFirstName((string) $firstName);
        $customerAddress->setLastName((string) $lastName);
        $customerAddress->setCompany((string) $company);
        $customerAddress->setAddress((string) $address);
        $customerAddress->setCity((string) $city);
        $customerAddress->setState((string) $state);
        $customerAddress->setZip((string) $zip);
        $customerAddress->setCountry((string) $country);

        // Set the customer's identifying information
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId((string) time());
        $customerData->setEmail($email);

        // Add values for transaction settings
        $duplicateWindowSetting = new AnetAPI\SettingType();
        $duplicateWindowSetting->setSettingName("duplicateWindow");
        $duplicateWindowSetting->setSettingValue("60");

        // Add some merchant defined fields. These fields won't be stored with the transaction,
        // but will be echoed back in the response.
        $merchantDefinedField1 = new AnetAPI\UserFieldType();
        $merchantDefinedField1->setName("customerLoyaltyNum");
        $merchantDefinedField1->setValue("1128836273");

        $merchantDefinedField2 = new AnetAPI\UserFieldType();
        $merchantDefinedField2->setName("favoriteColor");
        $merchantDefinedField2->setValue("blue");

        // Create a TransactionRequestType object and add the previous objects to it
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount((float) $amount);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setPayment($paymentOne);
        $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);
        $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
        $transactionRequestType->addToUserFields($merchantDefinedField1);
        $transactionRequestType->addToUserFields($merchantDefinedField2);

        // Assemble the complete transaction request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);

        // Create the controller and get the response
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);


        if ($response != null) {
            // Check to see if the API request was successfully received and acted upon
            if ($response->getMessages()->getResultCode() == "Ok") {
                // Since the API request was successful, look for a transaction response
                // and parse it to display the results of authorizing the card
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    echo " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
                    echo " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
                    echo " Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n";
                    echo " Auth Code: " . $tresponse->getAuthCode() . "\n";
                    echo " Description: " . $tresponse->getMessages()[0]->getDescription() . "\n";
                } else {
                    echo "Transaction Failed \n";
                    if ($tresponse->getErrors() != null) {
                        echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                        echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                    }
                }
                // Or, print errors if the API request wasn't successful
            } else {
                echo "Transaction Failed \n";
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getErrors() != null) {
                    echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                    echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                } else {
                    echo " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
                    echo " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
                }
            }
        } else {
            echo "No response returned \n";
        }

        return response()->json(['success' => false, 'message' => $response], 200);
        ;
    }

    public function processUnSubscriptionPayment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => "User not found"], 400);
            } else {
                $subscription = subscription::findOrFail($request->subscription_id);

                if (!$subscription) {
                    return response()->json(['success' => false, 'message' => 'Subscription not Found'], 400);
                }

                $receipt = Receipt::where('user_id', $user->id)
                    ->where('subscription_id', $subscription->id)
                    ->orderBy('id', 'desc')
                    ->firstOrFail();

                if (!$receipt) {
                    return response()->json(['success' => false, 'message' => 'Receipt not found'], 400);
                }

                // Check if the user is subscribed to the subscription
                if ($user->sub_id != $request->subscription_id) {
                    return response()->json(['success' => false, 'message' => 'User is not subscribed to this plan.'], 400);
                }

                $receipt->update(['cancelled' => 1]);

                return response()->json(['success' => true, 'message' => 'Subscription cancelled successfully.'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cancelUnsubcription(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => "User not found"], 400);
            } else {
                $subscription = subscription::findOrFail($request->subscription_id);

                if (!$subscription) {
                    return response()->json(['success' => false, 'message' => 'Subscription not Found'], 400);
                }

                $receipt = Receipt::where('user_id', $user->id)
                    ->where('subscription_id', $subscription->id)
                    ->orderBy('id', 'desc')
                    ->firstOrFail();

                if (!$receipt) {
                    return response()->json(['success' => false, 'message' => 'Receipt not found'], 400);
                }

                // Check if the user is subscribed to the subscription
                if ($user->sub_id != $request->subscription_id) {
                    return response()->json(['success' => false, 'message' => 'User is not subscribed to this plan.'], 400);
                }

                $receipt->update(['cancelled' => 0]);

                return response()->json(['success' => true, 'message' => 'UnSubscription process cancelled successfully.'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function processSubscriptionPayment(Request $request)
    {
        $authService = new AuthorizeNetService();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => "User not found"], 400);
            } else {
                $subscription = subscription::findOrFail($request->subscription_id);

                if (!$subscription) {
                    return response()->json(['success' => false, 'message' => 'Subscription not Found'], 400);
                }

                // Retrieve the user's payment profile ID
                $paymentProfileId = $user->payment_profile_id;
                $customerProfileId = $user->customer_profile_id;

                if (!$paymentProfileId) {
                    // throw new \Exception('User does not have a payment profile.');
                    return response()->json(['success' => false, 'message' => 'User does not have a payment profile'], 400);
                }

                if ($user->sub_id != $request->subscription_id) {
                    // Process the payment
                    $paymentResult = $authService->processPayment(
                        $customerProfileId,
                        $paymentProfileId,
                        $subscription->price
                    );


                    if (in_array('success', $paymentResult)) {
                        $duration = 0;
                        if ($subscription->type == 'Monthly') {
                            if ($subscription->id == 5) {
                                $now = Carbon::now();
                                $targetDate = Carbon::createFromFormat('Y-m-d', '2025-08-31');
                                if ($now->greaterThan($targetDate)) {
                                    return response()->json(['success' => false, 'message' => 'Summer Sizzle Promotion is now depreciated'], 400);
                                }
                                $daysDiff = $now->diffInDays($targetDate); // Always positive
                                $duration = $daysDiff; // 30 days for monthly subscription

                            } else {
                                $duration = 30; // 30 days for monthly subscription
                            }
                        } elseif ($subscription->type == 'Annually') {
                            $duration = 365; // 365 days for annual subscription
                        }

                        // Save receipt
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
                            'authorize_transaction_id' => $paymentResult['transaction_id'] ?? null,
                            'payment_type' => 'one_time',
                            'billing_cycle_number' => null,
                            'next_billing_date' => null,
                        ]);

                        $user->update(['sub_id' => $subscription->id]);
                        return response()->json(['success' => true, 'message' => 'Payment processed successfully.', 'data' => $user]);
                    } else {
                        // Handle payment failure
                        return response()->json(['success' => false, 'message' => 'Payment failed.', $paymentResult], 400);
                    }
                } else {
                    return response()->json(['success' => false, 'message' => 'User already subscribed to this plan.'], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function hasUnsubcribe(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => "User not found"], 400);
            } else {
                $subscription = subscription::findOrFail($request->subscription_id);

                if (!$subscription) {
                    return response()->json(['success' => false, 'message' => 'Subscription not Found'], 400);
                }

                $receipt = Receipt::where('user_id', $user->id)
                    ->where('subscription_id', $subscription->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$receipt) {
                    return response()->json(['success' => false, 'message' => 'Receipt not found'], 400);
                }

                $unsubscribed = $receipt->cancelled;

                return response()->json(['success' => true, 'message' => 'Fetched Succesfully', 'data' => ['unsub' => $unsubscribed]], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function createSub(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:User,Business',
            'name' => 'required',
            'type' => 'required|in:Monthly,Annually',
            'price' => 'required',
            'sub_points' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        $sub = new subscription();
        $sub->role = $request->role;
        $sub->name = $request->name;
        $sub->type = $request->type;
        $sub->price = (double) $request->price;
        $sub->save();


        if ($request->has('sub_points')) {
            foreach ($request->sub_points as $points) {
                $point = new sub_points();
                $point->point = $points;
                $point->sub_id = $sub->id;
                $point->save();
            }

            $response = [
                'status' => 1,
                'message' => 'Subscription Created Successfully.',
            ];

            return response()->json($response, 200);
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }

    public function applyPromoCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'code' => 'required|string|max:255',
        ]);

        try {
            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => "User not found with ID: {$request->user_id}"], 400);
            }

            // Find the business promo code using the enhanced model
            $promoCode = BusinessPromoCode::where('code', strtoupper(trim($request->code)))
                ->valid() // Use the scope for active, not expired, not exhausted
                ->first();

            if (!$promoCode) {
                return response()->json(['success' => false, 'message' => 'Invalid or expired promo code'], 400);
            }

            // Check if user can use this promo code
            if (!$promoCode->canBeUsedBy($user)) {
                return response()->json(['success' => false, 'message' => 'This promo code is not available for your account type'], 400);
            }

            // Check if promo code has applicable subscriptions
            $applicableSubscriptions = $promoCode->applicable_subscriptions;
            if (empty($applicableSubscriptions)) {
                return response()->json(['success' => false, 'message' => 'No valid subscriptions for this promo code'], 400);
            }

            // Get the first applicable subscription (you could also let user choose)
            $subscription = subscription::find($applicableSubscriptions[0]);
            if (!$subscription) {
                return response()->json(['success' => false, 'message' => 'Subscription not found for this promo code'], 400);
            }

            // Calculate discount amount and final price
            $originalPrice = $subscription->price;
            $discountAmount = $promoCode->calculateDiscount($originalPrice);
            $finalAmount = max(0, $originalPrice - $discountAmount);

            // Determine duration based on promo type
            $duration = $promoCode->free_days ?: 30; // Use free_days if set, otherwise default to 30

            // Create receipt with enhanced data
            Receipt::create([
                'user_id' => $user->id,
                'payment_date' => now(),
                'subscription_id' => $subscription->id,
                'amount' => $finalAmount,
                'duration' => $duration,
                'strikes' => 0,
                'cancelled' => false,
                'is_recurring' => false,
                'recurring_subscription_id' => null,
                'authorize_transaction_id' => 'PROMO_' . $promoCode->code . '_' . time(),
                'payment_type' => 'promo',
                'billing_cycle_number' => null,
                'next_billing_date' => null,
            ]);

            // Update user subscription
            $user->update(['sub_id' => $subscription->id]);

            // Increment promo code usage count
            $promoCode->incrementUsage();

            return response()->json([
                'success' => true,
                'message' => 'Promo code applied successfully! ' . $promoCode->formatted_discount . ' discount applied.',
                'data' => [
                    'promo_code' => $promoCode->code,
                    'discount_type' => $promoCode->discount_type,
                    'discount_amount' => $discountAmount,
                    'original_price' => $originalPrice,
                    'final_amount' => $finalAmount,
                    'duration_days' => $duration,
                    'subscription' => $subscription,
                    'user' => $user->fresh()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create recurring subscription for business users
     */
    public function createRecurringSubscription(Request $request)
    {
        $authService = new AuthorizeNetService();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subscription_id' => 'required|exists:subscriptions,id',
            'start_date' => 'nullable|date|after_or_equal:today',
            'total_occurrences' => 'nullable|integer|min:1|max:9999'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $subscription = subscription::findOrFail($request->subscription_id);

            // Check if user has payment profile
            if (!$user->customer_profile_id || !$user->payment_profile_id) {
                return response()->json(['success' => false, 'message' => 'User does not have a payment profile. Please add a payment method first.'], 400);
            }

            // Check if user already has active recurring subscription
            if ($user->recurring_subscription_id && $user->recurring_subscription_status === 'active') {
                return response()->json(['success' => false, 'message' => 'User already has an active recurring subscription.'], 400);
            }

            $startDate = $request->start_date ? new \DateTime($request->start_date) : new \DateTime();
            $totalOccurrences = $request->total_occurrences ?? 9999;

            // Get the billing cycle from the subscription
            $billingCycle = $subscription->billing_cycle ?? 'monthly';

            // Handle Authorize.Net limitations for daily billing cycles
            $actualBillingCycle = $billingCycle;
            if (strtolower($billingCycle) === 'daily') {
                // Authorize.Net requires minimum 7 days for day-based subscriptions
                // So daily subscriptions will actually be billed weekly
                $actualBillingCycle = 'weekly';
                Log::info('Daily billing cycle converted to weekly due to Authorize.Net limitations', [
                    'original_billing_cycle' => $billingCycle,
                    'actual_billing_cycle' => $actualBillingCycle
                ]);
            }

            // Log the billing cycle for debugging
            Log::info('Creating recurring subscription', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'original_billing_cycle' => $billingCycle,
                'actual_billing_cycle' => $actualBillingCycle,
                'price' => $subscription->price
            ]);

            // Create recurring subscription
            $result = $authService->createRecurringSubscription(
                $user->customer_profile_id,
                $user->payment_profile_id,
                $subscription->price,
                $subscription->name . " - " . $user->name,
                $actualBillingCycle,
                $startDate,
                $totalOccurrences
            );

            if ($result['success']) {
                // Calculate next billing date based on actual billing cycle used
                $nextBillingDate = clone $startDate;
                switch (strtolower($actualBillingCycle)) {
                    case 'daily':
                        $nextBillingDate->add(new \DateInterval('P1D'));
                        break;
                    case 'weekly':
                        $nextBillingDate->add(new \DateInterval('P7D'));
                        break;
                    case 'monthly':
                        $nextBillingDate->add(new \DateInterval('P1M'));
                        break;
                    case 'annually':
                    case 'yearly':
                        $nextBillingDate->add(new \DateInterval('P1Y'));
                        break;
                    default:
                        // Default to monthly
                        $nextBillingDate->add(new \DateInterval('P1M'));
                        break;
                }

                // Update user with recurring subscription info
                $user->update([
                    'recurring_subscription_id' => $result['subscription_id'],
                    'recurring_subscription_start_date' => $startDate,
                    'recurring_subscription_status' => 'active',
                    'sub_id' => $subscription->id
                ]);

                // Create initial receipt
                Receipt::create([
                    'user_id' => $user->id,
                    'payment_date' => $startDate,
                    'subscription_id' => $subscription->id,
                    'amount' => $subscription->price,
                    'duration' => $this->calculateDuration($actualBillingCycle),
                    'strikes' => 0,
                    'cancelled' => false,
                    'is_recurring' => true,
                    'recurring_subscription_id' => $result['subscription_id'],
                    'authorize_transaction_id' => 'RECURRING_SETUP_' . $result['subscription_id'],
                    'payment_type' => 'recurring',
                    'billing_cycle_number' => 1,
                    'next_billing_date' => $nextBillingDate
                ]);

                $responseMessage = 'Recurring subscription created successfully.';
                if (strtolower($billingCycle) === 'daily') {
                    $responseMessage .= ' Note: Due to payment processor limitations, daily billing cycles are processed weekly (every 7 days).';
                }

                return response()->json([
                    'success' => true,
                    'message' => $responseMessage,
                    'data' => [
                        'subscription_id' => $result['subscription_id'],
                        'next_billing_date' => $nextBillingDate->format('Y-m-d H:i:s'),
                        'original_billing_cycle' => $billingCycle,
                        'actual_billing_cycle' => $actualBillingCycle,
                        'user' => $user->fresh(),
                        'subscription' => $subscription
                    ]
                ], 200);
            } else {
                return response()->json(['success' => false, 'message' => $result['error']], 400);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel recurring subscription
     */
    public function cancelRecurringSubscription(Request $request)
    {
        $authService = new AuthorizeNetService();

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $user = User::findOrFail($request->user_id);

            if (!$user->recurring_subscription_id) {
                return response()->json(['success' => false, 'message' => 'User does not have a recurring subscription.'], 400);
            }

            // Check if already cancelled
            if ($user->recurring_subscription_status === 'cancelled') {
                return response()->json(['success' => false, 'message' => 'Recurring subscription is already cancelled.'], 400);
            }

            // Cancel subscription with Authorize.Net
            $result = $authService->cancelRecurringSubscription($user->recurring_subscription_id);

            if ($result['success']) {
                // Update user status
                $user->update([
                    'recurring_subscription_status' => 'cancelled'
                ]);

                // Update receipts to mark as cancelled
                Receipt::where('user_id', $user->id)
                    ->where('recurring_subscription_id', $user->recurring_subscription_id)
                    ->where('is_recurring', true)
                    ->update(['cancelled' => 1]);

                return response()->json([
                    'success' => true,
                    'message' => 'Recurring subscription cancelled successfully.',
                    'data' => ['user' => $user->fresh()]
                ], 200);
            } else {
                return response()->json(['success' => false, 'message' => $result['error']], 400);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get recurring subscription status
     */
    public function getRecurringSubscriptionStatus(Request $request)
    {
        $authService = new AuthorizeNetService();

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $user = User::findOrFail($request->user_id);

            if (!$user->recurring_subscription_id) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => 'none',
                        'message' => 'No recurring subscription found',
                        'user_status' => $user->recurring_subscription_status
                    ]
                ], 200);
            }

            // Get local status first
            $localStatus = [
                'local_status' => $user->recurring_subscription_status,
                'subscription_id' => $user->recurring_subscription_id,
                'start_date' => $user->recurring_subscription_start_date,
                'user' => $user
            ];

            // Try to get status from Authorize.Net (optional - may fail due to API issues)
            try {
                $result = $authService->getSubscriptionStatus($user->recurring_subscription_id);
                if ($result['success']) {
                    $localStatus['authorize_status'] = $result['status'];
                }
            } catch (\Exception $e) {
                // Log the error but don't fail the request
                Log::warning('Failed to get Authorize.Net subscription status: ' . $e->getMessage());
                $localStatus['authorize_status'] = 'unavailable';
            }

            return response()->json([
                'success' => true,
                'data' => $localStatus
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update recurring subscription plan
     */
    public function updateRecurringSubscription(Request $request)
    {
        $authService = new AuthorizeNetService();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_subscription_id' => 'required|exists:subscriptions,id'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $newSubscription = subscription::findOrFail($request->new_subscription_id);

            if (!$user->recurring_subscription_id || $user->recurring_subscription_status !== 'active') {
                return response()->json(['success' => false, 'message' => 'User does not have an active recurring subscription.'], 400);
            }

            // Try to update subscription amount with Authorize.Net
            $result = $authService->updateRecurringSubscription(
                $user->recurring_subscription_id,
                $newSubscription->price
            );

            if ($result['success']) {
                // Update user's subscription
                $user->update(['sub_id' => $newSubscription->id]);

                // Create a receipt for the plan change
                Receipt::create([
                    'user_id' => $user->id,
                    'payment_date' => now(),
                    'subscription_id' => $newSubscription->id,
                    'amount' => $newSubscription->price,
                    'duration' => $newSubscription->billing_cycle === 'annually' ? 365 : 30,
                    'strikes' => 0,
                    'cancelled' => false,
                    'is_recurring' => true,
                    'recurring_subscription_id' => $user->recurring_subscription_id,
                    'authorize_transaction_id' => 'PLAN_CHANGE_' . time(),
                    'payment_type' => 'recurring',
                    'billing_cycle_number' => 0, // Plan change
                    'next_billing_date' => null, // Will be calculated by next billing cycle
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Recurring subscription updated successfully.',
                    'data' => [
                        'user' => $user->fresh(),
                        'new_subscription' => $newSubscription
                    ]
                ], 200);
            } else {
                return response()->json(['success' => false, 'message' => $result['error']], 400);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get subscription with new fields
     */
    public function getSubV2(Request $request)
    {
        $user = $request->user();

        $subscriptions = subscription::with('sub_points')
            ->where('status', 'active')
            ->where('on_show', true)
            ->when($user, function ($query) use ($user) {
                return $query->where('role', $user->role);
            })
            ->ordered()
            ->get();

        if ($subscriptions->isNotEmpty()) {
            $subscriptions = $subscriptions->map(function ($sub) {
                $list = collect();
                foreach ($sub->sub_points as $points) {
                    $list->push($points->point);
                }

                // Add new fields to response
                $subData = $sub->toArray();
                $subData['sub_points'] = $list;
                $subData['formatted_price'] = $sub->formatted_price;
                $subData['is_popular'] = (bool) $sub->is_popular;

                return $subData;
            });

            return response()->json([
                'success' => true,
                'data' => ['subscriptions' => $subscriptions],
                'message' => 'Subscriptions fetched successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No active subscriptions found'
            ], 404);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoresubscriptionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(subscription $subscription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(subscription $subscription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(subscription $subscription)
    {
        //
    }

    /**
     * Calculate duration in days based on billing cycle
     */
    private function calculateDuration($billingCycle)
    {
        switch (strtolower($billingCycle)) {
            case 'daily':
                return 1;
            case 'weekly':
                return 7;
            case 'monthly':
                return 30;
            case 'annually':
            case 'yearly':
                return 365;
            default:
                return 30; // Default to monthly
        }
    }
}
