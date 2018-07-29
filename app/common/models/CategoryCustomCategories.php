<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property integer $custom_category_id
 * @property integer $category_id
 * @property string $key
 *
 * @property CategoriesCustom parent
 */
class CategoryCustomCategories extends ActiveRecord
{
    public $category_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['custom_category_id', 'category_id', 'key'], 'required'],
            [['category_id'], 'categoriesIdValidator'],
            [['category_id'], 'unique', 'targetAttribute' => ['custom_category_id','key'], 'message' => 'Данная категория уже добавлена!'],
            [['custom_category_id'], 'integer'],
            [['key'], 'string', 'max' => 32],
            [['custom_category_id', 'category_id', 'key'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'category_id' => 'ID категории',
        ];
    }

    public function beforeValidate()
    {
        $this->key = '';
        if ($this->category_id && $category = Categories::findById($this->category_id)) {
            $this->key = $category->getUkey();
        }
        return parent::beforeValidate();
    }

    /**
     * Проверка на существование категории
     */
    public function categoriesIdValidator()
    {
        if (!Categories::find()->where(['id' => $this->category_id])->exists()) {
            $this->addError('category_id', 'Данная категория не найдена!');
        }
    }

    public function getParent()
    {
        return $this->hasOne(CategoriesCustom::className(), ['id' => 'custom_category_id']);
    }

    public static function getLink($categoryId, $key)
    {
        return self::find()->where(['custom_category_id' => $categoryId, 'key' => $key])->one();
    }
}
