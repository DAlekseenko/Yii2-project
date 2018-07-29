<?php

namespace common\components\services\builders;

use PbrLibBelCommon\Multiprocessing\Job;


class MoneyStatBuilder extends AbstractReportBuilder
{
    public $dateTo;

    public $dateFrom;

    private $name = 'Статистика прибыли ';

    public function __construct($data)
    {
        if (isset($data['date_to'])) {
            $this->dateTo = $data['date_to'];
        }
        if (isset($data['date_from'])) {
            $this->dateFrom = $data['date_from'];
        }
        $this->fileName = 'money_stat_' . rand(1000, 9999) . '_' . date('Y-m-d_H-i-s');
        $this->extension = '.xlsx';
    }

    public function getName()
    {
        return $this->name . $this->dateFrom . ' ' . $this->dateTo;
    }

    public function getParams()
    {
        return [
            '-p ' . $this->getPath(),
            '-s ' . SUBSCRIPTION_SERVICE_NAME,
            '-f ' . $this->dateFrom,
            '-t ' . $this->dateTo

        ];
    }

    public function getFile()
    {
        $name = '';
        $command = SUBSCRIPTION_PATH_TO_REPORT . implode(' ', $this->getParams());
        $jobs = new Job($command);

        if ($jobs->execute() && $this->sleepProcess($jobs)) {
            $name = $jobs->pipeline(true);
        }

        return $this->renameFile($name);

    }

    public function sleepProcess(Job $process)
    {
        while ($process->isRunning()) {
            usleep(100000);
        }
        return true;
    }

    public function renameFile($name)
    {
        $name = substr($name, 0, -1);
        if(!file_exists($name)){
            throw new \Exception('file not found');
        }
        return rename($name, $this->getFilePath());
    }

    public function getData()
    {
    }

    public function prepareHeader()
    {
    }

    public function prepareRow($tr)
    {
    }


}