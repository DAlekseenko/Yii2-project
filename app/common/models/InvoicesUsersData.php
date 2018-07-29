<?php

namespace common\models;

use api\components\formatters\EntitiesFormatter;
use Yii;
use common\models\entities\InvoicesFolderEntity;

/**
 * This is the model class for table "invoices_users_data".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $service_id
 * @property string $identifier
 * @property string $date_create
 * @property string $date_delete
 * @property string $visible_type
 * @property string $description
 * @property bool   $is_invoice
 *
 * @property Services $service
 * @property Users $user
 */
class InvoicesUsersData extends \yii\db\ActiveRecord implements \JsonSerializable
{
	const VISIBILITY_NONE = 0;
	const VISIBILITY_CRON = 1;
	const VISIBILITY_USER = 2;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'invoices_users_data';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['service_id', 'identifier', 'user_id'], 'required'],
			[['user_id', 'service_id'], 'integer'],
			['visible_type', 'in', 'range' => [self::VISIBILITY_NONE, self::VISIBILITY_CRON, self::VISIBILITY_USER]],
			[['identifier', 'description'], 'filter', 'filter' => 'trim'],
			[['identifier', 'description'], 'string', 'max' => 100],
			[['date_create', 'date_delete'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'user_id' => 'User ID',
			'service_id' => 'Service ID',
			'identifier' => 'Номер лицевого счета',
			'description' => 'Сохранить под именем',
			'date_create' => 'Date Create',
			'date_delete' => 'Date Delete',
			'visible_type' => 'visible_type',
		];
	}

	public function validate($attributeNames = null, $clearErrors = true)
	{
		if (parent::validate($attributeNames, $clearErrors) === false) {
			return false;
		}
		// Если у нас новая привязка или у привязки меняется identifier, то необходимо проверить,
		// не существует ли у пользователя аналогичная привязка.
		if ($this->isNewRecord || $this->isAttributeChanged('identifier')) {
			$oldInvoice = $this->getUserActiveItem($this->user_id, $this->service_id, $this->identifier);
			if (!empty($oldInvoice) && $oldInvoice->visible_type == 2) {
				$this->addError('identifier', 'Уже добавлена аналогичная услуга');
			}
		}
		return !$this->hasErrors();
	}

	public static function getNewInstance($userId, $serviceId, $identifier, $description)
	{
		$invoicesUsersData = new static();
		$invoicesUsersData->user_id = $userId;
		$invoicesUsersData->description = $description;
		$invoicesUsersData->service_id = $serviceId;
		$invoicesUsersData->identifier = $identifier;
		$invoicesUsersData->visible_type = static::VISIBILITY_USER;

		return $invoicesUsersData;
	}

	/** @deprecated */
	public static function getActiveUserIdList()
	{
		$u = Users::tableName();
		$ud = self::tableName();
		return self::find()
			->select("$ud.user_id")
			->leftJoin($u, "$ud.user_id = $u.user_id")
			->where("$ud.identifier IS NOT NULL AND $ud.visible_type > 0 AND $u.subscription_status > 0")
			->andWhere(['>', "$ud.date_create", date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 50)])
			->groupBy("$ud.user_id")
			->orderBy("$ud.user_id")
			->column();
	}

	/**
	 * @param $userId
	 * @param $serviceId
	 * @param $identifier
	 * @return array|bool|InvoicesUsersData
	 */
	public static function getUserActiveItem($userId, $serviceId, $identifier)
	{
		$u = Users::tableName();
		$ud = self::tableName();
		return self::find()
			->leftJoin($u, "$ud.user_id = $u.user_id")
			->where("$ud.visible_type > 0 AND $u.subscription_status > 0")
			->andWhere(['>', "$ud.date_create", date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 50)])
			->andWhere(["$u.user_id" => $userId, "$ud.service_id" => $serviceId, "$ud.identifier" => $identifier])
			->one();
	}

	public function beforeSave($insert)
	{
		if ($insert) {
			self::deleteAll(['user_id' => $this->user_id, 'service_id' => $this->service_id, 'identifier' => $this->identifier]);
		}
		return parent::beforeSave($insert);
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
	public function getUser()
	{
		return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
	}

	/**
	 * @param int $userId
	 * @param int $serviceId
	 * @param int $identifier
	 * @return bool
	 * @throws \Exception
	 */
	public static function createInvoiceData($userId, $serviceId, $identifier = null)
	{
		if (self::find()->where(['user_id' => $userId, 'service_id' => $serviceId, 'identifier' => $identifier])->exists()) {
			return true;
		}
		$newInvoice = new self();
		$newInvoice->user_id = $userId;
		$newInvoice->service_id = $serviceId;
		$newInvoice->identifier = $identifier;

		return $newInvoice->save();
	}

	/**
	 * @param array $ignoredDefault
	 * @return InvoicesFolderEntity[]|array
	 */
	public static function getDefaultFolders(array $ignoredDefault = [])
	{
		return Categories::find()
			->joinWith('categoriesInfo')
			->where([CategoriesInfo::tableName() . '.is_global' => true])
			->andWhere(Categories::tableName() . '.date_removed IS NULL')
			->andWhere(['not in', Categories::tableName() . '.key', $ignoredDefault])
			->allAsEntities(InvoicesFolderEntity::class);
	}

	/**
	 * @param array $ignoredDefault
	 * @return array
	 */
	public static function getList(array $ignoredDefault = [])
	{

		$folderList = self::getDefaultFolders($ignoredDefault);

		$invoiceUsersData = InvoicesUsersData::find()
			 ->with('service', 'service.servicesInfo', 'service.category.categoriesInfo')
			 ->where(['visible_type' => self::VISIBILITY_USER])->currentUser()->all();

		foreach ($invoiceUsersData as $item) {
			$category = Categories::getGlobalByCategoryId($item->service->category_id);
			if (!isset($folderList[$category->id])) {
				$folderList[$category->id] = new InvoicesFolderEntity($category, false);
			}
			$folderList[$category->id]->appendItem($item);
		}

		return $folderList;
	}

	public function setDeleted()
	{
		return self::updateAll(['date_delete' => 'now()', 'visible_type' => self::VISIBILITY_NONE], ['id' => $this->id]);
	}

	/**
	 * @inheritdoc
	 * @return InvoicesUsersDataQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new InvoicesUsersDataQuery(get_called_class());
	}

	public function jsonSerialize()
	{
		$categoryName = $this->service->category->name;
		$serviceName = $this->service->name;

		return [
			'id' => $this->id,
			'service_id' => $this->service_id,
			'service' => EntitiesFormatter::serviceFormatter($this->service),
			'category' => EntitiesFormatter::categoryFormatter($this->service->category),
			'name' => $categoryName . ' / ' . $serviceName,
			'category_name' => $categoryName,
			'service_name' => $serviceName,
			'description' => $this->description,
			'identifier' => $this->identifier,
			/** @todo выставлять картинки */
			'img' => EntitiesFormatter::getEntityImg($this->service)
		];
	}
}