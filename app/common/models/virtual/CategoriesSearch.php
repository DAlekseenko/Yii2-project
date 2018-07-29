<?php
namespace common\models\virtual;

use common\models\Categories;
use common\models\CategoriesQuery;
use common\models\sql\CategoriesSearchSql;
use Yii;
use yii\base\Model;

class CategoriesSearch extends Model
{
	public $value;
	public $category;
	public $locationId;

	/** @var  \PDOStatement */
	public $result;

	public function rules()
	{
		return [
			[['value'], 'required', 'message' => 'Введите более 2х символов'],
			[['value'], 'string', 'min' => 3, 'tooShort' => 'Введите более 2х символов'],
			['category', 'safe'],
			['locationId', 'safe'],
		];
	}

	public function doSearch($sortMode = Categories::SORT_MODE_ALPHABET)
	{
		$searchEngine = new CategoriesSearchSql($this->category, trim($this->value), $this->locationId);
		$searchEngine->setSortMode($sortMode);
		$this->result = $searchEngine->findCategories();
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
				$line = $this->result->fetchObject(Categories::class);
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
		$categories = $this->fetch($limit);
		return array_map(function ($category) {
			return $this->prepareCategoryAutocomplete($category);
		}, $categories);
	}

	protected function prepareCategoryAutocomplete(Categories $category)
	{
		$result = $category->toArray(['id', 'name']);
		$result['servicesCount'] = $category->services_count;
		return $result;
	}
}
