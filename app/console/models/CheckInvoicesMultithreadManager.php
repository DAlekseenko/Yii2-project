<?php

namespace console\models;

use PbrLibBelCommon\Multiprocessing\JobsManager;
use PbrLibBelCommon\Multiprocessing\Job;

class CheckInvoicesMultithreadManager
{
	protected $smsHandler;

	protected $jobsManager;

	public function __construct(CheckInvoicesSmsQueue $smsHandler, JobsManager $jobsManager)
	{
		$this->smsHandler = $smsHandler;
		$this->jobsManager = $jobsManager;
	}

	/**
	 * @param \Traversable|array|InvoicesUpdateState[] $states
	 * @param int $perCluster
	 * @return array
	 */
	public function createUpdateMap($states, $perCluster = 100)
	{
		// Список для существующих привязок
		$existInvoicesStateIds = [[]];
		$clusterIndexForExist = 0;

		// Список для новых привязок
		$newInvoicesStateIds = [[]];
		$clusterIndexForNew = 0;

		foreach ($states as $state) {
			if (isset($state->invoice_id)) {
				if (count($existInvoicesStateIds[$clusterIndexForExist]) == $perCluster) {
					$existInvoicesStateIds[++$clusterIndexForExist] = [];
				}
				$existInvoicesStateIds[$clusterIndexForExist][] = $state->id;
				continue;
			}
			if (count($newInvoicesStateIds[$clusterIndexForNew]) == $perCluster) {
				$newInvoicesStateIds[++$clusterIndexForNew] = [];
			}
			$newInvoicesStateIds[$clusterIndexForNew][] = $state->id;
		}
		return [$existInvoicesStateIds, $newInvoicesStateIds];
	}

	/**
	 * @param  array $map
	 * @return \PbrLibBelCommon\Multiprocessing\Job[]
	 */
	public function processMap($map)
	{
		/** @var Job[] $jobs */
		$jobs = [];
		foreach ($map as $key => $cluster) {
			if (empty($cluster)) {
				continue;
			}
			$command = 'php ' .  ROOT_DIR . 'cron/yii-cron-gw.php check-invoices ' . implode('_', $cluster);
			$jobs[$key] = new Job($command);
			if ($this->jobsManager->runCommand($jobs[$key])) {
				\yii::info("Run check invoice subprocess; command: [$command]; pid {$jobs[$key]->pid()}");
			} else {
				\yii::warning("Unsuccessful run check invoice subprocess; command: $command");
			}
		}
		while ($this->jobsManager->getRunningCount()) {
			sleep(5);
		}
		return $jobs;
	}

	/**
	 * @param array|\PbrLibBelCommon\Multiprocessing\Job[] $processResult
	 */
	public function appendSmsData(array $processResult)
	{
		foreach ($processResult as $job) {
			$jobOutput = $job->getPipe();
			$result = IOMultithreadFormatter::readProcessOutput($jobOutput);
			foreach ($result as $item) {
				@list($userId, $uuid) = $item;
				$this->smsHandler->add($userId, $uuid);
			}
		}
	}
}
