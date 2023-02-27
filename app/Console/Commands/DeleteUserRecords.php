<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteUserRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Swiped Up profiles after 1 hour';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $one_hour_ago = now()->subHour(); 
        DB::table('swiped_up_users')->where('created_at', '<', $one_hour_ago)->delete();
        $this->info('Successfully deleted swiped-up profiles.');
    }
}
