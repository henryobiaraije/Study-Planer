<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateTableTopics extends AbstractMigration {
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
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_TOPICS ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_TOPICS,
		//				function ( Blueprint $table ) {
		//					$table->id();
		//					$table->string( 'name' )->unique();
		//					$table->foreignId( 'deck_id' )->constrained( SP_TABLE_DECKS )->cascadeOnDelete()->cascadeOnUpdate();
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_TOPICS );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_TOPICS );
		$table
			->addColumn( 'name', 'string', [ 'limit' => 255 ] )
			->addColumn( 'deck_id', 'biginteger' )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			->addForeignKey( 'deck_id', SP_TABLE_DECKS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->addIndex( [ 'name' ], [ 'unique' => true ] )
			->create();

	}
}
