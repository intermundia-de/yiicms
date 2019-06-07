<?php

use yii\db\Migration;

/**
 * Class m180727_121932_add_blame_attributes_to_video_section
 */
class m180727_121932_add_blame_attributes_to_video_section extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%video_section}}','created_by',$this->integer());
        $this->addColumn('{{%video_section}}','updated_by',$this->integer());
        $this->addColumn('{{%video_section}}','created_at',$this->integer());
        $this->addColumn('{{%video_section}}','updated_at',$this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%video_section}}','created_by');
        $this->dropColumn('{{%video_section}}','updated_by');
        $this->dropColumn('{{%video_section}}','created_at');
        $this->dropColumn('{{%video_section}}','updated_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180727_121932_add_blame_attributes_to_video_section cannot be reverted.\n";

        return false;
    }
    */
}
