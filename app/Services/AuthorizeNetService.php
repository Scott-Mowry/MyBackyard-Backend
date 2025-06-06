<?php

namespace App\Services;

use App\Models\User;
use Http;
use Log;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Exception;

class AuthorizeNetService
{
    protected $merchantAuthentication;

    public function __construct()
    {
        $this->merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        // $this->merchantAuthentication->setClientKey(config('authorizeNet.client_key'));
        // $this->merchantAuthentication->setName(config('authorizeNet.login_id'));
        // $this->merchantAuthentication->setTransactionKey(config('authorizeNet.transaction_key'));
        $this->merchantAuthentication->setName("5k4nCLA7U");
        $this->merchantAuthentication->setTransactionKey("9b6Bv748nfWbG2Hw");
    }

    function getCustomerPaymentProfile(
        string $customerProfileId,
        string $customerPaymentProfileId
    ) {

        // Set the transaction's refId
        $refId = 'ref' . time();

        //request requires customerProfileId and customerPaymentProfileId
        $request = new AnetAPI\GetCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($refId);
        $request->setCustomerProfileId($customerProfileId);
        $request->setCustomerPaymentProfileId($customerPaymentProfileId);

        $controller = new AnetController\GetCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        if (($response != null)) {
            if ($response->getMessages()->getResultCode() == "Ok") {
                // echo "GetCustomerPaymentProfile SUCCESS: " . "\n";
                // echo "Customer Payment Profile Id: " . $response->getPaymentProfile()->getCustomerPaymentProfileId() . "\n";
                // echo "Customer Payment Profile Billing Address: " . $response->getPaymentProfile()->getbillTo()->getAddress() . "\n";
                // echo "Customer Payment Profile Card Last 4 " . $response->getPaymentProfile()->getPayment()->getCreditCard()->getCardNumber() . "\n";


                return [
                    "last4" => $response->getPaymentProfile()->getPayment()->getCreditCard()->getCardNumber(),
                    "billing_address" => $response->getPaymentProfile()->getbillTo()->getAddress()
                ];
                // if ($response->getPaymentProfile()->getSubscriptionIds() != null) {
                //     if ($response->getPaymentProfile()->getSubscriptionIds() != null) {

                //         echo "List of subscriptions:";
                //         foreach ($response->getPaymentProfile()->getSubscriptionIds() as $subscriptionid)
                //             echo $subscriptionid . "\n";
                //     }
                // }
            } else {
                // echo "GetCustomerPaymentProfile ERROR :  Invalid response\n";
                $errorMessages = $response->getMessages()->getMessage();
                // echo "Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n";

                return [
                    'error' => "Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n"
                ];
            }
        } else {
            // echo "NULL Response Error";
            return [
                'error' => "NULL Response Error"
            ];
        }
        // return $response;
    }

