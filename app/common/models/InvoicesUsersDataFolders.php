<?php

namespace common\models;

/**
 * @property integer $id
 * @property integer $category_id
 * @property integer $users_data_id
 *
 * @property Categories $category
 */
class InvoicesUsersDataFolders extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'invoices_users_data_folders';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['category_id', 'users_data_id'], 'required'],
			[['id', 'category_id', 'users_data_id'], 'integer'],
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCategory()
	{
		return $this->hasOne(Categories::class, ['id' => 'category_id']);
	}
}
