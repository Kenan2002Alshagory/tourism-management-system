<?php

namespace App\Repositories;

use App\Models\pushWallet;
use App\Models\Wallet;
use App\Repositories\interface\WalletRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class WalletRepository implements WalletRepositoryInterface{

    public function charging($request){
        $data = $request->all();
        $user_id = Auth::user()->id;
        $wallet = Wallet::where('user_id',$user_id)->where('wallet_password',$data['wallet_password'])->first();
        if(!$wallet){
            return response()->json([
                'message'=>'the wllet password uncorrect'
            ]);
        }
        pushWallet::create([
            'user_id'=>$user_id,
            'amount'=>$data['amount']
        ]);
        return response()->json([
            'message'=>'the order sended'
        ]);
    }

    public function accept($idOrder){
        $order = pushWallet::where('id',$idOrder)->first();
        $wallet = Wallet::where('user_id',$order->user_id)->first();
        $wallet_balance = $wallet->balance;
        $wallet->update([
            'balance' =>$wallet_balance + $order['amount']
        ]);
        $order->delete();
        return response()->json([
            'message'=>'the order is accept'
        ]);
    }

    public function reject($idOrder){
        $order = pushWallet::where('id',$idOrder)->delete();
        return response()->json([
            'message'=>'the order is reject'
        ]);
    }

    public function ViewChargingOrders(){
        return response()->json([
            'orders'=>pushWallet::with('user')->get()
        ]);
    }

}
