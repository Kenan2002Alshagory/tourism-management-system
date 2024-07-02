<?php

namespace App\Providers;

use App\Repositories\AuthRepository;
use App\Repositories\FavoriteRepository;
use App\Repositories\Interface\AuthRepositoryInterface;
use App\Repositories\interface\FavoriteRepositoryInterface;
use App\Repositories\Interface\NotificationRepositoryInterface;
use App\Repositories\interface\PlaceRepositoryInterface;
use App\Repositories\interface\ReservationRepositoryInterface;
use App\Repositories\interface\TripRepositoryInterface;
use App\Repositories\interface\WalletRepositoryInterface;
use App\Repositories\NotificationRepository;
use App\Repositories\PlaceRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\TripRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\ServiceProvider;

class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(AuthRepositoryInterface::class,AuthRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class,NotificationRepository::class);
        $this->app->bind(PlaceRepositoryInterface::class,PlaceRepository::class);
        $this->app->bind(TripRepositoryInterface::class,TripRepository::class);
        $this->app->bind(FavoriteRepositoryInterface::class,FavoriteRepository::class);
        $this->app->bind(WalletRepositoryInterface::class,WalletRepository::class);
        $this->app->bind(ReservationRepositoryInterface::class,ReservationRepository::class);


    }
}
