<?php
namespace common\models\virtual;

use common\models\Categories;
use common\models\Services;
use common\models\sql\ServicesSearchSql;
use Yii;
use yii\base\Model;

class ServicesSearch extends Model
{
	public $value;
	/** @var  Categories */
	public $category;

	/** @var  \PDOStatement */
	public $result;

	public function rules()
	{
		return [
			[['value'], 'required', 'message' => 'Введите более 2х символов'],
			[['value'], 'string', 'min' => 3, 'tooShort' => 'Введите более 2х символов'],
			['category', 'safe'],
		];
	}

	public function doSearch($sortMode = Services::SORT_MODE_DEFAULT)
	{
		$searchEngine = new ServicesSearchSql($this->category, trim($this->value));
		$searchEngine->setSortMode($sortMode);

		$this->result = $searchEngine->findServices();
	}

	public function count()
	{
		return (!empty($this->result) && $this->result instanceof \PDOStatement) ? $this->result->rowCount() : 0;
	}

	public function fetch($limit)
	{
		$result = [];
		if (!empty($this->result) && $this->result instanceof \PDOStatement) {
			foreach (range(1, $limit) as $i) {
				$line = $this->result->fetchObject(Services::class);
				if (empty($line)) {
					break;
				}
				$result[] = $line;
			}
		}
		return $result;
	}

	public function fetchAutocomplete($limit)
	{
		$services = $this->fetch($limit);
		return array_map(function ($service) {
			return $this->prepareServiceAutocomplete($service);
		}, $services);
	}

	protected function prepareServiceAutocomplete(Services $service)
	{
		return $service->toArray(['id', 'name']);
	}
}
