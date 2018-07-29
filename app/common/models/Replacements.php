<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "replacements".
 *
 * @property integer $id
 * @property string $target
 * @property string $search
 * @property string $replace
 * @property boolean $use_regexp
 * @property boolean $terminate
 * @property integer $order
 */
class Replacements extends \yii\db\ActiveRecord
{
	const TARGET_ERIP_ERROR = 'eripErrorMessage';

	public static $targets = [
		self::TARGET_ERIP_ERROR => self::TARGET_ERIP_ERROR,
	];

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'replacements';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['target', 'search', 'replace'], 'required'],
			[['search', 'replace'], 'string'],
			[['use_regexp', 'terminate'], 'boolean'],
			[['order'], 'integer'],
			[['target'], 'string', 'max' => 100],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id'         => 'Идентификатор',
			'target'     => 'Область применения',
			'search'     => 'Искомое значение',
			'replace'    => 'Значение замены',
			'use_regexp' => 'Регулярное выражение',
			'terminate'  => 'Последняя замена',
			'order'      => 'Порядок',
		];
	}

	/**
	 * @param $target
	 * @return array|\yii\db\ActiveRecord[]|Replacements[]
	 */
	public static function findAllByTarget($target)
	{
		return self::find()
				   ->where(['target' => $target])
				   ->orderBy('order')
				   ->all() ?: [];
	}

	public static function apply($target, $text)
	{
		$result = $text;

		foreach (self::findAllByTarget($target) as $replacement) {
			$previous = $result;
			$result = $replacement->use_regexp
				? preg_replace($replacement->search, $replacement->replace, $result)
				: str_replace($replacement->search, $replacement->replace, $result);
			$isChanged = ($previous != $result);
			if ($isChanged AND $replacement->terminate) {
				break;
			}
		}

		return $result;
	}

	public function beforeSave($insert)
	{
		if (empty($this->order)) {
			$this->order = 0;
		}
		return parent::beforeSave($insert);
	}
}
