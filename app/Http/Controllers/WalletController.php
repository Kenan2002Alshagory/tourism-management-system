<?php

namespace App\Http\Controllers;

use App\Http\Requests\chargingRequest;
use App\Repositories\interface\WalletRepositoryInterface;
use Illuminate\Http\Request;

class WalletController extends Controller
{

    private $walletRepository;
    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function charging(chargingRequest $request){
        return $this->walletRepository->charging($request);
    }

    public function accept($idOrder){
        return $this->walletRepository->accept($idOrder);
    }

    public function reject($idOrder){
        return $this->walletRepository->reject($idOrder);
    }

    public function ViewChargingOrders(){
        return $this->walletRepository->ViewChargingOrders();
    }

}
