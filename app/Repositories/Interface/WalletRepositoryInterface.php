<?php

namespace App\Repositories\interface;

interface WalletRepositoryInterface{
    public function charging($request);
    public function accept($idOrder);
    public function reject($idOrder);
    public function ViewChargingOrders();
}
