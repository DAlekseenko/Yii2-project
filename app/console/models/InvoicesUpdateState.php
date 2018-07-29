<?php

namespace console\models;

use common\models\InvoicesUsersDataQuery;
use common\models\Users;
use common\models\Invoices;
use common\models\InvoicesUsersData;

/**
 * @property integer $id
 * @property integer $user_data_id
 * @property integer $invoice_id
 * @property array $errors
 *
 * @property Invoices $invoice
 * @property InvoicesUsersData $userData
 */
class InvoicesUpdateState extends \common\models\AbstractModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invoices_update_state';
    }

    /**
     * @return InvoicesUsersDataQuery
     */
    public static function getUnaccountedUsersDataQuery()
    {
        $u = Users::tableName();
        $ud = InvoicesUsersData::tableName();
        $us = self::tableName();

        return InvoicesUsersData::find()
            ->leftJoin($u, "$ud.user_id = $u.user_id")
            ->leftJoin($us, "$ud.id = $us.user_data_id")
            ->where("$ud.identifier IS NOT NULL AND $ud.visible_type > 0 AND $u.subscription_status > 0 AND $us.user_data_id IS NULL and $ud.is_invoice = TRUE")
            ->andWhere(['>', "$ud.date_create", date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 50)]);
    }

    /**
     * @param InvoicesUsersData $userData
     * @return bool|InvoicesUpdateState
     */
    public static function saveUserDataInState(InvoicesUsersData $userData)
    {
        $state = new self();
        $state->user_data_id = $userData->id;

        return $state->save() ? $state : false;
    }

    /**
     * Сохраняет начисление в состояние.
     *
     * @param Invoices $invoice
     * @return bool|InvoicesUpdateState
     */
    public static function saveInvoiceInState(Invoices $invoice)
    {
        $state = new self();
        $state->user_data_id = $invoice->user_data_id;
        $state->invoice_id = $invoice->id;

        return $state->save() ? $state : false;
    }

    public function getInvoice()
    {
        return $this->hasOne(Invoices::class, ['id' => 'invoice_id']);
    }

    public function getUserData()
    {
        return $this->hasOne(InvoicesUsersData::class, ['id' => 'user_data_id']);
    }

    public static function saveInvoiceStateInCSV($file)
    {
		try {
			$f = fopen($file, 'w');
			$header = [
				'InvoicesUsersData ID',
				'Телефон',
				'User ID',
				'Service ID',
				'Номер лицевого счета',
				'Ошибки',
				'Старые нечисления',
			];
			fputcsv($f, $header);

			/** @var self $stateDate */
			foreach (self::find()->with(['userData', 'userData.user'])->each() as $stateDate) {
				fputcsv($f, [
					$stateDate->user_data_id,
					$stateDate->userData->user->phone,
					$stateDate->userData->user_id,
					$stateDate->userData->service_id,
					$stateDate->userData->identifier,
					isset($stateDate->errors) ? str_replace("\n", '', implode('; ', $stateDate->errors)) : '-',
					isset($stateDate->invoice_id) ? 1 : 0,
				]);
			}
			fclose($f);

			return true;
		} catch (\Exception $e) {

			\yii::error("Cant create file $file: {$e->getMessage()}");
			return false;
		}
    }
}
