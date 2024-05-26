<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateTableStudy extends AbstractMigration {
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
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_STUDY ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_STUDY,
		//				function ( Blueprint $table ) {
		//					global $wpdb;
		//					$table->id();
		//					$table->foreignId( 'deck_id' )->constrained( SP_TABLE_DECKS )->cascadeOnUpdate()->cascadeOnDelete();
		//					$table->foreignId( 'user_id' )->references( 'ID' )->on( ( $wpdb->prefix . 'users' ) )->cascadeOnUpdate()->cascadeOnDelete();
		//					$table->boolean( 'all_tags' );
		//					$table->integer( 'no_to_revise' );
		//					$table->integer( 'no_of_new' );
		//					$table->integer( 'no_on_hold' );
		//					$table->boolean( 'revise_all' );
		//					$table->boolean( 'study_all_new' );
		//					$table->boolean( 'study_all_on_hold' );
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_STUDY );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_STUDY, [ 'id' => false, 'primary_key' => 'id' ] );
		$table->addColumn( 'id', 'biginteger', [ 'identity' => true ] )
		      ->addColumn( 'deck_id', 'biginteger' )
		      ->addColumn( 'user_id', 'biginteger' )
		      ->addColumn( 'all_tags', 'boolean' )
		      ->addColumn( 'no_to_revise', 'integer' )
		      ->addColumn( 'no_of_new', 'integer' )
		      ->addColumn( 'no_on_hold', 'integer' )
		      ->addColumn( 'revise_all', 'boolean' )
		      ->addColumn( 'study_all_new', 'boolean' )
		      ->addColumn( 'study_all_on_hold', 'boolean' )
		      ->addTimestamps()
		      ->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
		      ->addForeignKey( 'deck_id', SP_TABLE_DECKS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
		      ->addForeignKey( 'user_id', SP_TABLE_USERS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
		      ->create();
	}
}
