<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateTableCollections extends AbstractMigration {
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
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_COLLECTIONS ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_COLLECTIONS,
		//				function ( Blueprint $table ) {
		//					$table->id();
		//					$table->string( 'name' )->unique();
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_COLLECTIONS );
		if ( $exists ) {
			return;
		}

		$table = $this->table( SP_TABLE_COLLECTIONS );
		$table
			->addColumn( 'name', 'string', [ 'limit' => 255 ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			->addIndex( [ 'name' ], [ 'unique' => true ] )
			->create();
	}
}
