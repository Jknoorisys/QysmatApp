<?php

namespace App\Console\Commands;

use Carbon\Carbon;
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
    protected $description = 'Delete Swiped Up profiles after 1 Week';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $one_week_ago = now()->subWeek(); 
        $one_day_ago = now()->subHour(); 
        DB::table('swiped_up_users')->where('created_at', '<', $one_day_ago)->delete();
        $this->info('Successfully deleted swiped-up profiles.');

        $one_hour_ago = now()->subHour(); 
        DB::table('instant_match_requests')->where('request_type', '=', 'hold')->where('created_at', '<', $one_hour_ago)->update(['request_type' => 'pending', 'created_at' => Carbon::now()]);
        $this->info('Successfully updated swiped-up Instant Requests.');
    }
}
