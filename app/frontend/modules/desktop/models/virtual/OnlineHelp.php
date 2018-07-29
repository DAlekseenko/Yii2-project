<?php
namespace frontend\modules\desktop\models\virtual;

use Yii;
use yii\base\Model;

class OnlineHelp extends Model
{
	public $subject;
	public $text;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['subject', 'text'], 'required'],
		];
	}

	public function attributeLabels()
	{
		return [
			'subject' => 'Заголовок',
			'text' => 'Описание',
		];
	}

	public function send()
	{
		if ($this->validate()) {
			$config = Yii::$app->params['onlineHelp'];
			return Yii::$app->mailer->compose()
									->setFrom($config['from'])
									->setTo($config['to'])
									->setSubject($this->subject)
									->setTextBody($this->text)
									->send();
		}
		return false;
	}
}
