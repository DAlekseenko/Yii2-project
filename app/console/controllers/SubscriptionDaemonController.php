<?php

namespace console\controllers;

class SubscriptionDaemonController extends AbstractCronDaemon
{
	public function handler(array $params)
	{
		/** @var \api\components\services\Subscription\SubscriptionClient $subscriptionClient */
		$subscriptionClient = \yii::$app->{SERVICE_SUBSCRIPTION_CLIENT};

		while ($subscriptionClient->receive()) {}
	}
}
