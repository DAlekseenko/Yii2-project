<?php
define('ROOT_DIR', __DIR__ . '/');
require(ROOT_DIR . 'config/current/ENV.php');

require(ROOT_DIR . 'bootstrap.php');

require(ROOT_DIR . 'vendor/autoload.php');
require(ROOT_DIR . 'vendor/yiisoft/yii2/Yii.php');

@include(ROOT_DIR . 'config/bootstrap.php');
@include(ROOT_DIR . 'config/current/commonBootstrap.php');
@include(ROOT_DIR . 'config/current/apiBootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
	require(ROOT_DIR . 'config/config.php'),
	(@include ROOT_DIR . 'config/apiConfig.php') ?: [],
	(@include ROOT_DIR . 'config/current/commonConfig.php') ?: [],
	(@include ROOT_DIR . 'config/current/apiConfig.php') ?: []
);
$config['params'] = array_merge(
	require ROOT_DIR . 'config/params.php',
	(@include ROOT_DIR . 'config/current/commonParams.php') ?: [],
	(@include ROOT_DIR . 'config/current/apiParams.php') ?: []
);

$_SERVER['REMOTE_ADDR'] = '127.0.0.1'; // чтобы обойти фильтр по IP

$application = new yii\web\Application($config);

$application->request->setUrl('/api/' . $argv[1]);
if (isset($argv[2])) {
	$params = [];
	parse_str($argv[2], $params);
	$application->request->setQueryParams($params);
}

$application->run();
