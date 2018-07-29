<?php

namespace console\controllers;

use Yii;
use Yii\base\InlineAction;
use yii\console\Controller;
use yii\console\Exception;

/**
 * Class AbstractTask
 * @package console\controllers
 */
abstract class AbstractTask extends Controller
{
    const YII_ACTION_PREFIX = 'action';

    /**
     * @param string[] $params
     * @return mixed|void
     */
	abstract public function handler(array $params);

    /**
     * @param InlineAction|null $action
     * @return string
     */
    protected function getClassName(InlineAction $action = null)
    {
        $actionName = '';
        $fullyQualifiedName = explode('\\', static::class);
        if(null !== $action && is_string($action->actionMethod)) {
            $actionName = substr($action->actionMethod, strlen(self::YII_ACTION_PREFIX));
        }
        return array_pop($fullyQualifiedName) . $actionName;
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getLockDir()
	{
		$lockDir = Yii::$app->runtimePath . DIRECTORY_SEPARATOR . 'locks' . DIRECTORY_SEPARATOR;
        /** @noinspection MkdirRaceConditionInspection */
        if (is_dir($lockDir) || mkdir($lockDir, 0775)) {
			return $lockDir;
		}
		throw new Exception('Cant access to ' . $lockDir);
	}

    /**
     * @param \Exception $exception
     */
    protected function echoException(\Exception $exception)
    {
        echo $exception->getCode() . ': ' . $exception->getMessage() . "\n";
        echo $exception->getTraceAsString() . "\n";
    }
}