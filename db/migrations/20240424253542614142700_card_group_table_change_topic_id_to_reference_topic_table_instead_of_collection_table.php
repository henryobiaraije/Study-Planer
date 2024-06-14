<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CardGroupTableChangeTopicIdToReferenceTopicTableInsteadOfCollectionTable extends AbstractMigration {
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
		global $wpdb;
		$wpdb->show_errors();
		require_once __DIR__ . '/table-definitions.php';
		$table_name = SP_TABLE_CARD_GROUPS;
		if ( ! $this->hasTable( $table_name ) ) {
			return;
		}

		$exists = $this->table( $table_name )->hasColumn( 'topic_id' );
		if ( ! $exists ) {
			return;
		}

		// Check if foreign key exists.
//		$foreign_key_exists = $this->table( $table_name )->hasForeignKey( 'topic_id' );
//		if ( $foreign_key_exists ) {
//			$this->table( $table_name )->dropForeignKey( 'topic_id' );
//		}

		$constraint_name = $this->get_constraint_name( 'topic_id', $table_name );
		if ( $constraint_name ) {
			$this->drop_constraint( $constraint_name, $table_name );
		}

		for ( $i = 0; $i < 10; $i ++ ) {
			$constraint_name = $this->get_constraint_name( 'topic_id', $table_name );
			if ( $constraint_name ) {
				$this->drop_constraint( $constraint_name, $table_name );
			}
		}

		// Make the column unsigned.
		$this->table( $table_name )
		     ->changeColumn( 'topic_id', 'biginteger', [ 'signed' => false, 'default' => 0 ] )
		     ->update();

		// Add foreign key to topic table.
		$this->table( $table_name )
		     ->addForeignKey( 'topic_id', SP_TABLE_TOPICS, 'id', [ 'delete' => 'SET NULL', 'update' => 'CASCADE' ] )
		     ->update();

		$wpdb->hide_errors();
	}

	function get_constraint_name( $column_name, $table_name ) {
		global $wpdb;

		$query = $wpdb->prepare(
			'SELECT CONSTRAINT_NAME
         FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_NAME = %s
         AND COLUMN_NAME = %s
         AND TABLE_SCHEMA = %s',
			$table_name, $column_name, DB_NAME
		);

		return $wpdb->get_var( $query );
	}

	function drop_constraint( $constraint_name, $table_name ) {
		global $wpdb;

		$query = sprintf(
			"ALTER TABLE %s DROP FOREIGN KEY %s",
			$table_name, '`' . $constraint_name . '`'
		);

		$wpdb->query( $query );

		$error = $wpdb->last_error;

		if ( $error ) {
			error_log( $error );
		}

	}
}