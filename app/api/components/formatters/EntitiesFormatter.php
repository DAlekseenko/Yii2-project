<?php

namespace api\components\formatters;

use api\models\admin\Users;
use common\models\Invoices;
use common\models\PaymentFavorites;
use common\models\PaymentTransactions;
use common\models\Services;
use common\models\Categories;
use common\components\services\Helper;
use common\components\behaviors\ImgBehavior;
use yii\helpers\ArrayHelper;

class EntitiesFormatter
{
    /**
     * @param $entity Services|Categories
     * @return string
     */
    public static function getEntityImg($entity)
    {
        return substr(EXTERNAL_URL, 0, -1) . ($entity->hasImg(ImgBehavior::IMG_MOBILE) ? $entity->getSrc(ImgBehavior::IMG_MOBILE) : '/img/default.png');
    }

    /**
     * @param Categories $category
     * @return array
     */
    public static function categoryFormatter(Categories $category, array $children = [])
    {
        $parents = $category->getCategoryNamePath(true);

        $top = [];
        foreach ($children as $child) {
            if ($child instanceof Categories) {
                $top[] = self::categoryFormatter($child);
            } else if ($child instanceof Services) {
                $top[] = self::serviceFormatter($child);
            }
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'is_category' => true,
            'img' => self::getEntityImg($category),
            'count' => $category->services_count,
            'top' => $top,
            'path' => empty($parents) ? null : implode(' / ', $parents),
            'u_key' => $category->getUkey()
        ];
    }

    /**
     * @param Services $service
     * @return array
     */
    public static function serviceFormatter(Services $service)
    {
		$parents = $service->category->getParents(true);

        return [
            'id' => $service->id,
            'name' => $service->name,
            'is_category' => false,
            'identifier_name' => $service->getIdentifierName() ?: 'Номер лицевого счета',
            'mask' => isset($service->servicesInfo) ? $service->servicesInfo->mask : null,
            'img' => self::getEntityImg($service),
            'location' => isset($service->location) ? implode(', ', $service->location->getLocationPath()) : null,
            'path' => empty($parents) ? null : implode(' / ', ArrayHelper::getColumn($parents, 'name')),
			'parents' => empty($parents) ? null : self::categorySetFormatter($parents),
            'u_key' => $service->getUkey()
        ];
    }

    public static function transactionFormatter(PaymentTransactions $transaction, $withFields = false, $withEripData = false, $advanced = false)
    {
        $result = [
            'id' => $transaction->id,
            'item_name' => $transaction->item_name ?: $transaction->service->name,
            'transaction_uuid' => $transaction->uuid,
            'key' => $transaction->getTransactionKey(),
            'service' => self::serviceFormatter($transaction->service),
            'service_name' => $transaction->service->name,
            'status' => $transaction->status,
            'method' => $transaction->method,
            'total_sum' => Helper::prepareSum($transaction->sum),
            'sum' => Helper::prepareSum($transaction->getSum()),
            'commission' => Helper::prepareSum($transaction->getCommission()),
            'currency' => $transaction->getCurrency(),
            'date_create' => $transaction->date_create,
            'date_pay' => $transaction->date_pay,
			'server_time' => $transaction->bank_date_create,
            'img' => self::getEntityImg($transaction->service),
        ];

        if ($withFields) {
            $result['fields'] = $transaction->getFieldsMap();
        }

        if ($withEripData) {
            $result['erip_data'] = $transaction->getEripDataArray();
        }

        if ($advanced) {
        	Users::$serializeMode = Users::SERIALIZE_MODE_SIMPLE;
        	$result['user'] = isset($transaction->user) ? $transaction->user : null;
            $result['category'] = self::categoryFormatter($transaction->service->category);
            $result['category_name'] = $transaction->service->category->name;
            $result['city'] = isset($transaction->service->location->name) ? $transaction->service->location->name : null;
            $result['additional'] = [
                ['name' => 'Тел. ЕРИП для справок', 'value' => '141']
            ];
        }

        return $result;
    }

    public static function invoiceFormatter(Invoices $invoice)
    {
        $fieldMap = $invoice->transaction->getFieldsMap();
        return [
            'transaction_uuid' => $invoice->uuid,
            'item_name' => empty($invoice->params) ? $invoice->service->name : $invoice->params,
            'service' => self::serviceFormatter($invoice->service),
            'category' => self::categoryFormatter($invoice->service->category),
            'service_name' => $invoice->service->name,
            'category_name' => $invoice->service->category->name,
            'total_sum' => Helper::prepareSum($invoice->getTotalSum()),
            'sum' => Helper::prepareSum($invoice->getSum()),
            'commission' => Helper::prepareSum($invoice->getCommission()),
            'user_data' => isset($fieldMap[0]['name'], $fieldMap[0]['value']) ? ['name' => $fieldMap[0]['name'], 'value' => $fieldMap[0]['value']] : null,
            'pay_status' => $invoice->transaction->status,
            'img' => self::getEntityImg($invoice->service)
        ];
    }

    public static function favoriteFormatter(PaymentFavorites $favorite, $withFields = false)
    {
        $result = [
            'id' => $favorite->id,
            'name' => $favorite->name,
            'service' => self::serviceFormatter($favorite->service),
            'category' => self::categoryFormatter($favorite->service->category),
            'service_id' => $favorite->service_id,
            'service_name' => $favorite->service->name,
            'category_name' => $favorite->service->category->name,
            'img' => self::getEntityImg($favorite->service),
        ];

        if ($withFields) {
            $result['fields'] = $favorite->getFieldsMap();
        }

        return $result;
    }

    /**
     * @param array|\common\models\Categories[] $categories
     * @param array $children
     * @return array
     */
    public static function categorySetFormatter(array $categories, array $children = [])
    {
        if (empty($categories)) {
            return [];
        }

        $result = [];
        foreach ($categories as $category) {
            $child = isset($children[$category->id]) ? $children[$category->id] : [];
            $result[] = self::categoryFormatter($category, $child);
        }

        return $result;
    }

    /**
     * @param array|\common\models\Services[] $services
     * @return array
     */
    public static function serviceSetFormatter(array $services)
    {
        if (empty($services)) {
            return [];
        }

        $result = [];

        foreach ($services as $service) {
            $result[] = self::serviceFormatter($service);
        }

        return $result;
    }

    public static function transactionSetFormatter(array $transactions, $advanced = false)
    {
        if (empty($transactions)) {
            return [];
        }

        $result = [];
        foreach ($transactions as $item) {
            $result[] = self::transactionFormatter($item, $advanced, $advanced, $advanced);
        }

        return $result;
    }

    public static function favoriteSetFormatter(array $favorites, $advanced = false)
    {
        if (empty($favorites)) {
            return [];
        }

        $result = [];
        foreach ($favorites as $item) {
            $result[] = self::favoriteFormatter($item, $advanced);
        }

        return $result;
    }
}