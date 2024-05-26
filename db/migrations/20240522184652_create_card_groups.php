<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateCardGroups extends AbstractMigration {
	public function change(): void {
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_CARD_GROUPS ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_CARD_GROUPS,
		//				function ( Blueprint $table ) {
		//					global $wpdb;
		//					$table->id();
		//					$table->foreignId( 'deck_id' )->constrained( SP_TABLE_DECKS )->cascadeOnDelete()->cascadeOnUpdate();
		//					// $table->foreignId('bg_image_id')->references('ID')->on($wpdb->prefix.'posts')->nullOnDelete()->cascadeOnUpdate();
		//					$table->bigInteger( 'bg_image_id' );
		//					$table->text( 'name' );
		//					$table->text( 'whole_question' );
		//					$table->string( 'card_type' );
		//					$table->dateTime( 'scheduled_at' );
		//					$table->boolean( 'reverse' );
		//					$table->string( 'image_type' )->nullable()->comment( 'The image Display type for image cards. e.g. hide_one_show_one' );
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_CARD_GROUPS );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_CARD_GROUPS );
		$table
			->addColumn( 'deck_id', 'integer' )
			->addColumn( 'bg_image_id', 'biginteger' )
			->addColumn( 'name', 'text' )
			->addColumn( 'whole_question', 'text' )
			->addColumn( 'card_type', 'string' )
			->addColumn( 'scheduled_at', 'datetime' )
			->addColumn( 'reverse', 'boolean' )
			->addColumn( 'image_type', 'string', [ 'null'    => true,
			                                       'comment' => "The image Display type for image cards. e.g. hide_one_show_one"
			] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addForeignKey( 'deck_id', SP_TABLE_DECKS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->addIndex( 'card_type' )
			->create();
	}
}
