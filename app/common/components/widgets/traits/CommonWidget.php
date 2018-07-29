<?php
namespace common\components\widgets\traits;

trait CommonWidget {
	protected function renderViewFile($file, $params)
	{
		/**@var \yii\base\Widget $this*/
		return $this->renderFile(\Yii::$app->getBasePath() . '/../common/components/widgets/views/' . $file . '.php', $params);
	}

	//добавляем в начало строки(которая находится в массиве $array под ключом $key) строку $value
	protected function prependStringValue(&$array, $value, $key = 'class') {
		$array[$key] = isset($array[$key]) ? ' ' . $array[$key]: '';
		$array[$key] = $value . $array[$key];
	}
}