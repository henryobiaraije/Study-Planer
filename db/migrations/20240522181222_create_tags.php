<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateTags extends AbstractMigration {
	public function change(): void {
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_TAGS ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_TAGS,
		//				function ( Blueprint $table ) {
		//					$table->id();
		//					$table->string( 'name' )->unique();
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_TAGS );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_TAGS );
		$table
			->addColumn( 'name', 'string', [ 'limit' => 255 ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addIndex( [ 'name' ], [ 'unique' => true ] )
			->create();

	}
}
