<?php

namespace console\controllers;


use common\components\services\MqRequestMessage;
use common\components\services\MqAnswerMessage;
use Yii;

/**
 * Class MobileEventsHandlerController
 * @package console\controllers
 */
class MobileEventsHandlerController extends AbstractCronDaemon
{
    const HEARTBEAT = 100;

    /**
     * @return mixed
     */
    private function getRequestQueue()
    {
        return Yii::$app->params['requestQueue'];
    }

    /**
     * @param string[] $params
     * @return void
     */
    public function handler(array $params)
    {
        /** @var \common\components\services\MqConnector $connector*/
        $connector = Yii::$app->amqp;
        $queueManager = $connector->getConnectionManager();

        if ($queueManager->countChanelMessages($this->getRequestQueue()) > 0) {
            while ($request = $queueManager->getSimple($this->getRequestQueue(), MqRequestMessage::class)) {
				Yii::info($request->getMsgBody(), LOG_CATEGORY_MOBILE_MQ_IO);
            	if ($request->isValid()) {
                    Yii::info('Handle message with requestId: '.$request->getRequestId(), 'rest');
                    $args = $request->getArgs();
                    $args = array_merge($args, ['REMOTE_ADDR' => $request->getUserIp()]);
                    $command = sprintf('php %scron/yii-cron-gw.php handlers/sub-process %s %s %s %s %s > /dev/null 2>&1 &',
                        ROOT_DIR,
                        $request->getMethod(),
                        $request->getAnswerTo(),
                        $request->getConnectionId(),
                        $request->getRequestId(),
                        escapeshellarg(http_build_query($args))
                    );
                    Yii::info('Command to exec: '.$command, 'rest');
                    shell_exec($command);
                    $queueManager->ackSimple($request);
                    continue;
                }
                $queueManager->nackSimple($request);
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param $method
     * @param $answerTo
     * @param $connectionId
     * @param $requestId
     * @param $params
     */
    public function actionSubProcess($method, $answerTo, $connectionId, $requestId, $params)
    {
        $apiAnswer = $this->runApiApp($method, $params);
        Yii::info('Answer for requestId '.$requestId.':', 'rest');
        Yii::info($apiAnswer, 'rest');

        /** @var \common\components\services\MqConnector $connector*/
        $connector = Yii::$app->amqp;
        $queueManager = $connector->getConnectionManager();
        $answerMessage = new MqAnswerMessage($connectionId, $answerTo, $requestId);
        $answerMessage->setContent($apiAnswer);
        $queueManager->sendSimple($answerMessage);
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    private function runApiApp($method, $params)
    {
        Yii::info('Running API app for method '.$method.' with params '.$params, 'rest');
        $command = 'php ' . ROOT_DIR . 'run-web-api.php ' . $method . ' ' . escapeshellarg($params);
        try {
            return json_decode(`$command`, 1);    
        }
        catch (\Exception $e) {
            Yii::error($e, 'rest');
        }
    }
}
