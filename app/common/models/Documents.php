<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "documents".
 *
 * @property integer $id
 * @property string $alias
 * @property string $title
 * @property string $text
 * @property string $draft
 */
class Documents extends \yii\db\ActiveRecord
{
    const KEY_FAQ   = 'faq';
    const KEY_RULES = 'rules';
    const KEY_SOCIAL = 'social';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'documents';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['alias'], 'required'],
            ['alias', 'match', 'pattern' => '/^[a-z0-9][\w\-_]*$/isu'],
            [['alias'], 'string', 'max' => 100],
            [['alias'], 'unique'],
            [['title'], 'string', 'max' => 500],
            [['text', 'draft'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'title' => 'Заголовок',
            'text' => 'Текст документа',
            'draft' => 'Черновик документа',
            'alias' => 'Псевдоним',
        ];
    }

    public static function findByKey($key)
    {
        return parent::findOne(['alias' => $key]);
    }

}
