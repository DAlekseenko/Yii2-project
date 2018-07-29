#!/usr/bin/env php
<?php

$params = [];
foreach ($argv as $param) {
	$params = array_merge($params, preg_split('/\s+/', $param));
}

$argv = $_SERVER['argv'] = $params;
require(__DIR__ . '/../yii');