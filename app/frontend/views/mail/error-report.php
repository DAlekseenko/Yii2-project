<?
/**
 * @var $exception \Exception
 * @var $user \common\models\Users
 */
$user = Yii::$app->user->identity ?: null;
?>
<table>
	<tr>
		<td>Ошибка:</td><td><?= $exception->getMessage() ?></td>
	</tr>
	<tr>
		<td>Файл:</td><td><?= $exception->getFile() ?></td>
	</tr>
	<tr>
		<td>Строка:</td><td><?= $exception->getLine() ?></td>
	</tr>
	<tr>
		<td>Код:</td><td><?= $exception->getCode() ?></td>
	</tr>
	<tr>
		<td>Исключение:</td><td><?= get_class($exception) ?></td>
	</tr>
	<tr>
		<td>Пользователь:</td><td><?= isset($user) ? $user->user_id : '<i>N/A</i>' ?></td>
	</tr>
	<tr>
		<td>Телефон:</td><td><?= isset($user) ? $user->phone : '<i>N/A</i>' ?></td>
	</tr>

</table>
<hr>$_SERVER:<hr>
<pre><? isset($_SERVER) ? print_r($_SERVER) : 'Нет данных'; ?></pre>

<hr>$_COOKIE:<hr>
<pre><? isset($_COOKIE) ? print_r($_COOKIE) : 'Нет данных'; ?></pre>

<hr>$_SESSION:<hr>
<pre><? isset($_SESSION) ? print_r($_SESSION) : 'Нет данных'; ?></pre>

<hr>$_GET:<hr>
<pre><? isset($_GET) ? print_r($_GET) : 'Нет данных'; ?></pre>

<hr>$_POST:<hr>
<pre><? isset($_POST) ? print_r($_POST) : 'Нет данных'; ?></pre>
