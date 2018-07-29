<?php

namespace common\models;

use common\components\behaviors\ImgBehavior;
use yii\web\UploadedFile;

/**
 * Class InvoicesSlider
 * @package common\models
 *
 * @mixin ImgBehavior
 *
 * @property integer $service_id
 * @property string $text
 * @property string $placeholder
 * @property float  $offset_x
 * @property float  $offset_y
 * @property string $description
 *
 * @property Services $service
 * @property InvoicesUsersData $userData
 */
class InvoicesSlider extends \yii\db\ActiveRecord
{
	public $banner;

	public function rules()
	{
		return [
			[['service_id', 'text', 'placeholder'], 'required'],
			['service_id', 'serviceValidator'],
			['banner', 'bannerValidator'],
			[['offset_x', 'offset_y'], 'double'],
			[['description'], 'string', 'max' => 100],
			[['text'], 'string', 'max' => 68],
			[['placeholder'], 'string', 'max' => 32],
			['banner', 'file', 'extensions' => $this->getExtensions(), 'maxFiles' => 1],
			['banner', 'image', 'maxWidth' => 1000, 'maxHeight' => 320],
		];
	}

	public function attributeLabels()
	{
		return [
			'service_id' => 'Идентификатор сервиса',
			'text' => 'Общий текст'	,
			'description' => 'Дополнительный текст',
			'placeholder' => 'Имя идентификатора',
			'offset_x' => 'Сдвиг по оси X',
			'offset_y' => 'Сдвиг по оси Y',
			'banner' => 'Картинка баннера',
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
	 * @return array|self[]
	 */
	public static function findActiveBanners() {
		$ud = InvoicesUsersData::tableName();

		return self::find()
			->with('service', 'service.servicesInfo', 'service.category', 'service.category.categoriesInfo')
			->leftJoin($ud, self::tableName() . '.service_id = ' . $ud . '.service_id AND ' . $ud . '.user_id = ' . \yii::$app->user->id . ' AND ' . $ud . '.visible_type = ' . InvoicesUsersData::VISIBILITY_USER)
			->where($ud . '.id IS NULL')
			->all();
	}

	public function getUkey()
	{
		return 'banner_' . $this->service_id;
	}

	public function serviceValidator()
	{
		if ($this->isNewRecord && self::find()->where(['service_id' => $this->service_id])->one() != null) {
			$this->addError('service_id', 'Баннер для данного сервиса уже создан');
			return false;
		}
		if (empty(Services::findById($this->service_id))) {
			$this->addError('service_id', 'Сервис не существует');
			return false;
		}
		return true;
	}
	public function bannerValidator()
	{
		if ($this->banner === false) {
			$this->addError('banner', 'Необходимо ввести баннер');
			return false;
		}
		return true;
	}

	public function load($data, $formName = null)
	{
		$result = parent::load($data, $formName);

		$this->banner = UploadedFile::getInstance($this, 'banner');
		if (empty($this->banner) && $this->isNewRecord) {
			$this->banner = false;
		}

		return $result;
	}

	public function beforeDelete()
	{
		$prevIcon = $this->getImgPath();
		if ($prevIcon && file_exists($prevIcon)) {
			unlink($prevIcon);
		}
		return parent::beforeDelete();
	}

	public function afterSave($insert, $changedAttributes)
	{
		parent::afterSave($insert, $changedAttributes);
		if ($this->banner instanceof UploadedFile) {
			$prevIcon = $this->getImgPath();
			$newIcon = $this->getFolder() . $this->getUkey() . '.' . $this->banner->extension;

			if ($this->banner->saveAs($newIcon)) {
				if ($prevIcon && $newIcon != $prevIcon && file_exists($prevIcon)) {
					unlink($prevIcon);
				}
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'invoices_slider';
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getService()
	{
		return $this->hasOne(Services::className(), ['id' => 'service_id'])->with('servicesInfo');
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUserData()
	{
		return $this->hasOne(InvoicesUsersData::className(), ['service_id' => 'service_id']);
	}
}
