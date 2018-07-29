<?php
/**
 * @var \frontend\models\InvoicesUsersData $model
 * @var \common\models\Services $service
 */
/* @var $this yii\web\View */
/* @var $form common\components\widgets\ActiveForm */
/* @var $model \frontend\models\InvoicesUsersData */
/* @var $service \console\models\Services */
use common\helpers\Html;
?>
<div id="createUserInvoiceContainer" class="create-user-invoice">
	<h2 class="create-user-invoice_header"><?=$service['name']?></h2>

	<form id="createUserInvoiceForm" action="/api/invoices/create-user-data" method="post">
		<div class="form-group -required">
			<input type="hidden" id="invoicesusersdata-service_id" name="service_id" value="<?= $service->id ?>">
			<div class="form-group_help"></div>
		</div>
		<div class="form-group -form-user-invoice -required">
			<label class="form-group_label" for="invoicesusersdata-identifier"><?= $service->getIdentifierName() ?: $model->getAttributeLabel('identifier') ?></label>
			<input type="text" id="invoicesusersdata-identifier" class="form-group_control" name="identifier">
			<div class="form-group_help"></div>
		</div>
		<div class="form-group -form-user-invoice">
			<label class="form-group_label" for="invoicesusersdata-description"><?= $model->getAttributeLabel('description')?></label>
			<input type="text" id="invoicesusersdata-description" class="form-group_control" name="description">
			<div class="form-group_help"></div>
		</div>
		<?=Html::mtsButton('Узнать начисления')?>
	</form>
</div>