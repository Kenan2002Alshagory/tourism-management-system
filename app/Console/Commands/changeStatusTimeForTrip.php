<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Console\Command;

class changeStatusTimeForTrip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:change-status-time-for-trip';

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
        $trips = Trip::where('trip_status','!=','pending')->get();
        $time = Carbon::now()->format('Y-m-d');
        $timee = Carbon::now();
        foreach($trips as $trip){
            if($time < $trip->start_date){
                $trip->update([
                    'status_time'=>'before'
                ]);
            }
            if($timee > $trip->start_date && $timee < $trip->end_date){
                $start = Carbon::parse($trip->start_date);
                $duration = $timee->diffInDays($start);
                $trip->update([
                    'status_time'=>'Trip in day : '.($duration+1).' '
                ]);
            }
            if($time == $trip->start_date){
                $trip->update([
                    'status_time'=>'Trip in first day'
                ]);
            }
            if($time == $trip->end_date){
                $trip->update([
                    'status_time'=>'Trip in last day'
                ]);
            }
            if($time > $trip->end_date){
                $trip->update([
                    'status_time'=>'Ending'
                ]);
            }
        }
    }
}
