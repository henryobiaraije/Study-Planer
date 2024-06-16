<?php /** @noinspection ALL */

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class StudyModifyTopicIdAndMakeItMullable extends AbstractMigration {
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
		require_once __DIR__ . '/table-definitions.php';
		if ( ! $this->hasTable( SP_TABLE_STUDY ) ) {
			return;
		}

		$exists = $this->table( SP_TABLE_STUDY )->hasColumn( 'topic_id' );
		if ( ! $exists ) {
			return;
		}

		// Drop the foreign key constraint.
		$constraint_name = $this->get_constraint_name( 'topic_id', SP_TABLE_STUDY );
		if ( $constraint_name ) {
			$this->drop_constraint( $constraint_name, SP_TABLE_STUDY );
		}

		// Make the column nullable.
		$tb_study = SP_TABLE_STUDY;
		$sql      = "
				ALTER TABLE {$tb_study} MODIFY deck_id BIGINT UNSIGNED NULL;
		";

		$wpdb->show_errors();
		$wpdb->query( $sql );
		$last_error = $wpdb->last_error;


		// Add foreign key to topic table.
		$add = $this
			->table( SP_TABLE_STUDY )
			->addForeignKey( 'topic_id', SP_TABLE_TOPICS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->update();

		if ( $add ) {

		}
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
			'ALTER TABLE %s DROP FOREIGN KEY %s',
			$table_name, '`' . $constraint_name . '`'
		);

		$wpdb->query( $query );

		$error = $wpdb->last_error;

		if ( $error ) {
			error_log( $error );
		}

	}

}