<?php

namespace App\Repositories\interface;

interface NotificationRepositoryInterface{
    public function notificationToUsers($users,$title,$body);
}
