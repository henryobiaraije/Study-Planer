<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateCardsTable extends AbstractMigration {
	public function change(): void {

		// if ( ! $this->schema_builder->hasTable( SP_TABLE_CARDS ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_CARDS,
		//				function ( Blueprint $table ) {
		//					$table->id();
		//					// $table->foreignId('card_group_id')->references('id')->on(SP_TABLE_CARD_GROUPS);
		//					$table->foreignId( 'card_group_id' )->constrained( SP_TABLE_CARD_GROUPS )->cascadeOnDelete()->cascadeOnUpdate();
		//					$table->string( 'hash' );
		//					$table->string( 'c_number' );
		//					$table->text( 'question' );
		//					$table->text( 'answer' );
		//					$table->integer( 'x_position' )->nullable();
		//					$table->integer( 'y_position' )->nullable();
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_CARDS );
		if ( $exists ) {
			return;
		}

		$table = $this->table( SP_TABLE_CARDS );
		$table
			->addColumn( 'card_group_id', 'integer' )
			->addColumn( 'hash', 'string' )
			->addColumn( 'c_number', 'string' )
			->addColumn( 'question', 'text' )
			->addColumn( 'answer', 'text' )
			->addColumn( 'x_position', 'integer', [ 'null' => true ] )
			->addColumn( 'y_position', 'integer', [ 'null' => true ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			->addForeignKey( 'card_group_id', SP_TABLE_CARD_GROUPS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->create();
	}
}
