<?php

namespace App\Http\Controllers;

use App\Http\Requests\forgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\resetPasswordRequest;
use App\Http\Requests\sendCodeRequest;
use App\Http\Requests\updateInfoRequest;
use App\Models\User;
use App\Repositories\Interface\AuthRepositoryInterface;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    private $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(RegisterRequest $request){
        return $this->authRepository->register($request);
    }

    public function login(LoginRequest $request){
        return $this->authRepository->login($request);
    }

    public function forgetPassword(forgetPasswordRequest $request){
        return $this->authRepository->forgetPassword($request);
    }

    public function sendCode(sendCodeRequest $request){
        return $this->authRepository->sendCode($request);
    }

    public function resetPassword(resetPasswordRequest $request){
        return $this->authRepository->resetPassword($request);
    }

    public function logout(Request $request){
        return $this->authRepository->logout($request);
    }

    public function updateInfo(updateInfoRequest $request){
        return $this->authRepository->updateInfo($request);
    }

    public function userInfo(){
        return $this->authRepository->userInfo();
    }

    public function allUser(){
        return $this->authRepository->allUser();
    }

    public function deleteUser($user_id){
        return $this->authRepository->deleteUser($user_id);
    }
}
