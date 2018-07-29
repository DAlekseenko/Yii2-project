<?php
/**
 * @var string $name
 * @var string $attribute
 * @var string $value
 * @var array $options
 * @var array $codeOptions
 * @var array $inputOptions
 *
 */
use common\helpers\Html;
?>
<span <?=Html::renderTagAttributes($options)?>>
	<?= Html::tag('div', '+375', $codeOptions)?>
	<?= Html::hiddenInput($name . '[code]', '+375', $codeOptions) ?>
	<?= Html::textInput($name . '[value]', substr($value, 3), $inputOptions) ?>
</span>