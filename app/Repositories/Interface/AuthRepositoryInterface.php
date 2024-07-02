<?php

namespace App\Repositories\Interface;

interface AuthRepositoryInterface {
    public function register($request);
    public function login($request);
    public function logout($request);
    public function forgetPassword($request);
    public function sendCode($request);
    public function resetPassword($request);
    public function updateInfo($request);
    public function userInfo();
    public function allUser();
    public function deleteUser($user_id);
}
