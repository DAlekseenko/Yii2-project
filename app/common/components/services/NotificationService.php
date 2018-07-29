<?php

namespace common\components\services;

use yii;
use common\components\services\senders\messageFormatter\NewInvoiceMessage;
use common\models\Users;
use common\components\services\senders\SenderInterface;
use common\components\services\senders\SenderGroupGate;

class NotificationService
{
    const TYPE_NEW_INVOICE = 'newInvoice';
    const TYPE_ANDROID_PUSH = 'androidPush';
    const TYPE_SIMPLE_PUSH = "simple";

    private $type;

    /** @var  array  Список пользователей с набором данных, согласно которым будут формироваться сообщения */
    protected $data = [];

    protected $messages;

    /**    @var SenderInterface[] */
    protected $senders = [];

    /**    @var array */
    protected $chain = [];

    protected $stopOnAccept = true;

    protected $formatter;

    /**@var  array Устройства которым отправлять сообщения */
    protected $device = [];

    public function __construct($type, $devices = [])
    {
        $this->type = $type;
        $this->devices = $devices;
        $config = yii::$app->params['notifications'][$type];
        $this->chain = $config['senders'];
        if (!empty($devices)) {
            $this->setSendersByDevices($devices);
        }
        $this->stopOnAccept = isset($config['stopOnAccept']) && $config['stopOnAccept'] == true;
        /** @todo  вынести форматтер в настройки! */
        $formatter = '\common\components\services\senders\messageFormatter\\' . ucfirst($type) . 'Message';
        $this->formatter = new $formatter($type, isset($config['canBeMultiple']) && $config['canBeMultiple'] == true);
    }

    public static function newInvoicesNotification()
    {
        return new self(self::TYPE_NEW_INVOICE);
    }

    public static function androidPushNotification()
    {
        return new self(self::TYPE_ANDROID_PUSH);
    }

    public static function pushMessages(array $devices)
    {
        return new self(self::TYPE_SIMPLE_PUSH, $devices);
    }

    /**
     * С каждым пользователем ассоциирован некий набор данных, согласно которым будут формироваться сообщение.
     * За формирование сообщения для конкретного сендера отвечает некий форматер (@see MessageFormatterInterface).
     *
     * @param int $userId - пользователь, с которым ассоциированы данные.
     * @param mixed $data - данные для сообщения или список данных (см. ниже) для нескольких сообщений.
     * @param bool $list - флаг, который указывает, что данные - это набор однотипных данных, по каждому элементу которых
     *                            будет сформировано сообщение. Если у цепочки разрешено формирование составного сообщения (флаг canBeMultiple),
     *                            и форматтер умеет его формировать, то в дальнейшем отправится одно сообщение.
     */
    public function addUserData($userId, $data, $list = false)
    {
        if (!isset($this->data[$userId])) {
            $this->data[$userId] = [];
        }
        if ($list) {
            $this->data[$userId] = array_merge($this->data[$userId], $data);
        } else {
            $this->data[$userId][] = $data;
        }
    }

    public function sendAll()
    {
        $this->prepare();

        foreach ($this->senders as $sender) {
            $sender->send();
        }
    }

    public function clearAll()
	{
		$this->data = [];
		foreach ($this->senders as $sender) {
			$sender->clearData();
		}
		gc_collect_cycles();
	}

    protected function prepare()
    {
        foreach ($this->data as $userId => $data) {
            $user = Users::find()->where(['user_id' => $userId])->one();
            if (!empty($user)) {
                $this->putMessageToSender($user, $data);
            }
        }
    }

    private function putMessageToSender(Users $user, $data)
    {
        foreach ($this->chain as $link) {
            // это группа:
            if (is_array($link)) {
                $method = $this->getGroupGateMethod($link);
                if ($method && !SenderGroupGate::$method($user, $this->devices)) {
                    continue;
                }
                if ($this->pushDataToGroupSenders($link, $user, $data) && $this->stopOnAccept) {
                    break;
                }
                /** @todo  реализовать логику группы */
                // это отдельный сендер:
            } elseif (is_string($link)) {
                $sender = $this->getSenderInstance($link);

                if ($sender !== false && $sender->applyMessage($user, $data) === true && $this->stopOnAccept) {
                    break; //сендер принял данные для отправки, прирываем цепочку.
                }
            }
        }
    }

    /**
     * В рамках экземпляра данного класса все сендеры являются одиночками.
     *
     * @param  $name
     * @return SenderInterface|false
     */
    private function getSenderInstance($name)
    {
        if (isset($this->senders[$name])) {
            return $this->senders[$name];
        }
        if (!class_exists($name)) {
            return false;
        }

        $sender = new $name($this->formatter);
        if ($sender instanceof SenderInterface) {
            $this->senders[$name] = $sender;

            return $sender;
        }

        return false;
    }

    private function getGroupGateMethod($link)
    {
        if (!isset($link['name'])) {
            return null;
        }
        $methodName = $this->type . ucfirst($link['name']);
        if (!method_exists(SenderGroupGate::class, $methodName)) {
            return null;
        }
        return $methodName;
    }

    private function pushDataToGroupSenders($link, Users $user, $data)
    {
        $senders = isset($link['senders']) && is_array($link['senders']) ? $link['senders'] : [];
        $result = 0;
        foreach ($senders as $senderName) {
            $sender = $this->getSenderInstance($senderName);
            if ($sender !== false) {
                $result += $sender->applyMessage($user, $data);
            }
        }
        return (bool)$result;
    }

    private function setSendersByDevices($devices)
    {
        $senders = [];
        foreach ($devices as $device) {
            $sender = '\common\components\services\senders\\' . ucfirst($device) . 'PushSender';
            if (class_exists($sender)) {
                $senders[] = $sender;
            }
        }
        $this->chain[0]['senders'] = $senders;
    }
}
