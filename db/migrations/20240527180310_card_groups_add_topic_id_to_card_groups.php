<?php /** @noinspection ForgottenDebugOutputInspection */

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CardGroupsAddTopicIdToCardGroups extends AbstractMigration {
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
		global $wpdb;
		$table_name  = SP_TABLE_CARD_GROUPS;
		$column_name = 'topic_id';

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
		// SQL statement to create the table
		$sql = "
			ALTER TABLE {$table_name}
			ADD COLUMN {$column_name} BIGINT(20) UNSIGNED DEFAULT 0,
    		ADD FOREIGN KEY ({$column_name}) REFERENCES {$tb_collections}(id) ON DELETE SET NULL ON UPDATE CASCADE
    	";

		// Execute the query and log any errors
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// Check for errors
		if ( ! empty( $wpdb->last_error ) ) {
			// Log the error
			error_log( 'Error creating table ' . $table_name . ': ' . $wpdb->last_error );
		}
	}

}
