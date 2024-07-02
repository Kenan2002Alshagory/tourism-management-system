<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::create([
            'username' => "Travalio",
            'email' =>   'travalio@email.com',
            'password' => bcrypt("12345678"),
            'birth_date'=>'2000-10-10',
            'role'=>'admin',
        ]);
        Wallet::create([
            'user_id'=>$user->id,
            'wallet_password'=>'12345678',
            'balance'=>0
        ]);
        
    }
}
