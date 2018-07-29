<?php

namespace api\components\services\BelGai;

use PbrLibBelCommon\Protocol\BelGai\BelGaiHandler as BaseHandler;

class BelGaiHandler extends BaseHandler
{

	/**
	 * Сообщение о штрафах
	 *
	 * @param string $clientId
	 * @param string $messageBody
	 */
	protected function handleProtocol($clientId, $messageBody)
	{
		// TODO: Implement handleProtocol() method.
	}

	/**
	 * Услуга акитивирована
	 *
	 * @param string $clientId
	 * @param string $messageBody
	 */
	protected function handleIndividualContractApproved($clientId, $messageBody)
	{
		// TODO: Implement handleIndividualContractApproved() method.
	}

	/**
	 * Услуга не активирована
	 *
	 * @param string $clientId
	 * @param string $messageBody
	 */
	protected function handleIndividualContractDenied($clientId, $messageBody)
	{
		// TODO: Implement handleIndividualContractDenied() method.
	}

	/**
	 * Услуга деактиввирована
	 *
	 * @param string $clientId
	 * @param string $messageBody
	 */
	protected function handleIndividualContractDisabled($clientId, $messageBody)
	{
		// TODO: Implement handleIndividualContractDisabled() method.
	}

	/**
	 * Оповещение о скором завершении услуги
	 *
	 * @param string $clientId
	 * @param string $messageBody
	 */
	protected function handleIndividualContractExpired($clientId, $messageBody)
	{
		// TODO: Implement handleIndividualContractExpired() method.
	}
}
