<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "services_count".
 *
 * @property integer $category_id
 * @property integer $location_id
 * @property integer $count
 *
 * @property Categories $category
 * @property Locations $location
 */
class ServicesCount extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'services_count';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['category_id', 'location_id'], 'required'],
			[['category_id', 'location_id', 'count'], 'integer']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'category_id' => 'Category ID',
			'location_id' => 'Location ID',
			'count' => 'Count',
		];
	}
}