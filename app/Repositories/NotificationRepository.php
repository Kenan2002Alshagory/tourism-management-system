<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Models\TokenDevice;
use App\Repositories\interface\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface{
    protected static function send($tokens, $title, $body){
    $SERVER_API_KEY = 'AIzaSyBD3DbdjHnl9Hi1HpbING8FRmi7n3Bo7Yc';
    $data = [
        "to" => $tokens,
        "notification" => [
            "title" => $title,
            "body" => $body,
            "sound"=> "default" // required for sound on ios
        ],
    ];
    $dataString = json_encode($data);
    $headers = [
        'Authorization: key=' . $SERVER_API_KEY,
        'Content-Type: application/json',
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    $response = curl_exec($ch);
    //return $response;
    }

    public function notificationToUsers($users,$title,$body){
        foreach($users as $user){
            $this->notificationToUser($user->id, $title, $body);
        }
        return response()->json([
            'status'=>true,
            'message'=>'your notification sended'
        ]);
    }

    protected function notificationToUser($user_id,$title,$body){
        $tokens_devices = TokenDevice::where('user_id',$user_id)->get();
        foreach($tokens_devices as $token){
            $this->send($token, $title, $body);
        }
        return Notification::create([
            'user_id'=>$user_id,
            'title'=>$title,
            'body'=>$body
        ]);
    }

}
