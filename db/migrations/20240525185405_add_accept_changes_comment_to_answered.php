<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class AddAcceptChangesCommentToAnswered extends AbstractMigration {
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
		// if ( ! $this->schema_builder->hasColumn( SP_TABLE_ANSWERED, 'accept_changes_comment' ) ) {
		//			Capsule::schema()->table(
		//				SP_TABLE_ANSWERED,
		//				function ( Blueprint $table ) {
		//					$table->text( 'accept_changes_comment' )->after( 'card_last_updated_at' )->nullable();
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_ANSWERED );
		if ( ! $exists ) {
			return;
		}

		$table = $this->table( SP_TABLE_ANSWERED, [ 'id' => false, 'primary_key' => 'id' ] );
		if ( $table->hasColumn( 'accept_changes_comment' ) ) {
			$table
				->addColumn( 'accept_changes_comment', 'string', array(
					'after'   => 'card_last_updated_at',
					'comment' => 'The comment the user left when they accepted changes made to a card they answered before'
				) )
				->update();
		}
	}
}
