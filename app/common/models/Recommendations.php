<?php

namespace common\models;

use common\components\behaviors\ImgBehavior;
use yii\web\UploadedFile;

/**
 * Class Recommendations
 * @package common\models
 *
 * @mixin ImgBehavior
 *
 * @property integer $id
 * @property integer $service_id
 * @property string  $key
 *
 * @property Categories $category
 * @property Services $service
 */
class Recommendations extends \yii\db\ActiveRecord
{
	public $link = false;

	public $icon;

	public function rules()
	{
		return [
			[['link'], 'linkValidation'],
            ['icon', 'bannerValidator'],
			['icon', 'file', 'extensions' => $this->getExtensions(), 'maxFiles' => 1],
			['icon', 'image', 'maxWidth' => 250, 'maxHeight' => 200],
		];
	}

	public function attributeLabels()
	{
		return [
			'link' => 'Категория/сервис',
			'icon' => 'Картинка баннера'
		];
	}

	public function behaviors()
	{
		return [
			'imgBehavior' => [
				'class' => ImgBehavior::class,
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'recommendations';
	}

    public function bannerValidator()
    {
        if ($this->icon === false) {
            $this->addError('icon', 'Необходимо ввести баннер');
            return false;
        }
        return true;
    }


	public function beforeValidate()
	{
		if (!empty($this->link)) {
			$service = Services::find()->where(is_numeric($this->link) ? ['id' => $this->link] : ['name' => $this->link])->one();
			if (!empty($service)) {
				$this->service_id = $service->id;
				return true;
			}
			$category = Categories::find()->where(is_numeric($this->link) ? ['id' => $this->link] : ['name' => $this->link])->one();
			if (!empty($category)) {
				$this->key = $category->key;
				return true;
			}
		}
		return true;
	}

	public function load($data, $formName = null)
	{
		$result = parent::load($data, $formName);
		if (empty($this->link)) {
			$this->link = false;
		}
		$this->icon = UploadedFile::getInstance($this, 'icon');

		return $result;
	}

	public function linkValidation()
	{
		if (empty($this->link)) {
			$this->addError('link', 'необходимо заполнить поле');
			return false;
		}
		if (!isset($this->key) && !isset($this->service_id)) {
			$this->addError('link', 'сервис/категория не найдены по данному запросу');
			return false;
		}
		return true;
	}

	public function getUkey()
	{
		return $this->id;
	}

	/**
	 * @return Categories|Services
	 */
	public function getEntity()
	{
		return $this->service ?: $this->category;
	}

	public function isService()
	{
		return $this->getEntity() instanceof Services;
	}

	/**
	 * @return \common\models\Categories|null
	 */
	public function getEntityParent()
	{
		if ($this->isService()) {
			return $this->getEntity()->category;
		}
		return $this->getEntity()->parents(1)->one();
	}

	public function afterSave($insert, $changedAttributes)
	{
		parent::afterSave($insert, $changedAttributes);
		if ($this->icon instanceof UploadedFile) {
			$prevIcon = $this->getImgPath();
			$newIcon = $this->getFolder() . $this->getUkey() . '.' . $this->icon->extension;

			if ($this->icon->saveAs($newIcon)) {
				if ($prevIcon && $newIcon != $prevIcon && file_exists($prevIcon)) {
					unlink($prevIcon);
				}
			}
		}
	}

	public function beforeDelete()
	{
		$prevIcon = $this->getImgPath();
		if ($prevIcon && file_exists($prevIcon)) {
			unlink($prevIcon);
		}
		return parent::beforeDelete();
	}

	public static function find()
	{
		return parent::find()->with(['category', 'service']);
	}

	public static function findCurrent()
	{
		$c = Categories::tableName();
		$s = Services::tableName();
		$r = self::tableName();

		return parent::find()
					 ->leftJoin('services', "$s.id = $r.service_id")
					 ->leftJoin('categories', "$c.key = $r.key")
					 ->where("$r.service_id IS NOT NULL AND $s.date_removed IS NULL")
					 ->orWhere("$r.key IS NOT NULL AND $c.date_removed IS NULL")
			         ->all();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCategory()
	{
		return $this->hasOne(Categories::className(), ['key' => 'key']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getService()
	{
		return $this->hasOne(Services::className(), ['id' => 'service_id'])->with('servicesInfo');
	}
}
