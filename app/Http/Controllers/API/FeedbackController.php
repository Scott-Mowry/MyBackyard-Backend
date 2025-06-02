<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\UserFeedback;
use Illuminate\Support\Facades\Storage;
use Validator;
use Carbon\Carbon;
use Database\Factories\UserFactory;

class FeedbackController extends BaseController
{

    public function addFeedback(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'rate' => 'required',
            'feedback' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }


        $userID = auth()->user()->id;

        $userFeedBack = UserFeedback::where('user_id','=',$userID)->first();
        $status = 'Pending';
        if(!empty($userFeedBack)){
            if($userFeedBack->status != 'Pending'){
                $status = 'Pending';
            }
        }else{
            $userFeedBack = new UserFeedback();
        }

        $userFeedBack->user_id = $userID;
        $userFeedBack->rate = $request->rate;
        $userFeedBack->feedback = $request->feedback;
        $userFeedBack->status = $status;
        // dd($userFeedBack);


        if ($userFeedBack->save()) {
            return $this->sendResponse(array(
                "userFeedback" => $userFeedBack
            ), 'Feedback Submitted successfully.');
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }


    public function userFeedback()
    {
        $userID = auth()->user()->id;
        $user_feedback = UserFeedback::where('user_id', '=', $userID)->first();

        if (!$user_feedback) {
            return response()->json([
                'status' => 0,
                'message' => 'User Feedback not found.',
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => 'User Feedback found.',
            'data' => $user_feedback,
        ]);
    }

}
