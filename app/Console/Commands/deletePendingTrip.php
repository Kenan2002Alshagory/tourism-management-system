<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Console\Command;

class deletePendingTrip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-pending-trip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $trips = Trip::where('trip_status','pending')->get();
        $time = Carbon::now();

        foreach($trips as $trip){
            if($time >= $trip->start_date){
                $trip->delete();
            }
        }
    }
}
