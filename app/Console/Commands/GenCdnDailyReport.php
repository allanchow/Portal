<?php

namespace App\Console\Commands;

use App\Http\Controllers\Cdn\CdnReportController;
use Illuminate\Console\Command;

class GenCdnDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdnreport:daily {day?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CDN Daily Report';

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
                $day = $this->argument('day');
                $cdn = new CdnReportController();
                $cdn->genDailyByteSentReport($day);
            }
        } catch (Exception $ex) {
            //dd($ex);
            $this->error($ex->getMessage());
        }
    }
}
