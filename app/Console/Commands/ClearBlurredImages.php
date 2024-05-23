<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearBlurredImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Blurred Images after 1 Week';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $one_hour_ago = now()->subHour();
        $seven_days_ago = now()->subDays(7); 
        DB::table('matches')->where('matched_at', '<', $seven_days_ago)->update(['blur_image' => 'no']);
        $this->info('Successfully cleared blurred images.');
    }
}
