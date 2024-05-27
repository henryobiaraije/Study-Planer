<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class AddTopicIdToStudy extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * Write your reversible migrations using this method.
	 *
	 * More information on writing migrations is available here:
	 * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
	 *
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function change(): void {
		// if ( ! $this->schema_builder->hasColumn( SP_TABLE_STUDY, 'topic_id' ) ) {
		//			Capsule::schema()->table(
		//				SP_TABLE_STUDY,
		//				function ( Blueprint $table ) {
		//					$table
		//						->foreignId( 'topic_id' )
		//						->constrained( SP_TABLE_TOPICS )
		//						->nullOnDelete();
		//				}
		//			);
		//		}
		require_once __DIR__ . '/table-definitions.php';
		$exist = $this->hasTable( SP_TABLE_STUDY );
		if ( ! $exist ) {
			return;
		}
		$table = $this->table( SP_TABLE_STUDY );
		if ( ! $table->hasColumn( 'topic_id' ) ) {
			$table
				->addColumn( 'topic_id', 'integer', [ 'null' => true ] )
				->addForeignKey( 'topic_id', SP_TABLE_TOPICS, 'id', [ 'delete' => 'SET_NULL' , 'update' => 'CASCADE' ] )
				->update();
		}

	}
}
