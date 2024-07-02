<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DbBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

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
        //
        // Construct the command
        $command = "C:\\xampp\\mysql\\bin\\mysqldump -u root tms2 > backup".strtotime(now()).".sql";

        // Execute the command
        exec($command);
    }
}
