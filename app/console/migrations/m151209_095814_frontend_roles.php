<?php

use yii\db\Schema;
use yii\db\Migration;

class m151209_095814_frontend_roles extends Migration
{
	public function up()
	{
		$adminRole = Yii::$app->authManager->createRole('admin');
		$adminRole->description = 'Админ';
		Yii::$app->authManager->add($adminRole);

		$userRole = Yii::$app->authManager->createRole('user');
		$userRole->description = 'Пользователь';
		Yii::$app->authManager->add($userRole);

		Yii::$app->authManager->addChild($userRole, $adminRole);
	}

	public function down()
	{
		return Yii::$app->db->createCommand('truncate table {{%auth_item}} cascade')->execute();
	}

	/*
	// Use safeUp/safeDown to run migration code within a transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}
