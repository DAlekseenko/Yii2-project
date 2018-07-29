<?php
namespace common\helpers;

class Html extends \yii\helpers\Html {

    public static function mtsA($text, $url = null, $options = [])
    {
		$options['class'] = isset($options['class']) ? $options['class'] . ' mts-button' : 'mts-button';
		$options['style'] = isset($options['style']) ? $options['style'] . ' text-decoration:none;' : 'text-decoration:none;';

        return parent::a($text, $url, $options);
    }

	/**
	 * Generates a button tag.
	 * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded.
	 * Therefore you can pass in HTML code such as an image tag. If this is is coming from end users,
	 * you should consider [[encode()]] it to prevent XSS attacks.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 * @return string the generated button tag
	 */
	public static function mtsButton($content = 'Button', $options = [])
	{
		if (!isset($options['type'])) {
			$options['type'] = 'submit';
		}
		$options['class'] = isset($options['class']) ? $options['class'] . ' mts-button' : 'mts-button';
		return static::tag('button', static::tag('span', $content), $options);
	}

	/**
	 * @param \yii\base\Model $model the model object
	 * @param string $attribute
	 * @param array $options
	 * @return string
	 */
	public static function activeMtsCheckbox($model, $attribute, $options = [])
	{
		$name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
		$value = static::getAttributeValue($model, $attribute);

		if (!array_key_exists('value', $options)) {
			$options['value'] = '1';
		}
		if (!array_key_exists('uncheck', $options)) {
			$options['uncheck'] = '0';
		}
		if (!array_key_exists('label', $options)) {
			$options['label'] = static::encode($model->getAttributeLabel(static::getAttributeName($attribute)));
		}

		$checked = "$value" === "{$options['value']}";

		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}

		return static::mtsCheckbox($name, $checked, $options);
	}

	public static function mtsCheckbox($name, $checked = false, $options = [])
	{
		if (empty($options['id'])) $options['id'] = self::generateUniqueId();
		$options['class'] = empty($options['class']) ? 'mts-checkbox' : 'mts-checkbox ' . $options['class'];

		$options['checked'] = (bool) $checked;
		$value = array_key_exists('value', $options) ? $options['value'] : '1';
		if (isset($options['uncheck'])) {
			// add a hidden field so that if the checkbox is not selected, it still submits a value
			$hidden = static::hiddenInput($name, $options['uncheck']);
			unset($options['uncheck']);
		} else {
			$hidden = '';
		}
		$label = isset($options['label']) ? $options['label'] : '';
		$labelOptions = isset($options['labelOptions']) ? $options['labelOptions'] : [];
		unset($options['label'], $options['labelOptions']);
		$content = static::input('checkbox', $name, $value, $options) . static::label($label, $options['id'], $labelOptions);
		return $hidden . $content;
	}

	public static function mtsRadiobox($name, $checked = false, $options = [])
	{
		if (!isset($options['id']) || empty($options['id'])) {
			$options['id'] = self::generateUniqueId();
		}
		$options['class'] = empty($options['class']) ? 'mts-radiobox' : 'mts-radiobox ' . $options['class'];

		$options['checked'] = (bool) $checked;
		$value = array_key_exists('value', $options) ? $options['value'] : '1';
		if (isset($options['uncheck'])) {
			// add a hidden field so that if the checkbox is not selected, it still submits a value
			$hidden = static::hiddenInput($name, $options['uncheck']);
			unset($options['uncheck']);
		} else {
			$hidden = '';
		}
		$label = isset($options['label']) ? $options['label'] : '';
		$labelOptions = isset($options['labelOptions']) ? $options['labelOptions'] : [];
		unset($options['label'], $options['labelOptions']);
		$content = static::input('radio', $name, $value, $options) . static::label($label, $options['id'], $labelOptions);
		return $hidden . $content;
	}

	private static function generateUniqueId()
	{
		static $count = 0;
		return 'mts-checkbox-' . $count++;
	}
}