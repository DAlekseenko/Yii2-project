<?php

namespace console\controllers;

class JobsReaderSeqController extends JobsReaderController
{
	public function getQueue()
	{
		return QUEUE_SEQUENTIAL_JOBS;
	}

	public function getPattern()
	{
		return '%s';
	}

	public function afterProcessTask()
	{
		sleep(1);
	}
}
