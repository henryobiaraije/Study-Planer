<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateDeckGroups extends AbstractMigration {

	public function change(): void {
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_DECK_GROUPS ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_DECK_GROUPS,
		//				function ( Blueprint $table ) {
		//					$table->id();
		//					$table->string( 'name' )->unique();
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}

		$exists = $this->hasTable( 'deck_groups' );
		if ( $exists ) {
			return;
		}
		$table = $this->table('deck_groups' );
		$table
			->addColumn( 'name', 'string', [ 'limit' => 255 ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addIndex( [ 'name' ], [ 'unique' => true ] )
			->create();
	}
}

