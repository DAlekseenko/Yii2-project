<?php
/**
 * Created by PhpStorm.
 * User: kirsanov
 * Date: 07.02.2018
 * Time: 17:18
 */

namespace api\components\services\Auth\DTO;


final class AuthData
{
	private $userId;

	private $authKey;

	private $expires;

	public function __construct($userId, $authKey, $expires = null)
	{
		$this->userId  = $userId;
		$this->authKey = $authKey;
		$this->expires = $expires;
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * @return string
	 */
	public function getAuthKey()
	{
		return $this->authKey;
	}

	/**
	 * @return string|null
	 */
	public function getExpires()
	{
		return $this->expires;
	}
}
