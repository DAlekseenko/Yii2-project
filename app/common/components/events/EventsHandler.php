<?php

namespace common\components\events;

use common\models\Limits;
use common\models\LimitsGroup;
use common\models\PaymentTransactions;
use yii;
use yii\base\ModelEvent;
use yii\db\AfterSaveEvent;
use common\models\AssistTransactions;
use common\models\ServicesInfo;
use common\components\services\PhoneService;
use common\components\services\MqJobMessage;

class EventsHandler
{
	const ON_NEW_USER_CREATE = 'onNewUserCreate';

	const ON_USER_CHANGE = 'onUserChange';

	const ON_TRANSACTION_DELETE = 'onTransactionDelete';

	const ON_TRANSACTION_CREATE = 'onTransactionCreate';

	const ON_ASSIST_TRANSACTION_CHANGE = 'onAssistTransactionChange';

	const ON_TRANSACTION_CHANGE = 'onTransactionChange';

	public static function onNewUserCreate(AfterSaveEvent $event)
	{
		/** @var \common\models\Users $user */
		$user = $event->sender;
		if ($user->isReal()) {
			PhoneService::sendWelcomeMessage($user->phone);
		}
		\yii::info('NEW USER WAS CREATED: ');
		\yii::info($event->sender->getAttributes());
	}

	public static function onUserChange(ModelEvent $event)
	{
		/** @var \common\models\Users $user */
		$user = $event->sender;
		if ($user->isAttributeChanged('subscription_status') && $user->getOldAttribute('subscription_status') == $user::USER_TYPE_BLANK) {
			PhoneService::sendWelcomeMessage($user->phone);
		}
		\yii::info('USER WAS CHANGED: ');
		\yii::info($event->sender->getAttributes());
	}

	public static function onTransactionChange(ModelEvent $event)
	{
		/** @var \common\models\PaymentTransactions $tr */
		$tr = $event->sender;
		// Отлавливаем изменение статуса
		if ($tr->isAttributeChanged('status')) {
			$limitCategories = self::getLimitCategories($tr);

			// Статус транзакции изменился с "Новый" на "В процессе"
			if ($tr->getAttribute('status') === $tr::STATUS_IN_PROCESS) {
				$limit = Limits::find()->byUserId($tr->user_id)->oneForUpdate() ?: (new Limits())->setUserId($tr->user_id);
				$limit->day_sum += $tr->sum;
				$limit->save();
				foreach ($limitCategories as $limitCategory) {
					$limitGroup =
						LimitsGroup::find()->byUserIdAndCategoryValue($tr->user_id, $limitCategory)->oneForUpdate() ?:
						(new LimitsGroup())->setUserIdAndCategoryValue($tr->user_id, $limitCategory);
					$limitGroup->sum += $tr->sum;
					$limitGroup->save();
				}

				// Статус транзакции изменился с "В процессле" на "Успех/неуспех"
			} else if ($tr->getOldAttribute('status') === $tr::STATUS_IN_PROCESS) {
				$tr->date_pay = date('Y-m-d H:i:s', time());
				if ($tr->status === $tr::STATUS_SUCCESS) {
					$serviceInfo = $tr->service->servicesInfo ?: new ServicesInfo();
					$serviceInfo->setFields($tr->fields)->service_id = $tr->service_id;
					$serviceInfo->success_counter++;
					$serviceInfo->save();

				} elseif ($tr->status === $tr::STATUS_FAIL) {
					$limit = Limits::find()->byUserId($tr->user_id)->oneForUpdate() ?: (new Limits())->setUserId($tr->user_id);
					if ($limit->day_sum > 0) {
						$limit->day_sum -= $tr->sum;
						$limit->save();
					}
					foreach ($limitCategories as $limitCategory) {
						$limitGroup =
							LimitsGroup::find()->byUserIdAndCategoryValue($tr->user_id, $limitCategory)->oneForUpdate() ?:
							(new LimitsGroup())->setUserIdAndCategoryValue($tr->user_id, $limitCategory);
						if ($limitGroup->sum > 0) {
							$limitGroup->sum -= $tr->sum;
							$limitGroup->save();
						}
					}
				}
			}
		}
	}

	/**
	 * @param PaymentTransactions $tr
	 * @return array
	 */
	protected static function getLimitCategories(PaymentTransactions $tr)
	{
		try {
			/** @var \api\components\services\ParamsService\ParamsService $paramsService */
			$paramsService = \yii::$app->{SERVICE_PARAMS};

			$limitCategories = array_keys($paramsService->getCategoryLimits());
			$categoriesChain = \yii\helpers\ArrayHelper::getColumn($tr->service->category->getParents(true), 'key');

			return array_intersect($limitCategories, $categoriesChain);
		} catch (\Exception $e) {
			return [];
		}
	}

	/**
	 * Событие на удаление транзакции.
	 */
	public static function onTransactionDelete(ModelEvent $event)
	{
		\yii::info('Transaction delete: ');
		\yii::info($event->sender->getAttributes());
	}

	/**
	 * Событие на создание транзакции.
	 */
	public static function onTransactionCreate(AfterSaveEvent $event)
	{
		\yii::info('Transaction create: ');
		\yii::info($event->sender->getAttributes());
	}

	public static function onAssistTransactionChange(ModelEvent $event)
	{
		/** @var AssistTransactions $assistTransaction */
		$assistTransaction = $event->sender;
		if (
			$assistTransaction->getOldAttribute('status') != AssistTransactions::STATUS_APPROVED &&
			$assistTransaction->status ==  AssistTransactions::STATUS_APPROVED
		) {
			$assistTransaction->date_pay = date('Y-m-d H:i:s', time());
			/** @var \common\components\services\MqConnector $connector*/
			$connector = \Yii::$app->amqp;
			$connector->sendMessageDirectly(new MqJobMessage(yii::$app->params['jobsQueue'], 'handlers/assist-transaction-process', [$assistTransaction->order_number]));
		}
	}
}
