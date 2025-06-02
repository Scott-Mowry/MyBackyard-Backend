<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Goal;
use Carbon\Carbon;
use App\Notifications\UserNotification;
use App\Models\User;
class CronController extends Controller
{
    
    public function reminderNotify()
    {
        $goals = Goal::where('reminder_status', 'Pending')->where('is_reminder_on',1)->where('status','Pending')
        ->where(function ($query) {
            $query->where('reminder_date', Carbon::today())
                ->where('reminder_time', '<=', Carbon::now()->format('H:i:s'));
        })->get();

  
    if($goals->isNotEmpty()){
        foreach ($goals as $goal) {
                $update=$goal->update(['reminder_status' => 'Done']);
        
                $user=User::find($goal->user_id);
                $user->notify(new UserNotification("Goal Reminder: Check your goal '".$goal->title."'","Goal Reminder: Check your goal '".$goal->title."'",'goal',$goal->id,$goal->status));
                // Firebase notification for user 
            $token = (array) $user->device_token;
            $dataPush=[
                'title'=>"Goal Reminder: Check your goal '".$goal->title."'",
                'body'=>"Goal Reminder: Check your goal '".$goal->title."'",
                'type'=>'goal',
                'goal_id'=>$goal->id,
                 'goal_status'=>$goal->status
            ];
            if($user->is_push_notify==1){
                $res = sendFirebaseNotification($token,$dataPush);
            }

        }
    }

}


  public function notifyBeforeDateEnd()
  {
        $goals = Goal::where('status', 'Pending')->where('before_ending_notify','Pending')
        ->where(function ($query) {
            $query->where('end_date', '<=', Carbon::now()->addHours(24));
        })
        ->get();


if($goals->isNotEmpty()){
    foreach ($goals as $goal) {
            $update=$goal->update(['before_ending_notify' => 'Done']);
    
            $user=User::find($goal->user_id);


            $user->notify(new UserNotification("Goal Alert: Your '".$goal->title."' ends in 24 hours","Goal Alert: Your '".$goal->title."' ends in 24 hours",'goal',$goal->id,$goal->status));
            // Firebase notification for user 
        $token = (array) $user->device_token;
        $dataPush=[
            'title'=>"Goal Alert: Your '".$goal->title."' ends in 24 hours",
            'body'=>"Goal Alert: Your '".$goal->title."' ends in 24 hours",
            'type'=>'goal',
            'goal_id'=>$goal->id,
            'goal_status'=>$goal->status
        ];
        if($user->is_push_notify==1){
            $res = sendFirebaseNotification($token,$dataPush);
        }

    }
}
  }

}
