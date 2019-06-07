<?php

use yii\db\Migration;

class m180625_123104_page extends Migration
{
    /**
     * @return bool|void
     */
    public function up()
    {
        $this->createTable('{{%page}}', [
            'id' => $this->primaryKey(),
            'view' => $this->string(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
            'deleted_at' => $this->bigInteger(),
            'deleted_by' => $this->integer()
        ]);
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->dropTable('{{%page}}');
    }
}
