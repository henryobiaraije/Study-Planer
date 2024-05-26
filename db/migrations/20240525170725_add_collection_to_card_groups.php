<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class AddCollectionToCardGroups extends AbstractMigration {
	public function change(): void {
		// if ( ! $this->schema_builder->hasColumn( SP_TABLE_CARD_GROUPS, 'collection_id' ) ) {
		//			Capsule::schema()->table(
		//				SP_TABLE_CARD_GROUPS,
		//				function ( Blueprint $table ) {
		//					// $table
		//					// ->integer( 'collection_id' )
		//					// ->after( 'card_type' )
		//					// ->nullable();
		//					$table
		//						->foreignId( 'collection_id' )
		//						->constrained( SP_TABLE_COLLECTIONS )
		//						->cascadeOnDelete()
		//						->cascadeOnUpdate()
		//						->nullOnDelete();
		//				}
		//			);
		$exists = $this->hasTable( SP_TABLE_CARD_GROUPS );
		if ( ! $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_CARD_GROUPS );
		$table
			->addColumn( 'collection_id', 'integer' , [ 'null' => true ])
			->addForeignKey( 'collection_id', SP_TABLE_COLLECTIONS, 'id', [
				'delete' => 'SET_NULL',
				'update' => 'CASCADE'
			] )
			->update();
	}
}
