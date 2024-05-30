<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class StudyAddDeckIdToStudy extends AbstractMigration {
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
		// if ( $this->schema_builder->hasColumn( SP_TABLE_STUDY, 'deck_id' ) ) {
		//			Capsule
		//				::schema()
		//				->table(
		//					SP_TABLE_STUDY,
		//					function ( Blueprint $table ) {
		//						$prefix = sp_get_db_prefix();
		//						if ( $this->foreign_key_exists( SP_TABLE_STUDY, 'deck_id' ) ) {
		//							$table->dropForeign( $prefix . 'study_deck_id_foreign' );
		//						}
		//						if ( $this->has_index( SP_TABLE_STUDY, 'deck_id' ) ) {
		//							$table->dropIndex( $prefix . 'study_deck_id_foreign' );
		//						}
		//					}
		//				);
		//		}

		$table_name  = SP_TABLE_STUDY;
		$column_name = 'deck_id';
		$tb_exists   = $this->hasTable( $table_name );
		if( ! $tb_exists ) {
			return;
		}

		$col_exists = $this->table( $table_name )->hasColumn( $column_name );
		if( $col_exists ) {
			return;
		}

		$this->table( $table_name )
		     ->addColumn( $column_name, 'biginteger', [ 'default' => 0 ] )
		     ->addForeignKey( $column_name, SP_TABLE_DECKS, 'id', [
			     'delete' => 'SET NULL',
			     'update' => 'CASCADE'
		     ] )
		     ->update();
	}
}
