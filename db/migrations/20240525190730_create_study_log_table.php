<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateStudyLogTable extends AbstractMigration {
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
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_STUDY_LOG ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_STUDY_LOG,
		//				function ( Blueprint $table ) {
		//					global $wpdb;
		//					$table->id();
		//					// $table->foreignId('study_id')->references('id')->on(SP_TABLE_STUDY);
		//					$table->foreignId( 'study_id' )->constrained( SP_TABLE_STUDY )->cascadeOnDelete()->cascadeOnUpdate();
		//					// $table->foreignId('card_id')->references('id')->on(SP_TABLE_CARDS);
		//					$table->foreignId( 'card_id' )->constrained( SP_TABLE_CARDS )->cascadeOnUpdate()->cascadeOnDelete();
		//					// $table->foreignId('answered_id')->references('id')->on(SP_TABLE_ANSWERED);
		//					// $table->foreignId('answered_id')->nullable()->constrained(SP_TABLE_ANSWERED)->cascadeOnDelete()->cascadeOnDelete();
		//					$table->string( 'action' );
		//					$table->dateTime( 'created_at' );
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_STUDY_LOG );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_STUDY_LOG );
		$table
			->addColumn( 'study_id', 'biginteger' )
			->addColumn( 'card_id', 'biginteger' )
			->addColumn( 'action', 'string', [ "comment" => "The action taken. Can be 'start' or 'stop'. 'start' means the card was started to be studied(opened in study area) and 'stop' means the card was stopped(an answer is submitted) being studied." ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			//
			->addForeignKey( 'study_id', SP_TABLE_STUDY, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->addForeignKey( 'card_id', SP_TABLE_CARDS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->create();
	}
}
