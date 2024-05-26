<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateAnswerLog extends AbstractMigration {
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
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_ANSWER_LOG ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_ANSWER_LOG,
		//				function ( Blueprint $table ) {
		//					global $wpdb;
		//					$table->id();
		//					$table->foreignId( 'study_id' )->constrained( SP_TABLE_STUDY )->cascadeOnDelete()->cascadeOnUpdate()->comment( 'The study id' );
		//					$table->bigInteger( 'card_id' )->index()->unsigned()->nullable();
		//					$table->foreign( 'card_id' )->references( 'id' )->on( SP_TABLE_CARDS )->cascadeOnUpdate()->nullOnDelete();
		//					$table->dateTime( 'last_card_updated_at' )->nullable();
		//					$table->text( 'accepted_change_comment' )->nullable();
		//					$table->text( 'question' )->nullable();
		//					$table->text( 'answer' )->nullable();
		//					$table->timestamps();
		//				}
		//			);
		//		}
		$exist = $this->hasTable( SP_TABLE_ANSWER_LOG );
		if ( $exist ) {
			return;
		}
		$table = $this->table( SP_TABLE_ANSWER_LOG);
		$table
			->addColumn( 'study_id', 'biginteger' )
			->addColumn( 'card_id', 'biginteger', [ 'null' => true ] )
			->addColumn( 'last_card_updated_at', 'timestamp', [ 'null' => true ] )
			->addColumn( 'accepted_change_comment', 'text', [ 'null' => true ] )
			->addColumn( 'question', 'text', [ 'null' => true ] )
			->addColumn( 'answer', 'text', [ 'null' => true ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			->addForeignKey( 'study_id', SP_TABLE_STUDY, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->addForeignKey( 'card_id', SP_TABLE_CARDS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->create();
	}
}
