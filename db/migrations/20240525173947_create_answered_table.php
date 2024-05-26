<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateAnsweredTable extends AbstractMigration {
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
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_ANSWERED ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_ANSWERED,
		//				function ( Blueprint $table ) {
		//					global $wpdb;
		//					$table->id();
		//					// $table->foreignId('study_id')->references('id')->on(SP_TABLE_STUDY);
		//					$table->foreignId( 'study_id' )->constrained( SP_TABLE_STUDY )->cascadeOnDelete()->cascadeOnUpdate()->comment( 'The study id' );
		//					$table->bigInteger( 'card_id' )->index()->unsigned()->nullable();
		//					$table->foreign( 'card_id' )->references( 'id' )->on( SP_TABLE_CARDS )->cascadeOnUpdate()->nullOnDelete();
		//					// $table->text( 'answer' );
		//					$table->string( 'grade' )->nullable();
		//					$table->integer( 'ease_factor' )->nullable();
		//					$table->dateTime( 'next_due_at' )->nullable();
		//					$table->boolean( 'next_due_answered' )->nullable();
		//					$table->dateTime( 'started_at' )->nullable();
		//					$table->boolean( 'answered_as_new' )->nullable();
		//					$table->boolean( 'answered_as_revised' )->nullable();
		//					$table->integer( 'next_interval' )->nullable();
		//					$table->dateTime( 'rejected_at' )->nullable();
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}

		$exist = $this->hasTable( SP_TABLE_ANSWERED );
		if ( $exist ) {
			return;
		}
		$table = $this->table( SP_TABLE_ANSWERED );
		$table
			->addColumn( 'study_id', 'biginteger', [ 'null' => false ] )
			->addColumn( 'card_id', 'biginteger', [ 'null' => true ] )
			->addColumn( 'grade', 'string', [ 'null' => true ] )
			->addColumn( 'ease_factor', 'integer', [ 'null' => true ] )
			->addColumn( 'next_due_at', 'datetime', [ 'null' => true ] )
			->addColumn( 'next_due_answered', 'boolean', [ 'null' => true ] )
			->addColumn( 'started_at', 'datetime', [ 'null' => true ] )
			->addColumn( 'answered_as_new', 'boolean', [ 'null' => true ] )
			->addColumn( 'answered_as_revised', 'boolean', [ 'null' => true ] )
			->addColumn( 'next_interval', 'integer', [ 'null' => true ] )
			->addColumn( 'rejected_at', 'datetime', [ 'null' => true ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addForeignKey( 'study_id', SP_TABLE_ANSWERED, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->addForeignKey( 'card_id', SP_TABLE_CARDS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->create();
	}
}
