<?php

namespace common\models\entities;

use common\models\Categories;
use common\models\CategoriesInfo;
use common\models\InvoicesUsersData;
use api\components\formatters\EntitiesFormatter;

class InvoicesFolderEntity implements \JsonSerializable
{
	/** @var  Categories */
	protected $category;

	/** @var array|InvoicesUsersData[] */
	protected $data = [];

	protected $global;

	public function __construct(Categories $category, $isGlobal = true)
	{
		$this->category = $category;
		$this->global = $isGlobal;
	}

	public function appendItem(InvoicesUsersData $item)
	{
		$this->data[] = $item;
	}

	public function isGlobal()
	{
		return $this->global;
	}

	/**
	 * @return Categories|CategoriesInfo
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @return array|\common\models\InvoicesUsersData[]
	 */
	public function getUsersData()
	{
		return $this->data;
	}

	public function jsonSerialize()
	{
		$result = [
			'id' => $this->category->id,
			'name' => $this->category->name,
			'key' => $this->category->key,
			'placeholder' => isset($this->category->categoriesInfo) ? $this->category->categoriesInfo->identifier_name : null,
			'img' => EntitiesFormatter::getEntityImg($this->category),
			'services' => [],
			'is_global' => $this->isGlobal(),
			'has_children' => (bool) count($this->data),
		];

		foreach ($this->data as $invoicesUsersData) {
			$result['services'][] = $invoicesUsersData;
		}
		return empty($result) ? null : $result;
	}
}
