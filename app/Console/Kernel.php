<?php

namespace App\Console;

use App\Model\MailJob\Condition;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\Inspire',
        'App\Console\Commands\SendReport',
        'App\Console\Commands\CloseWork',
        'App\Console\Commands\TicketFetch',
        'App\Console\Commands\GenCdnDailyReport',
        'App\Console\Commands\genAutoSSL',
        'App\Console\Commands\checkResourcesXns',
        'App\Console\Commands\checkPop',
        'App\Console\Commands\checkDNSSwitched',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (env('DB_INSTALL') == 1) {
            if ($this->getCurrentQueue() != 'sync') {
                $schedule->command('queue:listen '.$this->getCurrentQueue().' --sleep 60')->everyMinute();
            }
            $this->execute($schedule, 'fetching');
            $this->execute($schedule, 'notification');
            $this->execute($schedule, 'work');
            $this->execute($schedule, 'cdnDailyReport');
            $this->execute($schedule, 'genAutoSSL');
            $this->execute($schedule, 'checkResourcesXns');
            $this->execute($schedule, 'checkPop');
            $this->execute($schedule, 'checkDNSSwitched');
        }
    }

    public function execute($schedule, $task)
    {
        $condition = new Condition();
        $command = $condition->getConditionValue($task);
        switch ($task) {
            case 'fetching':
                $this->getCondition($schedule->command('ticket:fetch')->withoutOverlapping(), $command);
                break;
            case 'notification':
                $this->getCondition($schedule->command('report:send')->withoutOverlapping(), $command);
                break;
            case 'work':
                $this->getCondition($schedule->command('ticket:close')->withoutOverlapping(), $command);
                break;
            case 'cdnDailyReport':
                $this->getCondition($schedule->command('cdnreport:daily')->withoutOverlapping(), ['condition'=>'dailyAt', 'at'=>'1:00']);
                break;
            case 'genAutoSSL':
                $this->getCondition($schedule->command('cdnschedule:autossl')->withoutOverlapping(), ['condition'=>'everyMinute', 'at'=>'']);
                break;
            case 'checkResourcesXns':
                $this->getCondition($schedule->command('cdnschedule:checkxns')->withoutOverlapping(), ['condition'=>'everyFiveMinutes', 'at'=>'']);
                break;
            case 'checkPop':
                $this->getCondition($schedule->command('cdnschedule:checkpop')->withoutOverlapping(), ['condition'=>'everyMinute', 'at'=>'']);
                break;
            case 'checkDNSSwitched':
                $this->getCondition($schedule->command('cdnschedule:checkdnsswitched')->withoutOverlapping(), ['condition'=>'hourly', 'at'=>'']);
                break;
        }
    }

    public function getCondition($schedule, $command)
    {
        $condition = $command['condition'];
        $at = $command['at'];
        switch ($condition) {
            case 'everyMinute':
                $schedule->everyMinute();
                break;
            case 'everyFiveMinutes':
                $schedule->everyFiveMinutes();
                break;
            case 'everyTenMinutes':
                $schedule->everyTenMinutes();
                break;
            case 'everyThirtyMinutes':
                $schedule->everyThirtyMinutes();
                break;
            case 'hourly':
                $schedule->hourly();
                break;
            case 'daily':
                $schedule->daily();
                break;
            case 'dailyAt':
                $this->getConditionWithOption($schedule, $condition, $at);
                break;
            case 'weekly':
                $schedule->weekly();
                break;
            case 'monthly':
                $schedule->monthly();
                break;
            case 'yearly':
                $schedule->yearly();
                break;
        }
    }

    public function getConditionWithOption($schedule, $command, $at)
    {
        switch ($command) {
            case 'dailyAt':
                $schedule->dailyAt($at);
                break;
        }
    }

    public function getCurrentQueue()
    {
        $queue = 'database';
        $services = new \App\Model\MailJob\QueueService();
        $current = $services->where('status', 1)->first();
        if ($current) {
            $queue = $current->short_name;
        }

        return $queue;
    }
}
