<?php

namespace App\Services;

use App\Models\User;
use Http;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Exception;

class AuthorizeNetService
{
    protected $merchantAuthentication;

    public function __construct()
    {
        $this->merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $this->merchantAuthentication->setClientKey(config('authorizeNet.client_key'));
        $this->merchantAuthentication->setName(config('authorizeNet.login_id'));
        $this->merchantAuthentication->setTransactionKey(config('authorizeNet.transaction_key'));
    }

    // Create Customer Profile
    public function createCustomerProfile(User $user, string $cardNumber, string $expirationDate, string $ccv): array
    {
        $lat = $user->latitude;
        $lng = $user->longitude;
        $components = $this->addressFromCoordinate($lat, $lng);

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
        $billTo->setFirstName($user->name);

        switch ($user->role) {
            case "Business":
                $billTo->setCompany($user->name);
                break;
            default:
                $billTo->setLastName($user->lastName);
                break;
        }

        $billTo->setAddress($user->address);
        $billTo->setCity($components['city'] ?? 'Unknown City');
        $billTo->setState($components['state'] ?? 'Unknown State');
        $billTo->setZip($components['zipcode'] ?? '00000');
        $billTo->setCountry($components['country'] ?? 'Unknown Country');
        $billTo->setPhoneNumber($user->phone);

        // Create a customer shipping address
        $customerShippingAddress = new AnetAPI\CustomerAddressType();
        $customerShippingAddress->setFirstName($user->name);
        switch ($user->role) {
            case "Business":
                $customerShippingAddress->setCompany($user->name);
                break;
            default:
                $customerShippingAddress->setLastName($user->lastName);
                break;
        }
        $customerShippingAddress->setAddress($user->address);
        $customerShippingAddress->setCity($components['city'] ?? 'Unknown City');
        $customerShippingAddress->setState($components['state'] ?? 'Unknown State');
        $customerShippingAddress->setZip($components['zipcode'] ?? '00000');
        $customerShippingAddress->setCountry($components['country'] ?? 'Unknown Country');
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
        $customerProfile->setDescription("$user Subscription Payment");
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
            return [
                'customer_profile_id' => $response->getCustomerProfileId(),
                'payment_profile_id' => $response->getCustomerPaymentProfileId()
            ];

        }
        // else {
        //     echo "ERROR :  Invalid response\n";
        //     $errorMessages = $response->getMessages()->getMessage();
        //     echo "Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n";
        // }
        return null;

    }

    public function updateCustomerProfile(
        User $user,
        string $cardNumber,
        string $expirationDate,
        string $ccv
    ) {
        $lat = $user->latitude;
        $lng = $user->longitude;
        $components = $this->addressFromCoordinate($lat, $lng);

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
        $billTo->setFirstName($user->name);

        switch ($user->role) {
            case "Business":
                $billTo->setCompany($user->name);
                break;
            default:
                $billTo->setLastName($user->lastName);
                break;
        }

        $billTo->setAddress($user->address);
        $billTo->setCity($components['city'] ?? 'Unknown City');
        $billTo->setState($components['state'] ?? 'Unknown State');
        $billTo->setZip($components['zipcode'] ?? '00000');
        $billTo->setCountry($components['country'] ?? 'Unknown Country');
        $billTo->setPhoneNumber($user->phone);

        // Create a Customer Profile Request
        //  1. create a Payment Profile
        //  2. create a Customer Profile   
        //  3. Submit a CreateCustomerProfile Request
        //  4. Validate Profiiel ID returned

        $paymentprofile = new AnetAPI\CustomerPaymentProfileType();

        $paymentprofile->setCustomerType('individual');
        $paymentprofile->setBillTo($billTo);
        $paymentprofile->setPayment($paymentCreditCard);
        $paymentprofiles[] = $paymentprofile;
        $customerprofile = new AnetAPI\CustomerProfileType();
        $customerprofile->setDescription("Update Customer Profile Request Test for PHP");
        $merchantCustomerId = time() . rand(1, 150);
        $customerprofile->setMerchantCustomerId($merchantCustomerId);
        $customerprofile->setEmail(rand(0, 10000) . "@test.com");
        $customerprofile->setPaymentProfiles($paymentprofiles);

        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($refId);
        $request->setProfile($customerprofile);
        $controller = new AnetController\CreateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            echo "SUCCESS: CreateCustomerProfile PROFILE ID : " . $response->getCustomerProfileId() . "\n";

            $profileidcreated = $response->getCustomerProfileId();
        } else {
            echo "ERROR :  CreateCustomerProfile: Invalid response\n";
        }

        // Update an existing customer profile
        $updatecustomerprofile = new AnetAPI\CustomerProfileExType();
        $updatecustomerprofile->setCustomerProfileId($profileidcreated);
        $updatecustomerprofile->setDescription("Updated existing Profile Request");
        $updatecustomerprofile->setEmail($user->email);

        $request = new AnetAPI\UpdateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setProfile($updatecustomerprofile);

        $controller = new AnetController\UpdateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            echo "UpdateCustomerProfile SUCCESS : " . "\n";
            // Validate the description and e-mail that was updated
            $request = new AnetAPI\GetCustomerProfileRequest();
            $request->setMerchantAuthentication($this->merchantAuthentication);
            $request->setCustomerProfileId($profileidcreated);
            $controller = new AnetController\GetCustomerProfileController($request);
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
            if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {

                $profileselected = $response->getProfile();

                return [
                    'customer_profile_id' => $profileidcreated
                ];
            } else {
                echo "ERROR :  GetCustomerProfile: Invalid response\n";
                $errorMessages = $response->getMessages()->getMessage();
                echo "Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n";
            }
        } else {
            echo "ERROR :  UpdateCustomerProfile: Invalid response\n";
            $errorMessages = $response->getMessages()->getMessage();
            echo "Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n";
        }
        return null;
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
            return ['success' => false, 'error' => $transactionResponse->getErrors()[0]->getErrorText() ?? 'Transaction Failed'];
        }

        return ['success' => false, 'error' => $this->getErrorMessages($response)];
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
