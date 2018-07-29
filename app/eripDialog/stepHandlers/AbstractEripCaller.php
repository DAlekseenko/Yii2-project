<?php

namespace eripDialog\stepHandlers;

use common\models\Replacements;
use console\models\Services;
use eripDialog\EdRequest;
use eripDialog\EdHelper as H;
use common\models\PaymentTransactions;
use PbrLibBelCommon\Caller\WsCaller;

abstract class AbstractEripCaller extends AbstractHandler
{
	public function prepareEripRequest(EdRequest $request)
	{
		$get = $request->get();
		unset($get[H::F_TRANSACTION]);
		unset($get[H::F_MTS_MONEY_SESSION]);
		return $get;
	}

	public function beforeEripCall()
	{
		/** @var Services $service */
		$service = Services::findOne($this->request->getServiceCode());
		if (empty($service) || !empty($service->date_removed)) {
			$this->response->setError(H::ERROR_MSG_REMOVED_SERVICE);
			return false;
		}

		if (!isset($this->user) && isset($service->servicesInfo) && $service->servicesInfo->forbidden_for_guest == true) {
			$this->response->setError(H::ERROR_MSG_FORBIDDEN_FOR_GUEST);
			return false;
		}

		if (isset($this->request->get()['fields']) && is_array($this->request->get()['fields'])) {
			$this->cache->setFieldsValue($this->request->get()['fields']);
		}
		return true;
	}

	public function eripCall()
	{
		if ($this->response->hasErrors()) {
			return false;
		}

		$data = $this->prepareEripRequest($this->request);
		$caller = new WsCaller(H::getEripApiUrl());
        $result = $caller->bulkSetGetParameters($data)->call();

		$this->response->clear();
		$this->response->setResponse($result);
		$this->writeLog($data, $this->response->get());
	}

	public function afterEripCall()
	{
		$response = $this->response;

		if ($response->hasErrors()) {
			// Заменяем ошибки Ерипа на более понятные. Настраиваются из админки в разделе "Замены".
			$response->setError(Replacements::apply(Replacements::TARGET_ERIP_ERROR, $response->getError()));
			return false;
		}

		$this->cache->appendFields($response->getFields());

		$this->cache
			->appendProperty(H::F_SERVICE_CODE, $this->request->getServiceCode())
			->appendProperty(H::F_MODE, $this->getNextMode())
			->appendProperty(H::F_SID, $response->getSid());

		// если от ерипа пришла сумма.
		if ($response->isSum()) {
			$sum = $response->getSum();
			$transaction = new PaymentTransactions();
			$transaction->setNewTransaction($this->request->getServiceCode(), $sum, isset($this->user) ? $this->user->user_id : null);
			$this->app->processNewTransaction($transaction);
			$transaction->insert();
			if ($transaction->hasErrors()) {
				return false;
			}

			$response->addField(H::getSumField($sum, $transaction->getMaxSum()));
			$this->transaction = $transaction;
			$this->cache
				->appendProperty(H::F_TRANSACTION, $transaction->uuid)
				->appendProperty(H::F_PAN, $transaction->id)
				->appendProperty(H::F_RECEIPT, $transaction->uuid);
		}
		return true;
	}

	public function beforeAction()
	{
		return true;
	}

	public function afterAction() {}

	protected function writeLog($toErip, $fromErip)
	{
		$logger = $this->logger;
		$logger->clearFields();
		$userId = $phone = null;
		if (isset($this->user)) {
			$userId = $this->user->user_id;
			$phone = $this->user->phone;
		}
		$logger->addField('user_id', $userId);
		$logger->addField('phone', $phone);
		$logger->addField('session', $this->cache->getId());
		$logger->addField('to_erip', $toErip);
		$logger->addField('from_erip', $fromErip);

		$logger->writeLog();
	}
}
