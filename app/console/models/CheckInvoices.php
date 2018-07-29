<?php

namespace console\models;

use yii;
use eripDialog\EdApplication;
use eripDialog\EdLogger;
use eripDialog\EdRequest;
use common\models\Users;
use common\models\Invoices;
use common\models\ServicesLists;
use common\models\PaymentTransactions;
use eripDialog\exceptions\EdIncorrectInvoiceServiceException;
use eripDialog\exceptions\EdStepException;
use eripDialog\exceptions\EdEmptyInvoiceServiceException;
use eripDialog\exceptions\EdMultipleFieldsServiceException;
use eripDialog\EdHelper as H;

class CheckInvoices
{
	/**
	 * @param array $ids  - список идентификаторов состояний.
	 * @return array
	 * @throws \Exception
	 */
	public function run(array $ids)
	{
		/** @var InvoicesUpdateState[] $states */
		$states = InvoicesUpdateState::find()->where(['id' => $ids])->with(['userData'])->all();
		if (empty($states)) {
			throw new \Exception('Cant find any invoice states');
		}

		$result = [];
		foreach ($states as $state) {
			$userData = $state->userData;
			if (empty($userData)) {
				$state->delete();
				continue;
			}
			$checkResult = $this->check($userData->user_id, $userData->service_id, $userData->identifier);
			if ($checkResult instanceof \Exception) {
				$errors = $state->errors ?: [];
				$errors[] = $checkResult->getMessage();
				$state->errors = $errors;
				$state->save();
				continue;
			}
			if ($checkResult instanceof PaymentTransactions) {
				$invoice = Invoices::createInvoice($checkResult, $userData);
				if ($invoice instanceof Invoices) {
					$result[] = [$userData->user_id, $invoice->uuid];
				}
			}
			$state->delete();
		}
		return $result;
	}

	/**
	 * Проверяет наличие начисления.
	 *
	 * @param $userId
	 * @param $serviceId
	 * @param $identifier
	 * @return \Exception|0|PaymentTransactions
	 * @throws \yii\web\HttpException
	 */
	public function check($userId, $serviceId, $identifier)
	{
		$app = new EdApplication();
		try {
			return $this->runDialog($app, $userId, $serviceId, $identifier);
		} catch (\Exception $e) {
			yii::warning('CRON DIALOG ERROR: ' . $e->getMessage());
			yii::info([
						  'session' => $app->getCache()->getId(),
						  'user_id' => $userId,
						  'service_id' => $serviceId,
						  'identifier' => $identifier,
					  ]);
			return $e;
		}
	}

	/**
	 * @param EdApplication $app
	 * @param $userId
	 * @param $serviceId
	 * @param $identifier
	 * @return PaymentTransactions|0
	 * @throws EdIncorrectInvoiceServiceException - Если сервис по каким-то причинам не соответствует формату диалога начислений.
	 * @throws EdStepException
	 * @throws \Exception
	 */
	public function runDialog(EdApplication $app, $userId, $serviceId, $identifier)
	{
		$service = Services::findById($serviceId);
		if ($service->isInList([ServicesLists::LIST_WITH_EMPTY_INVOICES])) {
			throw new EdEmptyInvoiceServiceException();
		}

		$logger = new EdLogger('cronDialog', 'user_dialog');
		$edRequest = new EdRequest();
		$edResponse = $app->getResponse();
		$edRequest->set(['serviceCode' => $serviceId]);
		$app->setLogger($logger);

		/** @var Users $user */
		$user = Users::findIdentity($userId);
		if (empty($user)) {
			throw new \Exception('User was not found');
		}
		$app->setUser($user);

		for ($step = 1; $step <= 10; $step++) {
			if ($step == 10) {
				throw new EdIncorrectInvoiceServiceException('Steps overflow');
			}

			$app->setRequest($edRequest);
			$app->run();
			if ($edResponse->hasErrors()) {
				throw new EdStepException($edResponse->getError(), $step);
			}

			if ($edResponse->isSum()) {
				if ($step > 1) {
					break; // пришло поле суммы - завершаем цикл.
				}
				throw new EdIncorrectInvoiceServiceException('Unexpected sum on first step'); //если сумма приходит на первом шаге, то это не начисление
			}

			$editableFields = $edResponse->countEditableFields();
			if ($editableFields > 1 && $step == 1 || $editableFields >= 1 && $step > 1) {
				throw new EdMultipleFieldsServiceException();
			}
			// формируем данные для следующего запроса:
			$edRequest = new EdRequest();
			if ($step == 1 && $editableFields == 1) {
				$field = $edResponse->getEditableFields();
				reset($field);
				$name = key($field);
				$edRequest->set([H::F_MTS_MONEY_SESSION => $app->getCache()->getId(), 'fields' => [$name => $identifier]]);
			}
		}
		$sumValue = isset($edResponse->getSum()['value']) ? $edResponse->getSum()['value'] : 0;
		if ($sumValue == 0) {
			return 0;
		}
		$edRequest = new EdRequest();
		$edRequest->set([H::F_MTS_MONEY_SESSION => $app->getCache()->getId(), H::F_SUM => $sumValue]);
		$app->setRequest($edRequest);
		$app->run();

		if ($edResponse->hasErrors()) {
			throw new \Exception($edResponse->getError());
		}
		$app->getCache()->clear();

		return $app->getTransaction();
	}
}
