<?php

use yii\db\Schema;
use yii\db\Migration;

class m170131_140403_create_recommendations extends Migration
{
    public static $table = "{{replacements}}";
/*
CREATE TABLE replacements (
    id SERIAL NOT NULL PRIMARY KEY,
    "target" VARCHAR(100),
    "search" TEXT NOT NULL DEFAULT '',
    "replace" TEXT NOT NULL DEFAULT '',
    use_regexp BOOLEAN NOT NULL DEFAULT FALSE,
    "terminate" BOOLEAN NOT NULL DEFAULT FALSE,
    "order" INT NOT NULL DEFAULT 0
);

GRANT SELECT, INSERT, UPDATE, DELETE
ON TABLE replacements
TO postgres;

GRANT ALL PRIVILEGES ON SEQUENCE replacements_id_seq TO postgres;

CREATE INDEX idx_target_order ON replacements (target, "order");
*/
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this::$table, [

            'id' => $this->primaryKey(),
            'target' => $this->string(100),
            'search' => $this->text()->notNull()->defaultValue(''),
            'replace' => $this->text()->notNull()->defaultValue(''),
            'use_regexp' => $this->boolean()->notNull()->defaultValue(FALSE),
            'terminate' => $this->boolean()->notNull()->defaultValue(FALSE),
            'order' => $this->integer()->notNull()->defaultValue(0)

        ], $tableOptions);

        $this->createIndex('idx_target_order', $this::$table, ['target', 'order']);
    }

    public function down()
    {
        $this->dropTable($this::$table);
    }
}
