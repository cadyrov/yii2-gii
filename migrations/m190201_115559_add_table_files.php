<?php

use yii\db\Migration;

/**
 * Class m190201_115659_add_table_files
 */
class m190201_115659_add_table_contact extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->createTable('files', [
			'file_id' => $this->primaryKey(),
			'name' => $this->string()->notNull(),
			'ext' => $this->string()->notNull(),
			'owner_id' => $this->integer(),
			'type_id' => $this->integer(),
			'user_id' => $this->integer(),
			'add_date' => $this->dateTime(),
		]);
        $this->createIndex ('files_owner_id_foreign_id', 'files', 'owner_id');
        $this->createIndex ('files_type_id_foreign_id', 'files', 'type_id');
		$this->createIndex ('files_user_id_foreign_id', 'files', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropTable('files');
    }


}