    // Create Customer Profile
    public function createCustomerProfile(
        User $user,
        string $cardNumber,
        string $expirationDate,
        string $ccv,
        string $firstName,
        string $lastName,
        string $company,
        string $address,
        string $city,
        string $state,
        string $zip,
        string $country
    ): array {
        try {
            // $lat = $user->latitude;
            // $lng = $user->longitude;
            // $components = $this->addressFromCoordinate($lat, $lng);

            // Set the transaction's refId
            $refId = 'ref' . time();

            // Set credit card information for payment profile
            $creditCard = new AnetAPI\CreditCardType();
            $creditCard->setCardNumber($cardNumber);
            $creditCard->setExpirationDate($expirationDate);
            $creditCard->setCardCode($ccv);
            $paymentCreditCard = new AnetAPI\PaymentType();
            $paymentCreditCard->setCreditCard($creditCard);

            // Create the Bill To info for new payment type
            $billTo = new AnetAPI\CustomerAddressType();
            // $billTo->setFirstName($user->name);
            // switch ($user->role) {
            //     case "Business":
            //         $billTo->setCompany($user->name);
            //         break;
            //     default:
            //         $billTo->setLastName($user->lastName);
            //         break;
            // }
            // $billTo->setAddress($user->address);
            // $billTo->setCity($components['city'] ?? 'Unknown City');
            // $billTo->setState($components['state'] ?? 'Unknown State');
            // $billTo->setZip($components['zipcode'] ?? '00000');
            // $billTo->setCountry($components['country'] ?? 'Unknown Country');
            // $billTo->setPhoneNumber($user->phone);
            $billTo->setFirstName($firstName);
            $billTo->setLastName($lastName);
            $billTo->setCompany($company);
            $billTo->setAddress($address);
            $billTo->setCity($city);
            $billTo->setState($state);
            $billTo->setZip($zip);
            $billTo->setCountry($country);
            $billTo->setPhoneNumber($user->phone);

            // Create a customer shipping address
            $customerShippingAddress = new AnetAPI\CustomerAddressType();
            // $customerShippingAddress->setFirstName($user->name);
            // switch ($user->role) {
            //     case "Business":
            //         $customerShippingAddress->setCompany($user->name);
            //         break;
            //     default:
            //         $customerShippingAddress->setLastName($user->lastName);
            //         break;
            // }
            // $customerShippingAddress->setAddress($user->address);
            // $customerShippingAddress->setCity($components['city'] ?? 'Unknown City');
            // $customerShippingAddress->setState($components['state'] ?? 'Unknown State');
            // $customerShippingAddress->setZip($components['zipcode'] ?? '00000');
            // $customerShippingAddress->setCountry($components['country'] ?? 'Unknown Country');
            // $customerShippingAddress->setPhoneNumber($user->phone);
            //
            $customerShippingAddress->setFirstName($firstName);
            $customerShippingAddress->setLastName($lastName);
            $customerShippingAddress->setCompany($company);
            $customerShippingAddress->setAddress($address);
            $customerShippingAddress->setCity($city);
            $customerShippingAddress->setState($state);
            $customerShippingAddress->setZip($zip);
            $customerShippingAddress->setCountry($country);
            $customerShippingAddress->setPhoneNumber($user->phone);

            // Create an array of any shipping addresses
            $shippingProfiles[] = $customerShippingAddress;


            // Create a new CustomerPaymentProfile object
            $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
            $paymentProfile->setCustomerType('individual');
            $paymentProfile->setBillTo($billTo);
            $paymentProfile->setPayment($paymentCreditCard);
            $paymentProfiles[] = $paymentProfile;


            // Create a new CustomerProfileType and add the payment profile object
            $customerProfile = new AnetAPI\CustomerProfileType();
            $customerProfile->setDescription("$user->name Subscription Payment");
            $customerProfile->setMerchantCustomerId("M_" . time());
            $customerProfile->setEmail($user->email);
            $customerProfile->setpaymentProfiles($paymentProfiles);
            $customerProfile->setShipToList($shippingProfiles);


            // Assemble the complete transaction request
            $request = new AnetAPI\CreateCustomerProfileRequest();
            $request->setMerchantAuthentication($this->merchantAuthentication);
            $request->setRefId($refId);
            $request->setProfile($customerProfile);

            // Create the controller and get the response
            $controller = new AnetController\CreateCustomerProfileController($request);
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);

            if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
                $paymentProfiles = $response->getCustomerPaymentProfileIdList();

                return [
                    'customer_profile_id' => $response->getCustomerProfileId(),
                    'payment_profile_id' => (string) $paymentProfiles[0]
                ];

            } else {
                // echo "ERROR :  Invalid response\n";
                $errorMessages = $response->getMessages()->getMessage();
                // echo "Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n";
                return ['error' => "Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n"];
            }
        } catch (\Throwable $th) {

            return ['error' => $th->getMessage() . $th->getLine() . ' ' . $th->getFile()];
        }


    }

    public function updateCustomerProfile(
        string $customerProfileId,
        string $customerPaymentProfileId,
        User $user,
        string $cardNumber,
        string $expirationDate,
        string $ccv,
        string $firstName,
        string $lastName,
        string $company,
        string $address,
        string $city,
        string $state,
        string $zip,
        string $country
    ) {
        try {
            // $lat = $user->latitude;
            // $lng = $user->longitude;
            // $components = $this->addressFromCoordinate($lat, $lng);

            // Set the transaction's refId
            $refId = 'ref' . time();

            $request = new AnetAPI\GetCustomerPaymentProfileRequest();
            $request->setMerchantAuthentication($this->merchantAuthentication);
            $request->setRefId($refId);
            $request->setCustomerProfileId($customerProfileId);
            $request->setCustomerPaymentProfileId($customerPaymentProfileId);

            $controller = new AnetController\GetCustomerPaymentProfileController($request);
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
            if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
                $billto = new AnetAPI\CustomerAddressType();
                $billto = $response->getPaymentProfile()->getbillTo();

                $creditCard = new AnetAPI\CreditCardType();
                $creditCard->setCardNumber($cardNumber);
                $creditCard->setExpirationDate($expirationDate);
                $creditCard->setCardCode($ccv);

                $paymentCreditCard = new AnetAPI\PaymentType();
                $paymentCreditCard->setCreditCard($creditCard);
                $paymentprofile = new AnetAPI\CustomerPaymentProfileExType();
                $paymentprofile->setBillTo($billto);
                $paymentprofile->setCustomerPaymentProfileId($customerPaymentProfileId);
                $paymentprofile->setPayment($paymentCreditCard);

                // We're updating the billing address but everything has to be passed in an update
                // For card information you can pass exactly what comes back from an GetCustomerPaymentProfile
                // if you don't need to update that info

                // Update the Bill To info for new payment type
                // Create the Bill To info for new payment type
                $billTo = new AnetAPI\CustomerAddressType();
                // $billTo->setFirstName($user->name);

                // switch ($user->role) {
                //     case "Business":
                //         $billTo->setCompany($user->name);
                //         break;
                //     default:
                //         $billTo->setLastName($user->lastName);
                //         break;
                // }

                // $billTo->setAddress($user->address);
                // $billTo->setCity($components['city'] ?? 'Unknown City');
                // $billTo->setState($components['state'] ?? 'Unknown State');
                // $billTo->setZip($components['zipcode'] ?? '00000');
                // $billTo->setCountry($components['country'] ?? 'Unknown Country');
                // $billTo->setPhoneNumber($user->phone);

                $billTo->setFirstName($firstName);
                $billTo->setLastName($lastName);
                $billTo->setCompany($company);
                $billTo->setAddress($address);
                $billTo->setCity($city);
                $billTo->setState($state);
                $billTo->setZip($zip);
                $billTo->setCountry($country);
                $billTo->setPhoneNumber($user->phone);


                // Update the Customer Payment Profile object
                $paymentprofile->setBillTo($billto);

                // Submit a UpdatePaymentProfileRequest
                $request = new AnetAPI\UpdateCustomerPaymentProfileRequest();
                $request->setMerchantAuthentication($this->merchantAuthentication);
                $request->setCustomerProfileId($customerProfileId);
                $request->setPaymentProfile($paymentprofile);

                $controller = new AnetController\UpdateCustomerPaymentProfileController($request);
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
                if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
                    // $Message = $response->getMessages()->getMessage();
                    // print_r($response);
                    // echo "Update Customer Payment Profile SUCCESS: " . $Message[0]->getCode() . "  " . $Message[0]->getText() . "\n";
                    // echo $paymentprofile->getCustomerPaymentProfileId();
                    return [
                        'customer_profile_id' => $customerProfileId,
                        'payment_profile_id' => $paymentprofile->getCustomerPaymentProfileId()
                    ];
                } else if ($response != null) {
                    $errorMessages = $response->getMessages()->getMessage();
                    return ['error' => "Update Customer Payment Profile ERROR: " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n"];
                }



            } else {
                $errorMessages = $response->getMessages()->getMessage();
                return ['error' => "Update Customer Payment Profile ERROR: " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n"];
            }


        } catch (\Throwable $th) {

            return ['error' => $th->getMessage() . $th->getLine() . ' ' . $th->getFile()];
        }

    }

    public function addressFromCoordinate($latitude, $longitude)
    {
        $apiKey = "AIzaSyBmaS0B0qwokES4a_CiFNVkVJGkimXkNsk";
        $url = "https://maps.googleapis.com/maps/api/geocode/json";

        $response = Http::get($url, [
            'latlng' => "$latitude,$longitude",
            'key' => $apiKey
        ]);

        if ($response->successful()) {
            $results = $response->json()['results'][0]['address_components'] ?? [];

            $components = [
                'city' => '',
                'state' => '',
                'country' => '',
                'zipcode' => ''
            ];

            foreach ($results as $component) {
                $types = $component['types'];

                if (in_array('locality', $types)) {
                    $components['city'] = $component['long_name'];
                }

                if (in_array('administrative_area_level_1', $types)) {
                    $components['state'] = $component['long_name'];
                }

                if (in_array('country', $types)) {
                    $components['country'] = $component['long_name'];
                }

                if (in_array('postal_code', $types)) {
                    $components['zipcode'] = $component['long_name'];
                }
            }

            return $components;
        }

        return null;
    }

    // Process one-time payment using payment profile
    public function processPayment(string $customerProfileId, string $paymentProfileId, float $amount): array
    {
        // Set the transaction's refId
        $refId = 'ref' . time();

        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($customerProfileId);
        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($paymentProfileId);
        $profileToCharge->setPaymentProfile($paymentProfile);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setProfile($profileToCharge);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);

        if ($response && $response->getMessages()->getResultCode() === "Ok") {
            $transactionResponse = $response->getTransactionResponse();
            if ($transactionResponse && $transactionResponse->getResponseCode() == '1') {
                return ['success' => true, 'transaction_id' => $transactionResponse->getTransId()];
            }
            return ['error' => $transactionResponse->getErrors()[0]->getErrorText() ?? 'Transaction Failed'];
        } else {
            return ['error' => $this->getErrorMessages($response)];
        }


    }

    // Create Recurring Subscription
    public function createSubscription(string $customerProfileId, string $paymentProfileId, float $amount, string $intervalUnit = 'months', int $intervalLength = 1, int $totalOccurrences = 9999): string
    {
        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName("Subscription");

        $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
        $interval->setLength($intervalLength);
        $interval->setUnit($intervalUnit); // 'days' or 'months'

        $paymentSchedule = new AnetAPI\PaymentScheduleType();
        $paymentSchedule->setInterval($interval);
        $paymentSchedule->setStartDate(new \DateTime());
        $paymentSchedule->setTotalOccurrences($totalOccurrences);

        $subscription->setPaymentSchedule($paymentSchedule);
        $subscription->setAmount($amount);

        $profile = new AnetAPI\CustomerProfileIdType();
        $profile->setCustomerProfileId($customerProfileId);
        $profile->setCustomerPaymentProfileId($paymentProfileId);

        $subscription->setProfile($profile);

        $request = new AnetAPI\ARBCreateSubscriptionRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setSubscription($subscription);

        $controller = new AnetController\ARBCreateSubscriptionController($request);
        $response = $controller->executeWithApiResponse(config('authorizeNet.environment'));

        if ($response && $response->getMessages()->getResultCode() === "Ok") {
            return $response->getSubscriptionId();
        }

        throw new Exception("Failed to create subscription: " . $this->getErrorMessages($response));
    }

    // Utility to extract error messages from response
    protected function getErrorMessages($response): string
    {
        if (!$response) {
            return 'No response from Authorize.Net API.';
        }
        if ($response->getMessages()) {
            $messages = $response->getMessages()->getMessage();
            if (count($messages)) {
                return $messages[0]->getText();
            }
        }
        return 'Unknown error';
    }
}
