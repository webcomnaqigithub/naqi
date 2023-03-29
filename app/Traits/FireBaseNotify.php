<?php

namespace App\Traits;


trait FireBaseNotify
{
    public function sendFirebasNotification($notify_data)
    {
        $firebase_key = env('FCM_SERVER_KEY');

        $dataArr = array(
            'test' => '',
        );

        $data = array(
            "registration_ids" => $notify_data['users_token'],
            "notification" => array(
                "title" => $notify_data['title'],
                'body' => $notify_data['message'],
                'sound' => 'default',
                'badge' => '1',
            ),
            "data" => $dataArr,
        );
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $firebase_key,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        $result = curl_exec($ch);

        curl_close($ch);
        return $result;
    }

}
