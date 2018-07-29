<?php
namespace common\components\widgets;

use yii;
use yii\base\Widget;
use common\components\widgets\traits\CommonWidget;
use common\components\behaviors\ImgBehavior;

abstract class Icon extends Widget
{
	use CommonWidget;
	/**
	 * @var \common\models\Categories|\common\models\Services $item
	 */
	public $item;
	public $url;
	public $options = [];
	public $imgOptions = [];
	public $tagName = 'a';
	public $type = ImgBehavior::IMG_DEFAULT;
	public $dummy = true;

	protected $defaultView = 'default-icon';
	protected $imgView = 'icon';
	private $ignore = ['с/с'];

	public function init()
	{
		parent::init();
		$this->prependStringValue($this->options, 'payment-icon');
		$this->options['title'] = htmlspecialchars($this->item['name']);

		$this->prependStringValue($this->imgOptions, 'payment-icon_img');
		$this->imgOptions['alt'] = htmlspecialchars($this->item['name']);
	}

	public function run()
	{
		if (!$this->item->hasImg($this->type)) {
			return $this->dummy ? $this->getDefaultView() : '';
		}

		return $this->renderViewFile($this->imgView, [
			'item' => $this->item,
			'tagName' => $this->tagName,
			'options' => $this->options,
			'imgOptions' => $this->imgOptions,
			'type' => $this->type
		]);
	}

	protected function getDefaultView()
	{
		$options = $this->options;
		$options['class'] .= ' -default';
		$options['style'] = 'color: hsl(' . $this->getColor($this->item['name']) . ', 70%, 50%)';

		return $this->renderViewFile($this->defaultView, [
			'tagName' => $this->tagName,
			'text' => $this->getText($this->item['name']),
			'options' => $options,
		]);
	}

	protected function getText($name)
	{
		$name = str_replace($this->ignore, '', $name);
		$name = preg_replace('/[^\dа-яa-z]/ui', ' ', $name);

		$m = [];
		preg_match_all('/^[а-яa-z]|\s[а-яa-z]/ui', $name, $m);
		if (!empty($m[0]) && count($m[0]) >= 3) {
			$name = implode('', $m[0]);
		} else {
			if (mb_strlen($name, 'UTF-8') > 4) {
				$name = preg_replace('/[аеёиоуыэюяйъь]/ui', '', $name);
			}
		}

		$name = mb_strtoupper(str_replace(' ', '', $name), 'UTF-8');
		return mb_substr($name, 0, 4, 'UTF-8');
	}

	protected function getColor($name)
	{
		$number = hexdec(substr(md5($name), -2));

		return floor($number / 3);
	}
}