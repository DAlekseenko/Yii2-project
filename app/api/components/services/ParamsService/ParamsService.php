<?php

namespace api\components\services\ParamsService;

use common\models\Params;

class ParamsService
{
	const PARAM_NAME_SUBSCRIPTION_MODE = 'subscription_mode';
	const PARAM_NAME_SUBSCRIPTION_TEST_PHONES = 'subscription_test_phones';
	const PARAM_NAME_MONTH_LIMIT = 'month_limit_sum';
	const PARAM_NAME_DAY_LIMIT = 'day_limit_sum';
	const PARAM_NAME_CATEGORIES_LIMIT = 'categories_limit_sum';
	const PARAM_NAME_MONTH_LIMIT_MSG = 'month_limit_msg';
	const PARAM_NAME_DAY_LIMIT_MSG = 'day_limit_msg';
	const PARAM_NAME_CATEGORY_LIMIT_MSG = 'category_limit_msg';
	const PARAM_NAME_OPERATION_LIMIT = 'operation_limit';

	const ENTITY_TYPE_GLOBAL = 'GLOBAL';

	protected $params = [];

	public function __construct()
	{
		/** @var Params[] $params */
		$params = Params::find()->all();
		foreach ($params as $param) {
			if ($param->entity_type == null) {
				$this->params[self::ENTITY_TYPE_GLOBAL][$param->name] = $param->getValue();
			}
		}
	}

	/**
	 * @param $name
	 * @return mixed
	 * @throws \Exception
	 */
	public function getGlobalParam($name)
	{
		if (!isset($this->params[self::ENTITY_TYPE_GLOBAL][$name])) {
			throw new \Exception('Parameter ' . $name . ' was not found');
		}
		return $this->params[self::ENTITY_TYPE_GLOBAL][$name];
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function isSubsModeOn()
	{
		return $this->getGlobalParam(self::PARAM_NAME_SUBSCRIPTION_MODE);
	}

	/**
	 * @return int
	 * @throws \Exception
	 */
	public function getMonthLimit()
	{
		return $this->getGlobalParam(self::PARAM_NAME_MONTH_LIMIT);
	}

	/**
	 * @return int
	 * @throws \Exception
	 */
	public function getDayLimit()
	{
		return $this->getGlobalParam(self::PARAM_NAME_DAY_LIMIT);
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getCategoryLimits()
	{
		return $this->getGlobalParam(self::PARAM_NAME_CATEGORIES_LIMIT) ?: [];
	}

	/**
	 * @return float
	 * @throws \Exception
	 */
	public function getOperationLimit()
	{
		return $this->getGlobalParam(self::PARAM_NAME_OPERATION_LIMIT);
	}

	/**
	 * @param $sum
	 * @return mixed
	 * @throws \Exception
	 */
	public function makeMonthLimitMsg($sum)
	{
		return str_replace('{sum}', $sum, $this->getGlobalParam(self::PARAM_NAME_MONTH_LIMIT_MSG));
	}

	/**
	 * @param $sum
	 * @return mixed
	 * @throws \Exception
	 */
	public function makeDayLimitMsg($sum)
	{
		return str_replace('{sum}', $sum, $this->getGlobalParam(self::PARAM_NAME_DAY_LIMIT_MSG));
	}

	/**
	 * @param  int $sum
	 * @param  string $categoryName
	 * @return string
	 * @throws \Exception
	 */
	public function makeCategoryLimitMsg($sum, $categoryName)
	{
		return str_replace(['{sum}', '{categoryName}'], [$sum, $categoryName], $this->getGlobalParam(self::PARAM_NAME_CATEGORY_LIMIT_MSG));
	}

	/**
	 * @param $phone
	 * @return bool
	 * @throws \Exception
	 */
	public function isSubsTestPhone($phone)
	{
		return in_array($phone, $this->getGlobalParam(self::PARAM_NAME_SUBSCRIPTION_TEST_PHONES));
	}
}
