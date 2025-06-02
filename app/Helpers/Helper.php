<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\Setting;

class Helper
{

    public static function push_notification($push_arr)
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

    public static function in_app_notification($data)
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


    // Application setting table
    // public static function getSetting()
    // {
    //     return Setting::first();
    // }


    public static function generateUniqueRandomString()
    {
        return md5(uniqid(rand(), true));
    }
}
