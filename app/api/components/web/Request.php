<?php

namespace api\components\web;

use yii\web\HttpException;

class Request extends \yii\web\Request
{
	/**
	 * @throws HttpException
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init()
	{
		//Возможность работать как с POST, так и с GET запросами
		$data = @json_decode($this->getRawBody(), true);
		if (!empty($data) && strpos($this->getUrl(), 'post_request') !== false) {
			if (!isset($data['method'])) {
				throw new HttpException(400);
			}
			$this->setUrl('/api/' . $data['method']);
			if (isset($data['args']) && is_array($data['args'])) {
				$this->setQueryParams($data['args']);
			}
		}
	}
}
