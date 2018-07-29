<?php
namespace frontend\controllersAbstract;

use yii;

class AbstractController extends yii\web\Controller
{
    public $isMobile = false;

    public function beforeAction($action)
    {
        $this->setEnvironment();
        $this->isMobile = isset(\yii::$app->clientDevice) && \yii::$app->clientDevice->mobileDetect->isMobile();
        return parent::beforeAction($action);
    }

    protected function setEnvironment()
    {
        /** @var \common\components\services\Environment $env */
        $env = Yii::$app->environment;

        $user = yii::$app->user->isGuest ? null : yii::$app->user->identity;
        $prop = ['mode' => isset($user) ? 'user' : 'guest'];
        if (isset($_SERVER)) {
            $prop['ip'] = trim(@$_SERVER['HTTP_X_REAL_IP'] . " " . @$_SERVER['REMOTE_ADDR']);
        }
        $env->setName($env::MODULE_WEB)->setProp($prop);
    }


/**
 * @todo код ниже неправильный и никогда не работал. Он должен был блочить пользователя по IP...
 */
//    public function beforeAction($action)
//    {
//        $actionName = self::getActionName($action);
//
//        $ip = $_SERVER['REMOTE_ADDR'];
//        $accessDeny = \common\models\AccessDeny::findOne(['action' => $actionName, 'ip' => $ip]);
//        if ($accessDeny) {
//            Yii::info("IP $ip was reverted to action $actionName because of he was added to access deny rules(".($accessDeny->is_temporary?'temporary':'constantly').")");
//            $this->redirect('site/error');
//        } elseif (in_array($actionName, Yii::$app->params['protectedActions'])) {
//            $accessDeny = new \common\models\AccessDeny();
//            $accessDeny->action = $actionName;
//            $accessDeny->ip = $ip;
//            $accessDeny->is_temporary = true;
//            $accessDeny->save();
//        }
//        return parent::beforeAction($action);
//    }
//    private static function getActionName($action)
//    {
//        return $action->controller->id . '_' . $action->id;
//    }
}
