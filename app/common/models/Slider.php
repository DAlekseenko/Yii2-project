<?php

namespace common\models;

use common\components\behaviors\ImgBehavior;
use yii\web\UploadedFile;
use api\components\formatters\EntitiesFormatter;

/**
 * @mixin ImgBehavior
 *
 * @property int 	$id
 * @property string $text
 * @property string $placeholder
 * @property float  $offset_x
 * @property float  $offset_y
 * @property string $description
 * @property int    $type
 * @property string $data
 */
class Slider extends \yii\db\ActiveRecord
{
	const TYPE_SERVICE_INVOICE = 1;

	public $banner;

	public function rules()
	{
		return [
			[['text', 'placeholder'], 'required'],
			//['service_id', 'serviceValidator'],
			['banner', 'bannerValidator'],
			[['id', 'type'], 'integer'],
			[['offset_x', 'offset_y'], 'double'],
			[['description'], 'string', 'max' => 100],
			['data', 'string'],
			[['text'], 'string', 'max' => 68],
			[['placeholder'], 'string', 'max' => 32],
			['banner', 'file', 'extensions' => $this->getExtensions(), 'maxFiles' => 1],
			['banner', 'image', 'maxWidth' => 1000, 'maxHeight' => 320],
		];
	}

	public function attributeLabels()
	{
		return [
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

	public function getUkey()
	{
		return 'banner_' . $this->id;
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

	public static function findActiveBanners($location_id = null)
	{
		$result = [];
		/** @var self[] $banners */
		$banners = self::find()->all();

		$locationIds = Locations::getCurrentLocationIds($location_id);
		$locationMeta = ["'glob'"];
		if (isset($locationIds[1])) {
			$locationMeta[] = "'loc{$locationIds[1]}'";
		}
		if (isset($locationIds[2])) {
			$locationMeta[] = "'loc{$locationIds[2]}'";
		}

		$pdo = \yii::$app->db->getMasterPdo();
		$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

		foreach ($banners as $banner) {
			$serviceIds = preg_split('/\n/', $banner->data);
			foreach ($serviceIds as &$serviceId) {
				$serviceId = (int) substr($serviceId, 0, 11);
			}
			if (empty($serviceIds)) {
				continue;
			}

			$sql = 'SELECT * FROM services WHERE date_removed IS NULL AND id IN (' . implode(',', $serviceIds) . ') AND  meta ?| array[' . implode(',', $locationMeta) . '] order by location_id DESC LIMIT 1';

			$service = $pdo->query($sql)->fetchObject(Services::class);

			if (empty($service)) {
				continue;
			}
			$userData = InvoicesUsersData::find()->where(['visible_type' => 2, 'service_id' => $service->id, 'user_id' => \yii::$app->user->id])->one();
			if (!empty($userData)) {
				continue;
			}

			$result[] = array_merge($banner->getAttributes(['text', 'placeholder', 'description', 'offset_x', 'offset_y']),
			[
				'service_id' => $service->id,
				'img' => EntitiesFormatter::getEntityImg($service),
				'banner_img' => EXTERNAL_URL . substr($banner->getSrc(), 1),
				'name' => $service->category->name . ' / ' . $service->name,
				'mask' => isset($service->servicesInfo) ? $service->servicesInfo->mask : null,
			]);

		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'slider';
	}
}
