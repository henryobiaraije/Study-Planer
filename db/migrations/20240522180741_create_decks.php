<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateDecks extends AbstractMigration {
	public function change(): void {
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_DECKS ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_DECKS,
		//				function ( Blueprint $table ) {
		//					$table->id();
		//					$table->string( 'name' )->unique();
		//					// $table->foreignId('deck_group_id')->references('id')->on(SP_TABLE_DECK_GROUPS);
		//					$table->foreignId( 'deck_group_id' )->constrained( SP_TABLE_DECK_GROUPS )->cascadeOnDelete()->cascadeOnUpdate();
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_DECKS );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_DECKS );
		$table
			->addColumn( 'name', 'string', [ 'limit' => 255 ] )
			->addColumn( 'deck_group_id', 'integer' )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addIndex( [ 'name' ], [ 'unique' => true ] )
			->addForeignKey( 'deck_group_id', SP_TABLE_DECK_GROUPS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->create();
	}
}
