<?php

use yii\db\Migration;

/**
 * Class m180727_084126_country_add_continent_id_foreign_key
 */
class m180727_084126_country_add_continent_id_foreign_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%country}}','continent_id',$this->integer(11)->after('id'));
        $this->addForeignKey(
            'fk_country_continent',
            '{{%country}}',
            'continent_id',
            '{{%continent}}',
            'id',
            'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_country_continent','{{%country}}');
        $this->dropColumn('{{%country}}','continent_id');
    }
}
