<?php

namespace App\Repositories;

use App\Mail\forgetPassMail;
use App\Mail\forgetPasswordMail;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\TokenDevice;
use App\Models\Trip;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Interface\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthRepository implements AuthRepositoryInterface{

    public function register($request){
        $data = $request->all();
        $user = User::create([
            'username'=>$data['username'],
            'email'=>$data['email'],
            'password'=>Hash::make($data['password']),
            'birth_date'=>$data['birth_date'],
            'role'=>'user',
            ]);
        $this->newWallet($data['wallet_password'],$user->id);
        array_key_exists('token_device', $data) ? $this->newTokenDevice($data['token_device'],$user->id) : " " ;
        $token = $user->createToken('APPLICATION')->accessToken;
        return response()->json([
            'status'=>true,
            'token'=>$token,
            'user'=>$user
        ]);
    }

    protected function newWallet($wallet_password,$user_id){
        return Wallet::create([
            'user_id'=>$user_id,
            'wallet_password'=>$wallet_password,
            'balance'=>0
        ]);
    }

    protected function newTokenDevice($token,$user_id){
        return TokenDevice::create([
            'user_id'=>$user_id,
            'token'=>$token
        ]);
    }

    public function login($request){
        $data = $request->all();
        if(Auth::attempt(['email'=>$data['email'],'password'=>$data['password']])){
            $user= Auth::user();
            $token =  $user->createToken('APPLICATION')->accessToken;
            array_key_exists('token_device', $data) ? $this->newTokenDevice($data['token_device'],$user->id) : " " ;
            return response()->json([
                'status'=>true,
                'token'=>$token,
                'user'=>$user,
                'role'=>$user->role,
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'your email or password rong'
        ]);
    }

    public function forgetPassword($request){
        $data = $request->all();
        $user = User::where('email',$data['email'])->first();
        if(!$user){
            return response()->json([
                'message'=>'Email Invalid'
            ]);
        }
        $token = rand(10000,99999);
        $user->update([
            'reset_password_code'=>$token
        ]);
        //Mail::to($data['email'])->send(new forgetPassMail($token));
        return response()->json([
            'message'=>'we sent code to your email'
        ]);
    }

    public function sendCode($request){
        $data = $request->all();
        $user = User::where('email',$data['email'])->where('reset_password_code',$data['code'])->first();
        if(!$user){
            return response()->json([
                'message'=>'Code Invalid'
            ]);
        }else{
            return response()->json([
                'message'=>'your code is true'
            ]);
        }
    }

    public function resetPassword($request){
        $data = $request->all();
        $user = User::where('email',$data['email'])->update([
            'password'=>Hash::make($data['password']),
            'reset_password_code'=>null
        ]);
        return response()->json([
            'messsage'=>'your password updated'
        ]);
    }

    public function logout($request){
        $data = $request->all();
        $user = Auth::user();
        $user->token()->revoke();
        array_key_exists('token_device', $data) ? $this->deleteTokenDevice($data['token_device']) : " " ;
        return response()->json([
            'status'=>true,
            'message'=>'logout sucsses'
        ]);
    }

    protected function deleteTokenDevice($token_device){
        return TokenDevice::where('token',$token_device)->delete();
    }

    public function updateInfo($request)
    {
        $user = $request->user();
        $data = $request->all();

        if(!Hash::check($data['password_sure'],$user->password)){
            return response()->json([
                'message'=>'your Password wrong'
            ]);
        }

        if (isset($data['username']) && $data['username'] !== $user->username) {
            $user->update(['username' => $data['username']]);
        }


        if (isset($data['email']) && $data['email'] !== $user->email) {
            $user->update(['email' => $data['email']]);
        }


        if (isset($data['birth_date']) && $data['birth_date'] !== $user->birth_date) {
            $user->update(['birth_date' => $data['birth_date']]);
        }


        if (isset($data['password']) && !(Hash::check($data['password'],$user->password))) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        if (isset($data['wallet_password'])) {
            Wallet::where('user_id',$user->id)->update([
                'wallet_password' => $data['wallet_password']
            ]);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('images', ['disk' =>'my_files']);
            $user->update([
                'photo_path' => $path
            ]);
        }

        return response()->json([
            'message'=>'your info updated'
        ]);
    }

    public function userInfo(){
        return User::with('wallet')->where('id',Auth::user()->id)->get();
    }

    public function allUser(){
        return User::with('wallet')->where('role','user')->get();
    }

    public function deleteUser($user_id){
        $trip = Trip::where('user_id',$user_id)->first();
        $reservation = Reservation::where('user_id',$user_id)->first();
        $review = Review::where('user_id',$user_id)->first();
        if($trip || $reservation || $review){
            return response()->json([
                'message'=>'you cant delete this user'
            ]);
        }
        User::where('id',$user_id)->delete();
        return response()->json([
            'message'=>'users delete'
        ]);
    }

}
