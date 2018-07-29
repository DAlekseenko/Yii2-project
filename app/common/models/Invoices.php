<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "invoices".
 *
 * @property integer $id
 * @property string $uuid
 * @property integer $sum
 * @property string $date_create
 * @property string $params
 * @property integer $user_id
 * @property integer $service_id
 * @property string $payment_date
 * @property string $fields_key
 *
 * @property Services|ServicesInfo $service
 * @property PaymentTransactions $transaction
 * @property Users $user
 */
class Invoices extends \yii\db\ActiveRecord
{
	public $user_data_id = null;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'invoices';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['uuid', 'params'], 'string'],
			[['user_id', 'service_id'], 'integer'],
			[['date_create', 'payment_date'], 'safe'],
			[['user_id', 'service_id'], 'required'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'uuid' => 'Uuid',
			'date_create' => 'Date Create',
			'params' => 'Params',
			'user_id' => 'User ID',
			'service_id' => 'Service ID',
			'payment_date' => 'Payment Date',
		];
	}

	public function getCurrency()
	{
		return $this->transaction->getCurrency();
	}

	/**
	 * @return int сумма + коммиссия
	 */
	public function getTotalSum()
	{
		return $this->transaction->sum;
	}

	public function getCommission()
	{
		return $this->transaction->getCommission();
	}

	public function getSum()
	{
		return $this->transaction->getSum();
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
	public function getTransaction()
	{
		return $this->hasOne(PaymentTransactions::className(), ['uuid' => 'uuid']);
	}


	/**
	 * Достаем все активные начисления с идентификатором привязки (если есть)
	 *
	 * @return \yii\db\Query
	 */
	public static function getActiveWithUserDataIdQuery()
	{
		$i = Invoices::tableName();
		$ud = InvoicesUsersData::tableName();
		$pt = PaymentTransactions::tableName();
		return self::find()
			->select("$i.*, $ud.id as user_data_id")
			->where("$i.payment_date IS NULL")
			->leftJoin($pt, "$pt.uuid = $i.uuid")
			->leftJoin($ud, "$pt.user_id = $ud.user_id AND $pt.service_id = $ud.service_id AND $ud.visible_type = " . InvoicesUsersData::VISIBILITY_USER . " AND trim(both '\"' from ($pt.fields->0->'value')::text) = $ud.identifier");
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
	}

	/**
	 * @inheritdoc
	 * @return InvoicesQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new InvoicesQuery(get_called_class());
	}

	/**
	 * Возвращает активное начисление по существующей привязке.
	 *
	 * @param InvoicesUsersData $usersData
	 * @return array|Invoices|null
	 */
	public static function findActiveByUsersData(InvoicesUsersData $usersData)
	{
		$key = md5($usersData->identifier);
		return self::find()->active($usersData->service_id, $key)->whereUserId($usersData->user_id)->one();
	}

	/**
	 * Возвращает активные начисления по соответствующему сервису и идентификатору.
	 *
	 * @param $serviceId
	 * @param $identifier
	 * @return array|InvoicesUsersData[]
	 */
	public static function findActiveInvoices($serviceId, $identifier)
	{
		$key = md5($identifier);
		return self::find()->active($serviceId, $key)->all();
	}

	/**
	 * Создает активное начисления используя привязку и транзакцию.
	 *
	 * @param PaymentTransactions $tr
	 * @param InvoicesUsersData $ud
	 * @return bool|Invoices
	 */
	public static function createInvoice(PaymentTransactions $tr, InvoicesUsersData $ud)
	{
		$transaction = Yii::$app->getDb()->beginTransaction();
		try {
			$invoice = new Invoices();

			$invoice->uuid = $tr->uuid;
			$invoice->user_id = $tr->user_id;
			$invoice->service_id = $tr->service_id;
			$invoice->fields_key = md5($ud->identifier);
			$invoice->date_create = date('Y-m-d H:i:s', time());
			if (!empty($ud->description)) {
				$invoice->params = $ud->description;
			}
			$invoice->save();

			$ud->visible_type = 2;
			$ud->date_create = date('Y-m-d H:i:s', time());
			$ud->save();

			$transaction->commit();

			return $invoice;
		} catch (\Exception $e) {
			$transaction->rollBack();
			return false;
		}
	}

	/**
	 * @param $userId int
	 *
	 * @inheritdoc
	 * @return InvoicesQuery
	 */
	public static function findActive($userId = null)
	{
		$t = Invoices::tableName();
		$q = self::find()->where("$t.payment_date IS NULL");
		if (isset($userId)) {
			$q->whereUserId($userId);
		} else {
			$q->currentUser();
		}
		return $q;
	}
}