<?php

namespace App\Console\Commands;

use App\Http\Controllers\Cdn\CdnScheduleController;
use Illuminate\Console\Command;

class checkDNSSwitched extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdnschedule:checkdnsswitched';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check DNS Switched';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            if (env('DB_INSTALL') == 1) {
                $cdn = new CdnScheduleController();
                $cdn->checkDNSSwitched();
            }
        } catch (Exception $ex) {
            //dd($ex);
            $this->error($ex->getMessage());
        }
    }
}
