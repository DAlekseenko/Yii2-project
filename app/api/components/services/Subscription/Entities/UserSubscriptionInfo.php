<?php

namespace api\components\services\Subscription\Entities;

class UserSubscriptionInfo implements \JsonSerializable
{
	/** @var  string */
	protected $uuid;

	/** @var  int */
	protected $status;

	/** @var bool  */
	protected $agreementRequired;

	/** @var string */
	protected $info = '';

	public function __construct($uuid, $agreementRequired, $status, $activateText)
	{
		$this->agreementRequired = (bool) $agreementRequired;
		$this->uuid = $uuid;
		$this->status = (int) $status;
		$this->info = $activateText;
	}

	/**
	 * @return string
	 */
	public function getUuid()
	{
		return $this->uuid;
	}

	/**
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function getInfo()
	{
		return $this->info;
	}

	public function isAgreementRequired()
	{
		return $this->agreementRequired;
	}

	public function jsonSerialize()
	{
		return [
			'uuid' => $this->uuid,
			'status' => $this->status,
			'info' => $this->info,
			'agreement_required' => $this->agreementRequired
		];
	}
}
