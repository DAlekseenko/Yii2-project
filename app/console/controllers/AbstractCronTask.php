<?php
/**
 * User: buchatskiy
 * Date: 18.07.2017
 * Time: 10:48
 */

namespace console\controllers;


use PbrLibBelCommon\CommandLine\TaskLockManager;
use Yii;

/** @noinspection LongInheritanceChainInspection
 * Class AbstractCronTask
 * @package console\controllers
 */
abstract class AbstractCronTask extends AbstractTask
{
    /**
     * Максимальное время работы задачи, в секундах.
     */
    const RUN_TIME = 1800; // секунд

    /**
     * @var TaskLockManager
     */
    protected $lock;

    /** @noinspection PhpMissingParentCallCommonInspection
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        try {
            $this->lock = new TaskLockManager($this->getClassName($action), $this->getLockDir(), static::RUN_TIME);
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
            $this->handler(func_get_args());
        } catch (\Exception $exception) {
            $this->echoException($exception);
            Yii::error($exception->getMessage());
        }
    }
}