<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class AddCardLastUpdatedAtToAnswered extends AbstractMigration {
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
		// 		if ( ! $this->schema_builder->hasColumn( SP_TABLE_ANSWERED, 'card_last_updated_at' ) ) {
		//			Capsule::schema()->table(
		//				SP_TABLE_ANSWERED,
		//				function ( Blueprint $table ) {
		//					$table->dateTime( 'card_last_updated_at' )->after( 'updated_at' )->nullable();
		//				}
		//			);
		//		}
		require_once __DIR__ . '/table-definitions.php';
		$exists = $this->hasTable( SP_TABLE_ANSWERED );
		if ( ! $exists ) {
			return;
		}

		$table = $this->table( SP_TABLE_ANSWERED, [ 'id' => false, 'primary_key' => 'id' ] );
		if ( $table->hasColumn( 'card_last_updated_at' ) ) {
			$table
				->addColumn( 'card_last_updated_at', 'timestamp', [ 'default' => 'CURRENT_TIMESTAMP' ] )
				->update();
		}
	}
}
