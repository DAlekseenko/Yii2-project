<?php

namespace console\controllers;


/**
 * Class FooCronController
 * @package console\controllers
 */
class FooCronController extends AbstractCronDaemon
{
	const HEARTBEAT = 3000; // 3 секунды между циклами

    /**
     * @param string[] $params
     * @return void
     */
    public function handler(array $params)
	{
		echo "demon running\n";
	}
}