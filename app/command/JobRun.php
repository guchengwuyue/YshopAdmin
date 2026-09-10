<?php
declare(strict_types=1);

namespace app\command;

use app\service\JobService;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * php think job:run
 * Schedule via crontab / Windows Task Scheduler every minute.
 */
class JobRun extends Command
{
    protected function configure()
    {
        $this->setName('job:run')
            ->setDescription('Run due sys_job tasks (Quartz cron, whitelist invoke)');
    }

    protected function execute(Input $input, Output $output)
    {
        $service = new JobService();
        $count = $service->runDue();
        $output->writeln(sprintf('[%s] ran %d job(s)', date('Y-m-d H:i:s'), $count));
        return 0;
    }
}
