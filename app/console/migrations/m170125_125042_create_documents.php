<?php

use yii\db\Schema;
use yii\db\Migration;

class m170125_125042_create_documents extends Migration
{
    public static $table = "{{documents}}";
/*
CREATE TABLE documents (
    id SERIAL NOT NULL PRIMARY KEY,
    title VARCHAR(500),
    "text" TEXT NOT NULL DEFAULT '',
    draft TEXT NOT NULL DEFAULT ''
);

GRANT SELECT, INSERT, UPDATE, DELETE
ON TABLE documents
TO postgres;
*/
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this::$table, [

            'id' => $this->primaryKey(),
            'title' => $this->string(500),
            'text' => $this->text()->notNull()->defaultValue(''),
            'draft' => $this->text()->notNull()->defaultValue('')

        ], $tableOptions);

    }

    public function down()
    {
        $this->dropTable($this::$table);
    }

}
