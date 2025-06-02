<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use Validator;
use Carbon\Carbon;
use Database\Factories\UserFactory;

class NotificationController extends BaseController
{

    // Enable & Disable Notifications
    public function enableNotifications()
    {
        $user = auth()->user();
        $authId = $user->id;

        // dd($user);

        if ($user->is_push_notify == '1') {
            $update_user = User::whereId($authId)->update(['is_push_notify' => '0']);
            if ($update_user) {
                $user = User::whereId($authId)->first();
                return response()->json([
                    'status' => 1,
                    'message' => 'Notifications Disabled Successfully',
                    'data' => $user,
                ]);
                // return $this->successResponse('Notifications Disabled Successfully', 200);
            } else {
                return $this->errorResponse('Something went wrong.', 400);
            }
        } else if ($user->is_push_notify == '0') {
            $update_user = User::whereId($authId)->update(['is_push_notify' => '1']);
            if ($update_user) {
                $user = User::whereId($authId)->first();
                return response()->json([
                    'status' => 1,
                    'message' => 'Notifications Enabled Successfully',
                    'data' => $user,
                ]);
                // return $this->successResponse('Notifications Enabled Successfully', 200);
            } else {
                return $this->errorResponse('Something went wrong.', 400);
            }
        } else {
            $update_user = User::whereId($authId)->update(['is_push_notify' => '1']);
            if ($update_user) {
                $user = User::whereId($authId)->first();
                return response()->json([
                    'status' => 1,
                    'message' => 'Notifications Enabled Successfully',
                    'data' => $user,
                ]);
                // return $this->successResponse('Notifications Enabled Successfully', 200);
            } else {
                return $this->errorResponse('Something went wrong.', 400);
            }
        }
    }



    /** List notification */
    public function notificationList(Request $request)
    {
        $this->validate($request, [
            'limit' => 'numeric'
        ]);

        $requestLimit = $request->limit;
        $limit = $requestLimit ?? PHP_INT_MAX;

        $notifications = Notification::with('sendBy:id,name,profile_image')->where('receiver_id', auth()->id())
            ->latest()
            ->limit($limit)
            ->get();

        if (count($notifications) > 0) {
            Notification::where(['receiver_id' => auth()->id(), 'read_at' => null, 'seen' => '0'])->update(['read_at' => now(), 'seen' => '1']);
            return $this->sendResponse($notifications,'Notification list found.' , 200);
        } else {
            return $this->sendError('Notification list not found.', 400);
        }
    }

    public function markAllNotificationRead(Request $request)
    {
        $notifications = Notification::where(['receiver_id' => auth()->id(), 'read_at' => null, 'seen' => '0'])->get();
        try {
            if ($notifications->isNotEmpty()) {
                    Notification::query()->where('receiver_id', auth()->id())->update(['read_at' => now(),'seen' => '1']);
                    $notifications = Notification::where(['receiver_id' => auth()->id()])->get();

                return $this->sendResponse($notifications, "All notifications has been marked as read Successfully.");
            } else {
                return $this->sendError('No unread notifications found in your records.');
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() . 'Error occurred while marking all notifications as read.');
        }
    }



    public function deleteNotificationById($id)
    {
        try {
            $notify = Notification::where('id', $id)->first();
            if($notify){
                $notify->delete();
                return $this->sendResponse(null, "Notification deleted successfully.");
            } else {
                return $this->sendError('Notification record not found.');
            }
        } catch(\Exception $e) {
            return $this->sendError('Error occurred while deleting notifications.');
        }

    }
}
