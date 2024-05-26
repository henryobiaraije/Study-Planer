<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateTaggables extends AbstractMigration {
	public function change(): void {
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_TAGGABLES ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_TAGGABLES,
		//				function ( Blueprint $table ) {
		//					$table->id();
		//					// $table->foreignId('tag_id')->constrained(SP_TABLE_TAGS)->onDelete('cascade');
		//					$table->foreignId( 'tag_id' )->constrained( SP_TABLE_TAGS )->cascadeOnDelete()->cascadeOnUpdate();
		//					$table->string( 'taggable_id' );
		//					$table->string( 'taggable_type' );
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_TAGGABLES );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_TAGGABLES );
		$table
			->addColumn( 'tag_id', 'integer' )
			->addColumn( 'taggable_id', 'string' )
			->addColumn( 'taggable_type', 'string' )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addIndex( [ 'tag_id', 'taggable_id', 'taggable_type' ], [ 'unique' => true ] )
			->addForeignKey( 'tag_id', SP_TABLE_TAGS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->create();
	}
}
