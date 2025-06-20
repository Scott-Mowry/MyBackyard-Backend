<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\UserFavoriteWords;
use Illuminate\Support\Facades\Storage;
use Validator;
use Carbon\Carbon;
use Database\Factories\UserFactory;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            // 'role' => 'required|in:User,Business'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }


        $checkUser = User::whereEmail($request->email)->latest()->first();
        //signup
        // $code = '123456';
        $code = '' . random_int(100000, 999999) . '';
        $email_object = [
            "to_email" => $request->email,
            "to_subject" => "Confirmation emails",
            "email_data" => array(
                "name" => "Confirmation emails",
                "body" => "Verification code is this: " . $code,
                "APP_NAME" => env("APP_NAME")
            )
        ];
        if (!$checkUser) {
            $emailSend = otpMailSend($email_object);
            if ($emailSend == 1) {
                $newUser = new User();
                $newUser->role = "User";
                $newUser->email = $request->email;
                $newUser->email_otp = $code;
                $newUser->password = Hash::make($request->password);
                $newUser->device_type = $request->device_type;
                $newUser->device_token = $request->device_token;
                $newUser->is_verified = 0;
                $newUser->save();
                // $user_id = $newUser->id;
                return $this->sendResponse(array(
                    "user" => $newUser
                ), 'OTP verification code has been sent to your Email Address');
            } else {
                return $this->sendError("Please input the valid Email Address");
            }
        } else {
            //login
            if ($checkUser->is_blocked == 1) {
                return $this->sendError("Your account has been deleted.");
            }
            // $role = $checkUser->role;
            // if ($request->role != $role) {
            //     return $this->sendError("This is a " . $role . " Account.");
            // }

            if (Hash::check($request->password, $checkUser->password)) {
                if ($checkUser->is_verified == 0) {
                    $emailSend = otpMailSend($email_object);
                    if ($emailSend == 1) {
                        $data = [];
                        $data = ['email_otp' => $code, 'email_verified_at' => null];
                        $checkUser->update($data);
                        return $this->sendResponse(array(
                            "user" => $checkUser
                        ), 'OTP verification code has been sent to your Email Address');

                    } else {
                        return $this->sendError("Error in sending email. ");
                    }
                } else {
                    $checkUser->tokens()->delete();
                    $token = $checkUser->createToken('AuthToken');
                    $checkUser["bearer_token"] = $token->plainTextToken;
                    if ($checkUser->role == 'Business') {
                        $days = Schedule::where('owner_id', $checkUser->id)->get();
                        $checkUser["days"] = $days;
                    }
                    return $this->sendResponse(
                        array(
                            "user" => $checkUser
                        ),
                        'Logged in Successfully'
                    );
                }
            } else {
                return $this->sendError("Invalid Password. ");
            }
        }
    }

    public function getUser(Request $request)
    {
        $user = User::whereId(auth()->user()->id)->latest()->first();
        if ($user) {
            if ($user->role == 'Business') {
                $days = Schedule::where('owner_id', $user->id)->get();
                $user["days"] = $days;
            }
            return $this->sendResponse(
                array(
                    "user" => $user
                ),
                'User Found'
            );

        } else {
            return $this->sendError('Sorry! There is no user found.');
        }

    }


    public function verification(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'user_id' => 'required|max:255|exists:users,id',
            'otp' => 'required|min:6|max:6',
            'device_type' => 'in:ios,android,web',
        ]);
        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }
        $user = User::whereId($request->user_id)->whereEmailOtp($request->otp)->latest()->first();
        if ($user) {
            $deviceType = $request->device_type ?? null;
            $deviceToken = $request->device_token ?? null;
            $update_user = $user->update(['is_verified' => 1, 'is_forgot' => 0, 'device_type' => $deviceType, 'device_token' => $deviceToken, 'email_otp' => null, 'email_verified_at' => Carbon::now()]);
            if ($update_user) {
                $user_obj = User::find($request->user_id);
                // Revoke other tokens upon successful login
                $user_obj->tokens()->delete();
                $token = $user_obj->createToken('AuthToken');
                $user_obj["bearer_token"] = $token->plainTextToken;
                if ($user->role == 'Business') {
                    $days = Schedule::where('owner_id', $user->id)->get();
                    $user_obj["days"] = $days;
                }
                return $this->sendResponse(array(
                    // "bearer_token" => $token->plainTextToken,
                    "user" => $user_obj
                ), 'OTP verified.');
            } else {
                return $this->sendError('Sorry! There is an error while updating data.');
            }

        } else {
            return $this->sendError('Invalid OTP verification code.');
        }

    }

    public function changePassword(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|max:255|exists:users,id',
            'password' => 'required'
        ]);
        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $user = User::whereId($request->id)->latest()->first();
        if ($user) {

            $user->update(['password' => Hash::make($request->password)]);

            return $this->sendResponse(array(
                "user" => $user
            ), 'Password Changed Successfully');

        } else {
            return $this->sendError('Sorry! There is no user found.');
        }
    }

    public function forgotPassword(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);
        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $user = User::whereEmail($request->email)->latest()->first();
        if ($user) {
            $user_object = $user;
            $code = '' . mt_rand(100000, 900000) . '';
            // $code = '123456';
            $user_object->update(['email_otp' => $code]);
            $email_object = [
                "to_email" => $user_object->email,
                "to_subject" => "Password resets",
                "email_data" => array(
                    "name" => "Password resets",
                    "body" => "We received a request to reset the password for your account. Please use the verification code below to change password." . $code,
                    "APP_NAME" => env("APP_NAME")
                )
            ];
            $email_response = otpMailSend($email_object);
            if ($email_response > 0) {
                $user->is_forgot = 1;
                $user->save();
                return $this->sendResponse(array(
                    "user" => $user
                ), 'We have send OTP verification code at your Email Address.');
            } else {
                return $this->sendError('Sorry there is some problem while sending email.');

            }
        } else {
            return $this->sendError('Sorry! There is no user found.');
        }
    }

    public function supportAPI(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'message' => 'required|min:30|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        $email_object_1 = [
            "to_email" => $request->email,
            "to_subject" => "My Backyard Support",
            "email_data" => array(
                "name" => "My Backyard Support",
                "body" => "Your Support is been sent to My Backyard support team, they'll contact you soon.\n\nThanks for reaching us for support.\n\nRegards,\nSupport Team,\nMy Backyard USA.",
                "APP_NAME" => env("APP_NAME")
            )
        ];
        $email_object_2 = [
            "to_email" => "mybackyardusa.help@gmail.com",
            "to_subject" => "Support Request",
            "email_data" => array(
                "name" => "Support Request",
                "body" => "{$request->email} submitted a support request from website support.\n\nReason:\n{$request->message}\n\nRegards,\nSupport Page,\nhttps://admin.mybackyardusa.com/html/Help",
                "APP_NAME" => env("APP_NAME")
            )
        ];
        $email_response = supportMailSend($email_object_1);
        $email_response = supportMailSend($email_object_2);

        if ($email_response == 1) {
            return $this->sendMessage('Support Request Submitted Successfully.');
        } else {
            return $this->sendError("Please input the valid Email Address");
        }
    }

    public function reSendCode(Request $request)
    {

        $valid = Validator::make($request->all(), [
            'user_id' => 'required|max:255|exists:users,id'
        ]);
        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $user = User::whereId($request->user_id)->latest()->first();
        if ($user) {
            $user_object = $user;
            if ($user_object->email_verified_at != null) {
                return $this->sendError('Already verified.');
            } else {
                // $code = '' . mt_rand(100000, 900000) . '';
                $code = '123456';
                $user_object->update(['email_otp' => $code]);
                $email_object = [
                    "to_email" => $user_object->email,
                    "to_subject" => "Resend Verification",
                    "email_data" => array(
                        "name" => "Resend Verification",
                        "body" => "Verification code is this: " . $code,
                        "APP_NAME" => env("APP_NAME")
                    )
                ];
                $email_response = otpMailSend($email_object);
                if ($email_response > 0) {
                    return $this->sendResponse(null, 'We have resend OTP verification code at your Email Address.');
                } else {
                    return $this->sendError('Sorry there is some problem while sending email.');

                }
            }
        } else {
            return $this->sendError('Sorry! There is no user found.');
        }
    }


    public function logout()
    {
        $user = User::whereId(auth()->user()->id)->latest()->first();
        if ($user) {

            $tokenDelete = auth()->user()->currentAccessToken()->delete();
            if ($tokenDelete == true) {
                $user->tokens()->delete();
                $update_user = $user->update(['device_type' => null, 'device_token' => null]);
                if ($update_user) {
                    return $this->sendResponse(null, 'User logout successfully.');
                    //   return $this->sendResponse(null,null);
                } else {
                    return $this->sendError('Sorry there is some problem while updating user data.');
                }
            } else {
                return $this->sendError('Sorry there is some problem while deleting old token.');
            }
        } else {
            return $this->sendError("Sorry this user id is incorrect");
        }
    }




    public function deleteAccount()
    {

        $delete = User::findOrFail(auth()->user()->id);

        $delete->is_blocked = 1;

        if ($delete->save()) {
            // dd($delete);
            return $this->sendResponse($delete, 'Your account has been deleted successfully.');
        } else {
            return $this->sendError('Sorry there is problem in deleteing your account.');
        }

    }


    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'social_token' => 'required',
            'phone' => 'required_if:social_type,phone',
            'social_type' => 'required|in:phone,google,apple',
            'role' => 'required|in:User,Business'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        $user = User::whereSocialToken($request->social_token)->latest()->first();
        if (!$user) {
            //create an account
            $newUser = new User();
            $newUser->social_token = $request->social_token;
            $newUser->social_type = $request->social_type;
            $newUser->role = $request->role;
            $newUser->phone = $request->phone;
            $newUser->device_token = $request->device_token ?? null;
            $newUser->device_type = $request->device_type ?? null;
            $newUser->email = $request->email ?? null;
            $newUser->name = $request->first_name ?? null;
            $newUser->last_name = $request->last_name ?? null;
            $newUser->save();
            $userId = $newUser->id;
            $user_obj = User::find($userId);
            // Revoke other tokens upon successful login
            $user_obj->tokens()->delete();
            $token = $user_obj->createToken('AuthToken');
            return $this->sendResponse(array(
                "bearer_token" => $token->plainTextToken,
                "user" => $user_obj
            ), 'Signup successfully.');

        } else {
            if ($user->is_blocked == 1) {
                return $this->sendError("Your account has been deleted.");
            }
            $user_obj = $user;
            // Revoke other tokens upon successful login
            $user_obj->tokens()->delete();
            $token = $user_obj->createToken('AuthToken');

            $updateRecord = ['device_token' => $request->device_token ?? null, 'device_type' => $request->device_type ?? null];
            $update = $user_obj->update($updateRecord);
            $user_obj["bearer_token"] = $token->plainTextToken;
            return $this->sendResponse(array(
                // "bearer_token" => $token->plainTextToken,
                "user" => $user_obj
            ), 'Signin successfully.');
        }
    }

    public function loginWithId(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'user_id' => 'required|max:255|exists:users,id'
        ]);
        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $user = User::whereId($request->user_id)->latest()->first();
        if ($user) {
            $user->tokens()->delete();
            $token = $user->createToken('AuthToken');
            $user["bearer_token"] = $token->plainTextToken;
            if ($user->role == 'Business') {
                $days = Schedule::where('owner_id', $user->id)->get();
                $user["days"] = $days;
            }
            return $this->sendResponse(
                array(
                    "user" => $user
                ),
                'Logged in Successfully'
            );

        } else {
            return $this->sendError('Sorry! There is no user found.');
        }
    }

    public function completeProfile(Request $request)
    {
        $user = auth()->user();
        $valid = Validator::make($request->all(), [
            'is_push_notify' => 'in:0,1',
            'profile_image' => 'mimes:jpeg,jpg,jpe,png,svg,svgz,tiff,tif,webp',
            'email' => 'unique:users,email,' . $user->id,
            'phone' => 'unique:users,phone,' . $user->id
        ]);

        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        if (($user->role == 'Business' || $request->role == 'Business') && $user->is_profile_completed == 0) {
            $valid = Validator::make($request->all(), [
                'days' => 'required|array',
                'days.*.start_time' => 'required|date_format:H:i',
                'days.*.end_time' => 'required|date_format:H:i',
                'days.*.day' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                        $days = request('days');
                        $dayCounts = array_count_values(array_column($days, 'day'));

                        if (!in_array($value, $validDays)) {
                            $fail("The $attribute must be one of: " . implode(', ', $validDays));
                        } elseif ($dayCounts[$value] > 1) {
                            $fail("The $attribute '$value' is repeated in the 'days' array.");
                        }
                    }
                ]
            ]);

            if ($valid->fails()) {
                return $this->sendError($valid->errors()->first());
            }
        }

        $message = 'Profile has been updated successfully.';
        if (($request->name == null || $request->name == '') && ($request->bio == null || $request->bio == '') && ($request->profile_image == null || $request->profile_image == '') && ($request->phone == null || $request->phone == '') && ($request->email == null || $request->email == '')) {
            if ($request->is_push_notify == 1) {
                $message = 'Notifications turned on successfully.';
            } else {
                $message = 'Notifications turned off successfully.';
            }
        }
        if ($user->is_profile_completed == 0) {
            $message = 'Profile has been completed successfully.';
        }

        $user = auth()->user();
        $data = [];
        if ($request->hasFile('profile_image')) {
            $img = $request->profile_image;
            $path = $img->store('public/user/profile');
            $file_path = Storage::url($path);
            $data['profile_image'] = $file_path;
        }
        $data += [
            'role' => $request->role ?? $user->role,
            'name' => $request->name ?? $user->name,
            'is_push_notify' => $request->is_push_notify ?? $user->is_push_notify,
            'phone' => $request->phone ?? $user->phone,
            'email' => $request->email ?? $user->email,
            'last_name' => $request->last_name ?? $user->last_name,
            'is_profile_completed' => 1,
            'zip_code' => $request->zip_code ?? $user->zip_code
        ];
        if ($user->role == 'Business' || $request->role == 'Business') {
            if ($request->has('days')) {
                $old_records = Schedule::where('owner_id', $user->id)->get();
                if ($old_records->isNotEmpty()) {
                    foreach ($old_records as $record) {
                        $record->delete();
                    }
                }
                foreach ($request->days as $day) {
                    $details = new Schedule();
                    $details->owner_id = auth()->id();
                    $details->day = $day['day'];
                    $details->start_time = $day['start_time'];
                    $details->end_time = $day['end_time'];
                    $details->save();
                }
            }
            $data += [
                'address' => $request->address ?? $user->address,
                'latitude' => $request->latitude ?? $user->latitude,
                'longitude' => $request->longitude ?? $user->longitude,
                'category_id' => $request->category_id ?? $user->category_id,
                'description' => $request->description ?? $user->description,
            ];
        }

        if ($request->sub_id != null) {
            $data += ['sub_id' => $request->sub_id];
        }

        $user->update($data);
        if ($user->role == 'Business') {
            $days = Schedule::where('owner_id', $user->id)->get();
            $user["days"] = $days;
        }
        return $this->sendResponse(array("user" => $user), $message);
    }

    public function submitReview(Request $request)
    {
        $user = auth()->user();
        $valid = Validator::make($request->all(), [
            'bus_id' => 'required',
            'rate' => 'required',
            'feedback' => 'required'
        ]);

        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $review = new Review();
        $review->user_id = $user->id;
        $review->bus_id = $request->bus_id;
        $review->rate = $request->rate;
        $review->feedback = $request->feedback;
        if ($review->save()) {
            $review['user'] = $user;
            unset($review['user_id']);
            return $this->sendResponse(array(
                "review" => $review
            ), 'Review Submitted Successfully.');
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }

    public function getReviews(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'bus_id' => 'required'
        ]);
        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }
        $page = $request->page;
        $reviews = Review::where('bus_id', $request->bus_id)->limit($page ?? 10)->get();
        if ($reviews != null) {
            $total = 0.00;
            foreach ($reviews as $review) {
                $total += (double) $review->rate;
                $user = User::findOrFail($review->user_id);
                $review['user'] = $user;
                unset($review["user_id"]);
            }
            // $reviews->map(function ($review) use ($total) {
            //     $total += 5;
            //     $user = User::findOrFail($review->user_id);
            //     $review['user'] = $user;
            //     unset($review["user_id"]);
            //     return $review;
            // });
            return $this->sendResponse(array(
                "ratings" => $reviews->count() != 0 ? ($total / $reviews->count()) : 0,
                "reviews" => $reviews
            ), 'All Reviews Fetched Successfully.');
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }

    public function deleteReview(Request $request)
    {
        $user = auth()->user();
        $valid = Validator::make($request->all(), [
            'review_id' => 'required'
        ]);

        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $review = Review::find($request->review_id);

        if ($review) {
            if ($review->user_id != auth()->user()->id) {
                return $this->sendError("You're not the user of this review.");
            }

            if ($review->delete()) {
                return $this->sendResponse(array(
                ), 'Review Deleted successfully.');
            } else {
                return $this->sendError("Unable to process your request at the moment.");
            }
        } else {
            return $this->sendError("Review doesn't not exist");
        }

    }



    // public function getNotifications()
    // {
    //   $notify=auth()->user()->notifications();
    //     if (request()->filled('per_page')) {
    //         $notify = $notify->paginate(request('per_page', 20));
    //     }
    //     else
    //     {
    //         $notify=$notify->paginate(20);
    //     }
    //     // $notify=$notify->get();
    //      $formattedNotifications = $notify->map(function ($notification) {
    //         return [
    //             "title" => $notification->data["title"] ?? null,
    //             "body" => $notification->data["body"] ?? null,
    //             "type" => $notification->data["type"] ?? null,
    //             "goal_id" => $notification->data["goal_id"] ?? null,
    //             "read_at"=>$notification->read_at ?? null,
    //             "created_at"=>$notification->created_at ?? null,
    //             "id"=>$notification->id
    //         ];
    //     });
    //     return $this->sendResponse($formattedNotifications,null);

    // }

    // public function markAllNotificationRead(Request $request)
    // {
    //     try{
    //         $notifications=auth()->user()->unreadNotifications;
    //         if ($notifications->isNotEmpty()) {
    //             $notifications->each(function ($notification) {
    //                 $notification->markAsRead();
    //             });

    //             return $this->sendResponse(null,"All notifications has been marked as read Successfully.");
    //         }
    //         else{
    //             return $this->sendError('No unread notifications found in your records.');
    //         }
    //     }
    //     catch(\Exception $e)
    //     {
    //         return $this->sendError($e->getMessage().'Error occurred while marking all notifications as read.');
    //     }
    // }


    // public function deleteNotificationById($id)
    // {
    //     try {
    //         $notify = auth()->user()->notifications()->where('id', $id)->first();
    //         if($notify){
    //             $notify->delete();
    //             return $this->sendResponse(null, "Notification deleted successfully.");
    //         } else {
    //             return $this->sendError('Notification record not found.');
    //         }
    //     } catch(\Exception $e) {
    //         return $this->sendError('Error occurred while deleting notifications.');
    //     }

    // }


    public function addFavWord(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'word_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }


        $userID = auth()->user()->id;

        $userFavWord = UserFavoriteWords::where('word_dictionary_id', '=', $request->word_id)->where('user_id', '=', $userID)->first();
        $isFav = 1;
        if (!empty($userFavWord)) {
            if ($userFavWord->is_favorite == 1) {
                $isFav = 0;
            }
        } else {
            $userFavWord = new UserFavoriteWords();
        }

        $userFavWord->word_dictionary_id = $request->word_id;
        $userFavWord->user_id = $userID;
        $userFavWord->is_favorite = $isFav;
        // dd($userFavWord);


        if ($userFavWord->save()) {
            return $this->sendResponse(array(
                "userfavword" => $userFavWord
            ), 'Word added to favorites successfully.');
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }


    public function userFavoriteWordsList()
    {
        $userID = auth()->user()->id;

        $userFavWords = UserFavoriteWords::where('user_id', '=', $userID)->where('is_favorite', '=', 1)->with('wordsDictionary')->get();

        if (count($userFavWords) >= 1) {
            return response()->json([
                'status' => 1,
                'message' => 'User Favorite Word(s) found.',
                'data' => $userFavWords,
            ]);
        } else {

            return response()->json([
                'status' => 0,
                'message' => 'User Favorite Word(s) not found...!'
            ]);
        }

    }
}
