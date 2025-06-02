<?php
namespace App\Http\Controllers\API;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Subscription;
use ReceiptValidator\iTunes\Validator as iTunesValidator;
use ReceiptValidator\GooglePlay\Validator as PlayValidator;
use Google_Client;
use App\Models\Package;
use App\Http\Controllers\BaseController as BaseController;
class SubscriptionController extends BaseController
{
    

    public function addSubscription(Request $request)
    {
        $user = auth()->user();
        $controls=$request->all();
        $rules=array(
            'receipt' => "required", 
            'type' => 'required|in:monthly', //1, 3 , 6 months
            'source' => 'required|in:google,apple', //google or apple
            'package'=>'required|in:basic,gold,platinum'
        );

        $validator = Validator::make($controls,$rules);
        if($validator->fails())
            {
                return $this->sendError($validator->errors()->first());
            }
        $check = Subscription::where('type',$request->type)->where('user_id',$user->id)->where('source',$request->source)->where('receipt',$request->receipt)
        ->wherePackage($request->package)
        ->where('expiry_date','>',Carbon::now()->toDateString())
        ->latest()->first();
        if($check)
        {
            
            $data = [
                "data" => $check,
                "user" => $user
            ];
            $user = User::find($user->id);
           
           return $this->sendError("You have already subscribed to this package.");
        
            
          
        }

        $check2 = Subscription::where('user_id',$user->id)->where('source',$request->source)->first();
        if($check2)
        {
            $del = Subscription::where('user_id',$user->id)->where('source',$request->source)->delete();
        }

        $receipt = $request->receipt;
        

        if($request->source == 'apple')
        {
            // $this->check_ios($request->receipt);
            $auth_receipt = $this->check_ios($receipt);
            
            // dd($auth_receipt);
        }
        elseif($request->source == 'google')
        {
            // $auth_receipt = $this->check_android_session($receipt, $request->type,$request->package_name,$request->purchase_token);
            
            // $auth_receipt = $this->check_android($receipt, $request->type,$request->package_name,$request->purchase_token);
            $auth_receipt = $receipt;
        }

        //Check subscription type
        if($request->type == 'monthly'){ // monthly
            $expiry_date = Carbon::now()->addMonths(1);
        }
        // elseif($request->type == 'quarterly'){ // 3 months
        //     $expiry_date = Carbon::now()->addMonths(3);
        // }
        // elseif($request->type == 'half_yearly'){ // 6 months
        //     $expiry_date = Carbon::now()->addMonths(6);
        // }
        // elseif($request->type == 'yearly'){ // 12 months
        //     $expiry_date = Carbon::now()->addMonths(12);
        // }
        else{
            $expiry_date = Carbon::now(); // Default
        }

        $subscription = new Subscription;
        $subscription->user_id = $user->id;
        $subscription->type = $request->type;
        $subscription->expiry_date = $expiry_date;
        $subscription->receipt = $request->receipt;
        $subscription->source = $request->source;
        $subscription->package = $request->package;

        if($subscription->save())
        {
            if($subscription->package=='basic'){
            $package=Package::whereType('Basic')->latest()->first();
                $user->is_subscribed = 1;
                $user->allow_goal=$package->allow_goal;
            }
            if($subscription->package=='gold'){
                $package=Package::whereType('Gold')->latest()->first();
                $user->is_subscribed = 2;
                $user->allow_goal=$package->allow_goal;
            }
            if($subscription->package=='platinum'){
                $package=Package::whereType('Platinum')->latest()->first();
                $user->is_subscribed = 3;
                $user->allow_goal=$package->allow_goal;
            }
               
            $user->save();
            // $user = User::where('id',$user->id)->update(['isSubscribed' => 1]);

            $data = [
               
                "user" => auth()->user()
            ];
        
            return $this->sendResponse($data,'Subscribed successfully!');
        }
        else
        {
            return $this->sendError('Error in Subscription.');
        }


    }


    public function check_ios($receipt)
    {
        $validator = new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION); // Or iTunesValidator::ENDPOINT_SANDBOX if sandbox testing

        $receiptBase64Data = $receipt;

        try {
          $response = $validator->setReceiptData($receiptBase64Data)->validate();
          // $sharedSecret = '1234...'; // Generated in iTunes Connect's In-App Purchase menu
          // $response = $validator->setSharedSecret($sharedSecret)->setReceiptData($receiptBase64Data)->validate(); // use setSharedSecret() if for recurring subscriptions
        } catch (Exception $e) {
          echo 'got error = ' . $e->getMessage() . PHP_EOL;
        }
// dd($response);
        if ($response->isValid()) {
          // echo 'Receipt is valid.' . PHP_EOL;
          // echo 'Receipt data = ' . print_r($response->getReceipt()) . PHP_EOL;
            
            return $response->getReceipt();
        
          foreach ($response->getPurchases() as $purchase) {
              
            echo 'getProductId: ' . $purchase->getProductId() . PHP_EOL;
            echo 'getTransactionId: ' . $purchase->getTransactionId() . PHP_EOL;

            if ($purchase->getPurchaseDate() != null) {
              echo 'getPurchaseDate: ' . $purchase->getPurchaseDate()->toIso8601String() . PHP_EOL;
            }
          }
        } else {
          // echo 'Receipt is not valid.' . PHP_EOL;
          // echo 'Receipt result code = ' . $response->getResultCode() . PHP_EOL;
          return $response->getResultCode();
        }
        
    }

  
}