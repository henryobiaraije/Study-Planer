<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class AnsweredAddCardLastUpdatedAt extends AbstractMigration {
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
		require_once __DIR__ . '/table-definitions.php';
		// 		if ( ! $this->schema_builder->hasColumn( SP_TABLE_ANSWERED, 'card_last_updated_at' ) ) {
		//			Capsule::schema()->table(
		//				SP_TABLE_ANSWERED,
		//				function ( Blueprint $table ) {
		//					$table->dateTime( 'card_last_updated_at' )->after( 'updated_at' )->nullable();
		//				}
		//			);
		//		}
		$table_name  = SP_TABLE_ANSWERED;
		$column_name = 'card_last_updated_at';
		$tb_exists   = $this->hasTable( $table_name );
		if ( ! $tb_exists ) {
			return;
		}

		$col_exists = $this->table( $table_name )->hasColumn( $column_name );
		if ( $col_exists ) {
			return;
		}

		$this->table( $table_name )
		     ->addColumn( $column_name, 'datetime', [ 'null' => true, 'after' => 'updated_at' ] )
		     ->update();
	}
}
