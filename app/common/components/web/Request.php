<?php
/**
 * User: buchatskiy
 * Date: 25.05.2017
 * Time: 16:14
 */

namespace common\components\web;


/**
 * Class Request
 * @package common\components\web
 */
class Request extends \yii\web\Request
{
    /**
     * @var bool
     */
    private $firstRun = true;

    /**
     * Заголовок который шлёт load-balancer
     */
    const HTTP_X_IMMO_REQUEST = 'HTTP_X_IMMO_REQUEST';

    /**
     * @return null|string
     */
    public function getHostInfo()
    {
        if($this->firstRun) {
            $this->firstRun = false;
            $secure = $this->getIsSecureConnection();
            $http = $secure ? 'https' : 'http';
            if (isset($_SERVER[self::HTTP_X_IMMO_REQUEST])) {
                parent::setHostInfo($http . '://' . parse_url($_SERVER[self::HTTP_X_IMMO_REQUEST], PHP_URL_HOST));
            } elseif (isset($_SERVER['HTTP_HOST'])) {
                parent::setHostInfo($http . '://' . $_SERVER['HTTP_HOST']);
            } elseif (isset($_SERVER['SERVER_NAME'])) {
                $hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $secure ? $this->getSecurePort() : $this->getPort();
                if (($port !== 80 && !$secure) || ($port !== 443 && $secure)) {
                    $hostInfo .= ':' . $port;
                }
                parent::setHostInfo($hostInfo);
            }
        }

        return parent::getHostInfo();
    }
}