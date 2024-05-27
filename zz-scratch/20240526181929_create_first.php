<?php /** @noinspection ForgottenDebugOutputInspection */

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateFirst extends AbstractMigration {
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
		$this->create_deck_groups();
//		$this->create_decks();
//		$this->create_topics();
//		$this->create_collections();
//		$this->create_tags();
//		$this->create_taggable();
//		$this->create_taggable_excluded();
//		$this->create_card_group();
//		$this->create_cards();
//		$this->create_study();
//		$this->create_study_log();
//		$this->create_user_cards();
//		$this->create_answer();
//		$this->create_answer_log();
	}

	public function create_deck_groups(): void {
		global $wpdb;
		$table_name = SP_TABLE_DECK_GROUPS;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at DATETIME NULL,
				PRIMARY KEY (id),
				UNIQUE KEY name (name)
			) {$wpdb->get_charset_collate()};
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

	public function create_decks(): void {
		$exists = $this->hasTable( SP_TABLE_DECKS );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_DECKS );
		$table
			->addColumn( 'name', 'string', [ 'limit' => 255 ] )
			->addColumn( 'deck_group_id', 'integer' )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addIndex( [ 'name' ], [ 'unique' => true ] )
			->addForeignKey( 'deck_group_id', SP_TABLE_DECK_GROUPS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->create();
	}

	public function create_topics(): void {
		$exists = $this->hasTable( SP_TABLE_TOPICS );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_TOPICS );
		$table
			->addColumn( 'name', 'string', [ 'limit' => 255 ] )
			->addColumn( 'deck_id', 'biginteger' )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			->addForeignKey( 'deck_id', SP_TABLE_DECKS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->addIndex( [ 'name' ], [ 'unique' => true ] )
			->create();
	}

	public function create_collections(): void {
		$exists = $this->hasTable( SP_TABLE_COLLECTIONS );
		if ( $exists ) {
			return;
		}

		$table = $this->table( SP_TABLE_COLLECTIONS );
		$table
			->addColumn( 'name', 'string', [ 'limit' => 255 ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			->addIndex( [ 'name' ], [ 'unique' => true ] )
			->create();
	}

	public function create_tags(): void {
		$exists = $this->hasTable( SP_TABLE_TAGS );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_TAGS );
		$table
			->addColumn( 'name', 'string', [ 'limit' => 255 ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addIndex( [ 'name' ], [ 'unique' => true ] )
			->create();
	}

	public function create_taggable(): void {
		$exists = $this->hasTable( SP_TABLE_TAGGABLES );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_TAGGABLES );
		$table
			->addColumn( 'tag_id', 'integer' )
			->addColumn( 'taggable_id', 'string' )
			->addColumn( 'taggable_type', 'string' )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addIndex( [ 'tag_id', 'taggable_id', 'taggable_type' ], [ 'unique' => true ] )
			->addForeignKey( 'tag_id', SP_TABLE_TAGS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->create();
	}

	public function create_taggable_excluded(): void {
		$exists = $this->hasTable( SP_TABLE_TAGGABLES_EXCLUDED );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_TAGGABLES_EXCLUDED );
		$table
			->addColumn( 'tag_id', 'integer' )
			->addColumn( 'taggable_id', 'string' )
			->addColumn( 'taggable_type', 'string' )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addIndex( [ 'tag_id', 'taggable_id', 'taggable_type' ], [ 'unique' => true ] )
			->addForeignKey( 'tag_id', SP_TABLE_TAGS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->create();
	}

	public function create_card_group(): void {
		$exists = $this->hasTable( SP_TABLE_CARD_GROUPS );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_CARD_GROUPS );
		$table
			->addColumn( 'deck_id', 'integer' )
			->addColumn( 'bg_image_id', 'biginteger' )
			->addColumn( 'name', 'text' )
			->addColumn( 'whole_question', 'text' )
			->addColumn( 'card_type', 'string' )
			->addColumn( 'scheduled_at', 'datetime' )
			->addColumn( 'reverse', 'boolean' )
			->addColumn( 'image_type', 'string', [
				'null'    => true,
				'comment' => 'The image Display type for image cards. e.g. hide_one_show_one'
			] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addForeignKey( 'deck_id', SP_TABLE_DECKS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->addIndex( 'card_type' )
			->create();
	}

	public function create_cards(): void {
		$exists = $this->hasTable( SP_TABLE_CARDS );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_CARDS );
		$table
			->addColumn( 'card_group_id', 'integer' )
			->addColumn( 'hash', 'string' )
			->addColumn( 'c_number', 'string' )
			->addColumn( 'question', 'text' )
			->addColumn( 'answer', 'text' )
			->addColumn( 'x_position', 'integer', [ 'null' => true ] )
			->addColumn( 'y_position', 'integer', [ 'null' => true ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			->addForeignKey( 'card_group_id', SP_TABLE_CARD_GROUPS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->create();
	}

	public function create_study(): void {
		$exists = $this->hasTable( SP_TABLE_STUDY );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_STUDY );
		$table
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

	public function create_study_log(): void {
		$exists = $this->hasTable( SP_TABLE_STUDY_LOG );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_STUDY_LOG );
		$table
			->addColumn( 'study_id', 'biginteger' )
			->addColumn( 'card_id', 'biginteger' )
			->addColumn( 'action', 'string', [ 'comment' => "The action taken. Can be 'start' or 'stop'. 'start' means the card was started to be studied(opened in study area) and 'stop' means the card was stopped(an answer is submitted) being studied." ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			->addForeignKey( 'study_id', SP_TABLE_STUDY, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->addForeignKey( 'card_id', SP_TABLE_CARDS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->create();
	}

	public function create_user_cards(): void {
		$exists = $this->hasTable( SP_TABLE_USER_CARDS );
		if ( $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_USER_CARDS );
		$table
			->addColumn( 'user_id', 'biginteger' )
			->addColumn( 'card_group_id', 'biginteger' )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			->addForeignKey( 'user_id', SP_TABLE_USERS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->addForeignKey( 'card_group_id', SP_TABLE_CARD_GROUPS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->create();
	}

	public function create_answer(): void {
		$exist = $this->hasTable( SP_TABLE_ANSWERED );
		if ( $exist ) {
			return;
		}
		$table = $this->table( SP_TABLE_ANSWERED );
		$table
			->addColumn( 'study_id', 'biginteger', [ 'null' => false ] )
			->addColumn( 'card_id', 'biginteger', [ 'null' => true ] )
			->addColumn( 'grade', 'string', [ 'null' => true ] )
			->addColumn( 'ease_factor', 'integer', [ 'null' => true ] )
			->addColumn( 'next_due_at', 'datetime', [ 'null' => true ] )
			->addColumn( 'next_due_answered', 'boolean', [ 'null' => true ] )
			->addColumn( 'started_at', 'datetime', [ 'null' => true ] )
			->addColumn( 'answered_as_new', 'boolean', [ 'null' => true ] )
			->addColumn( 'answered_as_revised', 'boolean', [ 'null' => true ] )
			->addColumn( 'next_interval', 'integer', [ 'null' => true ] )
			->addColumn( 'rejected_at', 'datetime', [ 'null' => true ] )
			->addTimestamps()
			->addColumn( 'deleted_at', 'datetime', [ 'null' => true ] )
			->addForeignKey( 'study_id', SP_TABLE_STUDY, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->addForeignKey( 'card_id', SP_TABLE_CARDS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->create();
	}

	public function create_answer_log(): void {
		$exist = $this->hasTable( SP_TABLE_ANSWER_LOG );
		if ( $exist ) {
			return;
		}
		$table = $this->table( SP_TABLE_ANSWER_LOG );
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
