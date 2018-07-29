<?php
/**
 * @var $this yii\web\View
 * @var $document \common\models\Documents
 * @var $changePasswordModel frontend\models\virtual\SettingsChangePassword
 * @var $changeUserInfoModel frontend\models\virtual\ChangeUserInfo
 * @var $context \yii\web\Controller|\frontend\controllersAbstract\AbstractController|\frontend\modules\desktop\components\behaviors\RenderLayout
 * @var $showSubscriptionTool boolean
 */
$context = $this->context;
$this->title = 'МТС Деньги - Настройки';
$this->params['header'] = 'Настройки';
$context->getBreadcrumbsLayout()->appendBreadcrumb('Настройки');
?>
<div class="<?= $this->context->isMobile ? '-mobile-padding' : '' ?>">
    <div class="settings-item --settings-item">
        <div class="settings-handler">
            <a class="settings-handler_link -dashed --settings-handler" id="change_password">Сменить пароль</a>
        </div>
        <div class="settings-item-wrap --settings-wrap" style="display: none">
            <div class="settings-item-content">
                <div class="pointer -settings-top"></div>
                <div class="--content-form">
                    <?= $this->render('//partial/settings/changePassword', ['changePasswordModel' => $changePasswordModel]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-item --settings-item">
        <div class="settings-handler">
            <a class="settings-handler_link -dashed --settings-handler" id="change_personal_data">Сменить ФИО</a>
        </div>
        <div class="settings-item-wrap --settings-wrap" style="display: none">
            <div class="settings-item-content">
                <div class="pointer -settings-top"></div>
                <div class="--content-form">
                    <?= $this->render('//partial/settings/changeUserInfo', ['changeUserInfoModel' => $changeUserInfoModel]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-item --settings-item">
        <div class="settings-handler">
            <a class="settings-handler_link -dashed --settings-handler" id="banking_agreement">Банковское соглашение</a>
        </div>
        <div class="settings-item-wrap --settings-wrap" style="display: none">
            <div class="settings-item-content">
                <div class="pointer -settings-top"></div>
                <div>
                    <?= $this->render('//partial/help/part/_agreement') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-item --settings-item">
        <div class="settings-handler">
            <a class="settings-handler_link -dashed --settings-handler" id="system_rules">Правила системы</a>
        </div>
        <div class="settings-item-wrap --settings-wrap" style="display: none">
            <div class="settings-item-content">
                <div class="pointer -settings-top"></div>
                <div>
                    <?= $document->text ?>
                </div>
            </div>
        </div>
    </div>
    <div class="settings-item --settings-item">
        <div class="settings-handler">
            <a class="settings-handler_link -dashed --settings-handler" id="logout_anywhere">Выйти на всех устройствах</a>
        </div>
        <div class="settings-item-wrap --settings-wrap" style="display: none">
            <div class="settings-item-content">
                <div class="pointer -settings-top"></div>
                <div>
                    <?= $this->render('//partial/settings/logOutOnAllDevices') ?>
                </div>
            </div>
        </div>
    </div>
    <? if ($showSubscriptionTool): ?>
    <div class="settings-item --settings-item">
        <div class="settings-handler">
            <a class="settings-handler_link -dashed --settings-handler" id="stop_subscription">Удалить услугу «МТС Деньги»</a>
        </div>
        <div class="settings-item-wrap --settings-wrap" style="display: none">
            <div class="settings-item-content">
                <div class="pointer -settings-top"></div>
                <div>
                    <?= $this->render('//partial/settings/stopSubscription') ?>
                </div>
            </div>
        </div>
    </div>
    <? endif; ?>
</div>