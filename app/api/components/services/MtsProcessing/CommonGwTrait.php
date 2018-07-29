<?php

namespace api\components\services\MtsProcessing;

use yii;
use Psr\Log\NullLogger;
use PbrLibBelCommon\Protocol\MQ\MqMessage;
use PbrLibBelCommon\Protocol\McProcessing\McProcessingRequest;
use PbrLibBelCommon\Protocol\McProcessing\PaymentContractRequest;
use PbrLibBelCommon\Protocol\McProcessing\PaymentContractResponse;
use PbrLibBelCommon\Protocol\McProcessing\PaymentCancellationRequest;
use PbrLibBelCommon\Protocol\McProcessing\PaymentCancellationResponse;
use PbrLibBelCommon\Protocol\McProcessing\PaymentAuthorizationRequest;
use PbrLibBelCommon\Protocol\McProcessing\PaymentAuthorizationResponse;
use PbrLibBelCommon\Exceptions\McProcessingTransportException;

trait CommonGwTrait
{
	abstract protected function onTariffSuccess($paymentUuid, $oid);

	abstract protected function onTariffFail($paymentUuid, $oid, $code, $description);

	public function actionCommonGw()
	{
		$logger = new NullLogger();
		$connector = new PostXMLConnector($logger);
		$connector->setRequestResponse(yii::$app->request);
		$requestWrapper = $connector->get();
		$response = $this->generateResponse($requestWrapper->getMqMessageData());
		$responseWrapper = MqMessage::createFromValues($requestWrapper->getCorrelationID());
		$responseWrapper->setMqMessageData($response);

		return $connector->ack($responseWrapper);
	}

	protected function generateResponse(\DOMNode $node)
	{
		switch ($node->localName) {
			case 'PaymentContractRequest':
				/** @var PaymentContractRequest $request */
				$request = PaymentContractRequest::createFromDomNode($node);
				$response = $this->generatePaymentContractResponse($request);
				return $response->buildRootElement();
			case 'PaymentAuthorizationRequest':
				/** @var PaymentAuthorizationRequest $request */
				$request = PaymentAuthorizationRequest::createFromDomNode($node);
				$this->onTariffSuccess($request->getPaymentUUID(), $this->getOid($request));
				$response = $this->generatePaymentAuthorizationResponse($request);
				return $response->buildRootElement();
			case 'PaymentCancellationRequest':
				/** @var PaymentCancellationRequest $request */
				$request = PaymentCancellationRequest::createFromDomNode($node);
				$this->onTariffFail($request->getPaymentUUID(), $this->getOid($request), $request->getReasonCode(), $request->getReasonDescription());
				$response = $this->generatePaymentCancellationResponse($request);
				return $response->buildRootElement();
			default:
				throw new  McProcessingTransportException('Unknown request type');
		}
	}

	protected function generatePaymentContractResponse(PaymentContractRequest $request)
	{
		$response = PaymentContractResponse::createFromValues(
			$request->getPaymentUUID(),
			$request->getSum(),
			1,		// Протокол мобильной коммерции подразумевает отправку PaymentDelay и TextProfileId в ответе на
			1		// PaymentContract. МТС-деньги не реализует этот функционал. Используем заглушки.
		);
		return $response;
	}

	protected function generatePaymentAuthorizationResponse(PaymentAuthorizationRequest $request)
	{
		$response = PaymentAuthorizationResponse::createFromValues(
			$request->getPaymentUUID(),
			false,
			''
		);
		return $response;
	}

	protected function generatePaymentCancellationResponse(PaymentCancellationRequest $request)
	{
		$response = PaymentCancellationResponse::createFromValues(
			$request->getPaymentUUID(),
			false,
			''
		);
		return $response;
	}

	/**
	 * @param McProcessingRequest|PaymentAuthorizationRequest|PaymentCancellationRequest $request
	 * @return null|int
	 */
	private function getOid(McProcessingRequest $request)
	{
		@list(, $oid) = explode('_', $request->getPaymentForeignId());

		return isset($oid) ? $oid : null;
	}
}
