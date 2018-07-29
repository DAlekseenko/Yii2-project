<?php

use yii\db\Schema;
use yii\db\Migration;

class m170126_102619_alter_documents_add_alias extends Migration
{
    public static $table = "{{documents}}";
/*
ALTER TABLE "documents"
    ADD COLUMN "alias" VARCHAR(100) NOT NULL DEFAULT md5(random()::text) UNIQUE;

ALTER TABLE "documents"
    ALTER COLUMN "alias" DROP DEFAULT;
*/
    public function safeUp()
    {
        $this->addColumn($this::$table, 'alias', 'VARCHAR(100) NOT NULL DEFAULT md5(random()::text) UNIQUE');

        $this->alterColumn($this::$table, 'alias', 'DROP DEFAULT');
    }

    public function safeDown()
    {
        $this->dropColumn($this::$table, 'alias');
    }

}
