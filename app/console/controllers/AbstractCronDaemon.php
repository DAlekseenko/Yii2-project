<?php
/**
 * User: buchatskiy
 * Date: 18.07.2017
 * Time: 10:49
 */

namespace console\controllers;


use PbrLibBelCommon\CommandLine\DaemonLockManager;
use PbrLibBelCommon\Environment\ReleaseDetector;
use Yii;

/** @noinspection LongInheritanceChainInspection
 * Class AbstractCronDaemon
 * @package console\controllers
 */
abstract class AbstractCronDaemon extends AbstractTask
{
    const PROJECT_NAME = 'pbr-wifc-mts-money';

    /**
     * Пауза между циклами демона, в миллисекундах
     */
    const HEARTBEAT = 10000; // миллисекунд

    /**
     * @var DaemonLockManager
     */
    protected $lock;

    /** @noinspection PhpMissingParentCallCommonInspection
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        try {
            $rd = new ReleaseDetector(self::PROJECT_NAME);
            $this->lock = new DaemonLockManager($this->getClassName($action), self::HEARTBEAT, $this->getLockDir(), $rd);
            return true;
        } catch (\Exception $exception) {
            $this->echoException($exception);
            Yii::error($exception->getMessage());
            return false;
        }
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        unset($this->lock);
    }

    /**
     * Запускает метод handler. Если крон создан как демон, то handler запускается циклически с паузой.
     */
    public function actionIndex()
    {
        try {
            while($this->lock->heartbeat()) {
                $this->handler(func_get_args());
                usleep($this->lock->getWaitTime());
            }
        } catch (\Exception $exception) {
            $this->echoException($exception);
            Yii::error($exception->getMessage());
        }
    }
}