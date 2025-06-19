<?php


use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

function otpMailSend($email_object)
{
    try {
        Mail::send('emails.email_temp', $email_object['email_data'], function ($message) use ($email_object) {
            $message->to($email_object['to_email'])->subject($email_object['to_subject']);
            $message->from(config("constants.mail.MAIL_FROM_ADDRESS"), config("constants.mail.MAIL_FROM_NAME"));
        });
        return 1;
    } catch (\Exception $e) {


        Log::error($e->getMessage());
        return 0;
    }
}

function supportMailSend($email_object)
{

    try {
        Mail::raw($email_object['email_data']["body"], function ($message) use ($email_object) {
            $message->to($email_object['to_email'])
                ->subject($email_object['to_subject']);
            $message->from(config("constants.mail.MAIL_FROM_ADDRESS"), config("constants.mail.MAIL_FROM_NAME"));
        });
        // Mail::send('emails.email_temp', $email_object['email_data'], function ($message) use ($email_object) {
        //     $message->to($email_object['to_email'])->subject($email_object['to_subject']);
        //     $message->from("noreply@mybackyardusa.com", "My Backyard Support");
        // });
        return 1;
    } catch (\Exception $e) {
        return 0;
    }
}


// function sendFirebaseNotification(array $firebaseToken,$data){

//     $SERVER_API_KEY = "AAAAiCmSLUE:APA91bGSW3U8D_4l2m1uEUzRo51_7qsAagofD3lrzYF1UVA8WxK4oth8wfs-tef3pPM94M-Piusl7N32aoqcDopWviEEHXIhfsn-2-izt6QQpkED0klwjV4vs0a52X4dSyAj5ntuO7yX";

//     $data = [
//         "registration_ids" => $firebaseToken,
//         "data" => [
//             "title" => $data['title'],
//             "body" => $data['body'],
//             "type" => $data['type'],
//             "goal_id" => $data['goal_id'] ?? null,
//             "goal_status" => $data['goal_status'] ?? null,
//         ],
//         "notification" => [
//             "title" => $data['title'],
//             "body" => $data['body'],
//             "type" => $data['type'],
//             "goal_id" => $data['goal_id'] ?? null,
//              "goal_status" => $data['goal_status'] ?? null,
//         ]
//     ];
//     $dataString = json_encode($data);
//     $headers = [
//         'Authorization: key=' . $SERVER_API_KEY,
//         'Content-Type: application/json',
//     ];
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
//     return $response = curl_exec($ch);
// }


function push_notification($push_arr)
{
    $apiKey = "BPv18xIRio1N8qbFYSmXxYMGgsJq_r9Dne3v8AJbiiQwFvKijBjhEn_SYXa_2Rdb2IWDm999v4eXXbx1YT7A8Yw";

    $registrationIDs = (array) $push_arr['device_token'];
    $message = array(
        "body" => $push_arr['description'],
        "title" => $push_arr['title'],
        "notification_type" => $push_arr['type'],
        "other_id" => $push_arr['record_id'],
        "date" => now(),
        'vibrate' => 1,
        'sound' => 1,
    );
    $url = 'https://fcm.googleapis.com/fcm/send';

    $fields = array(
        'registration_ids' => $registrationIDs,
        'notification' => $message,
        'data' => $message
    );

    $headers = array(
        'Authorization: key=' . $apiKey,
        'Content-Type: application/json'
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}


function in_app_notification($data)
{
    $notification = new Notification();
    $notification->sender_id = $data['sender_id'];
    $notification->receiver_id = $data['receiver_id'];
    $notification->title = $data['title'];
    $notification->description = $data['description'];
    $notification->record_id = $data['record_id'];
    $notification->type = $data['type'];
    $notification->save();

    return $notification;
}
