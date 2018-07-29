<?php
namespace api\controllers;

use yii;
use yii\helpers\ArrayHelper as Arr;
use eripDialog\EdApplication;
use eripDialog\EdHelper as H;
use eripDialog\EdLogger;

class MonitorController extends AbstractController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'ips'   => ALLOWED_INTERNAL_IPS,
                        'allow' => true,
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
    }

    /**
     * Test 'start' ERIP API method
     *
     * There are 2 ways to request. The first is the GET parameter 'services' with
     * service codes separated by non-word-non-digit characters
     * /api/monitor/start?services=10000324281,10000156241
     * In this case every service request expects 'success'=1 in API response.
     * The request below is the same as the above one
     * /api/monitor/start?services[10000324281]=success:1&services[10000156241]=success:1
     * BUT there is a way to define expected terms of response values. The expected terms
     * could be defined as multilevel depth path eg data.some.var:somevalue
     * Use 'throw' GET parameter to control output: 1 (TRUE - default value) generates
     * error 500 on test failure but 0 generates 200 OK response with failure details.
     * Use 'notify' GET parameter to provide e-mail addresses for notifications in case
     * of failure
     *
     * @return string
     * The string is JSON in case of test failed returns 'result' element with failure
     * details. Success generates "result":{"value":"successfully"} response
     *
     * @throws \yii\web\HttpException
     * @throws \Exception
     */
    public function actionEripDialogStart()
    {
        $default = 'success:1';

        $list = \Yii::$app->request->get('services', '');
        $throw = (bool) \Yii::$app->request->get('throw', true);
        $notify = (string) \Yii::$app->request->get('notify', '');

        if (!is_array($list)) {
            $a = [];
            $list = preg_split('/[^\w\d]+/', $list);
            foreach ($list as $i) {
                if (empty($i)) {
                    continue;
                }
                $a[$i] = $default;
            }
            $list = $a;
        }
        if (empty($list)) {
            $total = 5;
            // load $total most usefull services in different categories to test em
            $list = [];
            $services = \common\models\Services::find()
                ->select([
                        'services.id',
                        'root_id' => "REGEXP_REPLACE(CAST(\"path\" AS TEXT), '^{([0-9]+)[^0-9].*$', '\\1')",
                    ])
                ->joinWith('servicesInfo', 'INNER')
                ->where(['AND',
                        'services.date_removed IS NULL',
                        'services_info.forbidden_for_guest IS NOT TRUE',
                    ])
                ->orderBy('services_info.success_counter')
                ->limit($total * 10)
                ->asArray()
                ->all();

            $failed = [];
            foreach ($services as $a) {
                if (empty($failed[$a['root_id']])) {
                    $list[$a['id']] = $default;
                    $failed[$a['root_id']] = 1;
                    if ($total-- <= 1) {
                        break;
                    }
                }
            }
        }

        $failed = [];
        foreach ($list as $code => $expected) {
            try {
                $app = new EdApplication();
                $logger = new EdLogger('monitoringRequest', 'service');
                $app->setLogger($logger);
                $request = [
                    H::F_MODE         => H::MODE_START,
                    H::F_SERVICE_CODE => $code,
                ];
                $app->run($request);
                $a = explode(':', $expected, 2);
                $result = $app->getStepHandler()->response->get();

                if ((string)Arr::getValue($result, $a[0]) != (string)Arr::getValue($a, 1)) {
                    $failed[] = [
                        'code'     => $code,
                        'actual'   => (string) Arr::getValue($result, $a[0]),
                        'expected' => (string) Arr::getValue($a, 1),
                    ];
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'code'      => $code,
                    'exception' => $e->getMessage(),
                ];
            }
        }

        if ( ! empty($notify) AND count($failed)) {
            $a = [];
            $a[] = sprintf("%13s | %20s | %s", "Code", "Expected", "Actual");
            foreach ($failed as $f) {
                $a[] = sprintf("%13s | %20s | %s", $f['code'], $f['expected'], $f['actual']);
            }
            @mail($notify, 'Alert pbr-wifc-mts-money: ERIP API failed', implode("\n", $a));
        }
        if ($throw AND count($failed) == count($list)) {
            $a = [];
            foreach ($failed as $f) {
                $a[] = $f['code'];
            }
            throw new \yii\web\HttpException(500, 'Failed: ' . implode(', ', $a));
        }

        return count($failed)
            ? ['failed' => $failed]
            : 'successfully';
    }
}
