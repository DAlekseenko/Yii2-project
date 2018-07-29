<?php
/**
 * Created by PhpStorm.
 * User: kirsanov
 * Date: 07.02.2018
 * Time: 17:41
 */

namespace api\components\services\Auth;

use api\components\services\Auth\DTO\AuthData;
use api\components\services\Auth\Exceptions\AuthDataConvertionException;

class AuthDataConverter
{
	private $secureKey = 'Avr9qZgM4XD5-HxsQ6kDxrabQBkHHzJK';

	/**
	 * @return \yii\base\Security
	 */
	protected function getSecurity()
	{
		return \Yii::$app->getSecurity();
	}

	/**
	 * @param  string $encodedToken
	 * @return AuthData
	 * @throws AuthDataConvertionException
	 */
	public function decodeAuthData($encodedToken)
	{
		$dto = unserialize($this->getSecurity()->decryptByKey(base64_decode($encodedToken), $this->secureKey));
		if ($dto instanceof AuthData) {
			return $dto;
		}
		throw new AuthDataConvertionException('Unable to decode auth data');
	}

	/**
	 * @param  AuthData $dto
	 * @return string
	 */
	public function encodeAuthData(AuthData $dto)
	{
		return base64_encode($this->getSecurity()->encryptByKey(serialize($dto), $this->secureKey));
	}
}
