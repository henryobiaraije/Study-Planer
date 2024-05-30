<?php /** @noinspection ForgottenDebugOutputInspection */

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class StudyAddActiveToStudy extends AbstractMigration {

	public function change(): void {
		require_once __DIR__ . '/table-definitions.php';
		$tb_exists = $this->hasTable( SP_TABLE_STUDY );
		if ( ! $tb_exists ) {
			return;
		}

		$col_exists = $this->table( SP_TABLE_STUDY )->hasColumn( 'active3' );
		if ( $col_exists ) {
			return;
		}

		$this->table( SP_TABLE_STUDY )
		     ->addColumn( 'active3', 'integer', [ 'default' => 1 ] )
		     ->update();
	}
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
	public function change3(): void {
		global $wpdb;
		$table_name  = SP_TABLE_STUDY;
		$column_name = 'active3';

		// Check if the table exists
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		// check if the column exists
		$exists = $wpdb->get_results( "SHOW COLUMNS FROM $table_name LIKE '$column_name'" );
		if ( ! empty( $exists ) ) {
			return;
		}

		$tb_study       = SP_TABLE_STUDY;
		$tb_cards       = SP_TABLE_CARDS;
		$tb_collections = SP_TABLE_COLLECTIONS;
		// SQL statement to create the table.
//		$sql = "
//			ALTER TABLE {$table_name}
//			ADD COLUMN {$column_name} TINYINT DEFAULT 1 COMMENT 'Whether the study is active or not.'  AFTER `no_on_hold`
//    	";
		$sql = "
			ALTER TABLE {$table_name} 
			ADD COLUMN {$column_name} INT NOT NULL DEFAULT 1
    	";

		// Execute the query and log any errors
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

//		$status = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
//		$exists = $wpdb->get_results( "SHOW COLUMNS FROM $table_name LIKE '$column_name'" );
//		$wpdb->print_error();
//		$wpdb->show_errors();
		// 'SHOW ENGINE INNODB STATUS'
//		$inno_status =  $wpdb->get_results( "SHOW ENGINE INNODB STATUS" );
//		$current_db_name = $wpdb->dbname;
//		$columns = $wpdb->get_results( "SHOW COLUMNS FROM $table_name" );

		// Check for errors
		if ( ! empty( $wpdb->last_error ) ) {
			// Log the error
			error_log( 'Error adding active to study ' . $table_name . ': ' . $wpdb->last_error );
		}
	}
}
