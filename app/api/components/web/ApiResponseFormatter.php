<?php
namespace api\components\web;

use yii\web\JsonResponseFormatter;

class ApiResponseFormatter extends JsonResponseFormatter
{
	public function format($response)
	{
		/**@var Response $response*/
		switch($response->apiFormat) {
			case Response::API_FORMAT_AS_IS:
				break;
			case Response::API_FORMAT_DEFAULT:
				$response->data = [
					'name' => $response->statusText,
					'status' => $response->getStatusCode(),
					'result' => is_scalar($response->data) ? ['value' => (string) $response->data] : $response->data,
				];
				break;
		}
		if ($response->responseMessage) {
			$response->data['message'] = $response->responseMessage;
		}
		if ($response->responseType) {
			$response->data['type'] = $response->responseType;
		}
		parent::format($response);
	}
}