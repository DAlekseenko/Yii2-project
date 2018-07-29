<?php
use common\helpers\Html;

?>

<h3><strong>Внимание!</strong></h3>
<p>Нажимая "Выйти на всех устройствах", Вы сбрасываете пароль для доступа к сервису МТС Деньги на всех устройствах
    (компьютеры, телефоны).</p>
<p>Для дальнейшего пользования сервисом МТС Деньги Вам потребуется пройти процедуру регистрации на каждом устройстве
    заново.</p>
<p>Используйте эту возможность только при крайней необходимости.</p>
<div class="logout-anywhere">
    <form id="logoutAnywhere">
        <?= Html::mtsButton('Выйти на всех устройствах', ['class' => 'logout-anywhere_button']); ?>
    </form>
    <div class="setting-error --logout-anywhere-error">Произошла ошибка. Повторите попытку позже.</div>
</div>