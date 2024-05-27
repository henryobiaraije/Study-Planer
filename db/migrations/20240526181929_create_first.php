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
		$this->create_decks();
		$this->create_topics();
		$this->create_collections();
		$this->create_tags();
		$this->create_taggable();
		$this->create_taggable_excluded();
		$this->create_card_group();
		$this->create_cards();
		$this->create_study();
		$this->create_study_log();
		$this->create_user_cards();
		$this->create_answer();
		$this->create_answer_log();
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
		global $wpdb;
		$table_name = SP_TABLE_DECKS;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		$tb_deck_groups = SP_TABLE_DECK_GROUPS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            deck_group_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY name (name),
            FOREIGN KEY (deck_group_id) REFERENCES ${tb_deck_groups}(id) ON DELETE CASCADE ON UPDATE CASCADE
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

	public function create_topics(): void {
		global $wpdb;
		$table_name = SP_TABLE_TOPICS;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		$tb_decks = SP_TABLE_DECKS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            deck_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY name (name),
            FOREIGN KEY (deck_id) REFERENCES {$tb_decks}(id) ON DELETE CASCADE ON UPDATE CASCADE
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

	public function create_collections(): void {
		global $wpdb;
		$table_name = SP_TABLE_COLLECTIONS;

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

	public function create_tags(): void {
		global $wpdb;
		$table_name = SP_TABLE_TAGS;

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

	public function create_taggable(): void {
		global $wpdb;
		$table_name = SP_TABLE_TAGGABLES;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		$tb_tags = SP_TABLE_TAGS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            tag_id BIGINT(20) UNSIGNED NOT NULL,
            taggable_id VARCHAR(255) NOT NULL,
            taggable_type VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY taggable_unique (tag_id, taggable_id, taggable_type),
            FOREIGN KEY (tag_id) REFERENCES {$tb_tags}(id) ON DELETE CASCADE ON UPDATE CASCADE
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

	public function create_taggable_excluded(): void {
		global $wpdb;
		$table_name = SP_TABLE_TAGGABLES_EXCLUDED;
		$tb_tags    = SP_TABLE_TAGS;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}
		$tb_tags = SP_TABLE_TAGS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				tag_id BIGINT(20) UNSIGNED NOT NULL,
				taggable_id VARCHAR(255) NOT NULL,
				taggable_type VARCHAR(255) NOT NULL,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at DATETIME NULL,
				PRIMARY KEY (id),
				UNIQUE KEY taggable_unique (tag_id, taggable_id, taggable_type),
				FOREIGN KEY (tag_id) REFERENCES {$tb_tags}(id) ON DELETE CASCADE ON UPDATE CASCADE
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

	public function create_card_group(): void {
		global $wpdb;
		$table_name = SP_TABLE_CARD_GROUPS;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		$tb_decks = SP_TABLE_DECKS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				deck_id BIGINT(20) UNSIGNED NOT NULL,
				bg_image_id BIGINT(20) UNSIGNED,
				name TEXT NOT NULL,
				whole_question TEXT NOT NULL,
				card_type VARCHAR(255) NOT NULL,
				scheduled_at DATETIME,
				reverse BOOLEAN,
				image_type VARCHAR(255) NULL COMMENT 'The image Display type for image cards. e.g. hide_one_show_one',
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at DATETIME NULL,
				PRIMARY KEY (id),
				FOREIGN KEY (deck_id) REFERENCES {$tb_decks}(id) ON DELETE CASCADE ON UPDATE CASCADE,
				INDEX (card_type)
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

	public function create_cards(): void {
		global $wpdb;
		$table_name = SP_TABLE_CARDS;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		$tb_card_groups = SP_TABLE_CARD_GROUPS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				card_group_id BIGINT(20) UNSIGNED NOT NULL,
				hash VARCHAR(255) NOT NULL,
				c_number VARCHAR(255) NOT NULL,
				question TEXT NOT NULL,
				answer TEXT NOT NULL,
				x_position INTEGER NULL,
				y_position INTEGER NULL,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at DATETIME NULL,
				PRIMARY KEY (id),
				FOREIGN KEY (card_group_id) REFERENCES {$tb_card_groups}(id) ON DELETE CASCADE ON UPDATE CASCADE
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

	public function create_study(): void {
		global $wpdb;
		$table_name = SP_TABLE_STUDY;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		$tb_decks = SP_TABLE_DECKS;
		$tb_users = SP_TABLE_USERS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				deck_id BIGINT(20) UNSIGNED NOT NULL,
				user_id BIGINT(20) UNSIGNED NOT NULL,
				all_tags BOOLEAN,
				no_to_revise INTEGER,
				no_of_new INTEGER,
				no_on_hold INTEGER,
				revise_all BOOLEAN,
				study_all_new BOOLEAN,
				study_all_on_hold BOOLEAN,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at DATETIME NULL,
				PRIMARY KEY (id),
				FOREIGN KEY (deck_id) REFERENCES {$tb_decks}(id) ON DELETE CASCADE ON UPDATE CASCADE,
				FOREIGN KEY (user_id) REFERENCES {$tb_users}(id) ON DELETE CASCADE ON UPDATE CASCADE
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

	public function create_study_log(): void {
		global $wpdb;
		$table_name = SP_TABLE_STUDY_LOG;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		$tb_study = SP_TABLE_STUDY;
		$tb_cards = SP_TABLE_CARDS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            study_id BIGINT(20) UNSIGNED NOT NULL,
            card_id BIGINT(20) UNSIGNED NOT NULL,
            action VARCHAR(255) NOT NULL COMMENT 'The action taken. Can be start or stop. start means the card was started to be studied (opened in study area) and stop means the card was stopped (an answer is submitted) being studied.',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (study_id) REFERENCES {$tb_study}(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (card_id) REFERENCES {$tb_cards}(id) ON DELETE CASCADE ON UPDATE CASCADE
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

	public function create_user_cards(): void {
		global $wpdb;
		$table_name = SP_TABLE_USER_CARDS;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		$tb_users       = SP_TABLE_USERS;
		$tb_card_groups = SP_TABLE_CARD_GROUPS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            card_group_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (user_id) REFERENCES {$tb_users}(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (card_group_id) REFERENCES {$tb_card_groups}(id) ON DELETE CASCADE ON UPDATE CASCADE
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

	public function create_answer(): void {
		global $wpdb;
		$table_name =  SP_TABLE_ANSWERED;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		$tb_study = SP_TABLE_STUDY;
		$tb_cards = SP_TABLE_CARDS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				study_id BIGINT(20) UNSIGNED NOT NULL,
				card_id BIGINT(20) UNSIGNED,
				grade VARCHAR(255),
				ease_factor INTEGER,
				next_due_at DATETIME,
				next_due_answered BOOLEAN,
				started_at DATETIME,
				answered_as_new BOOLEAN,
				answered_as_revised BOOLEAN,
				next_interval INTEGER,
				rejected_at DATETIME,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at DATETIME NULL,
				PRIMARY KEY (id),
				FOREIGN KEY (study_id) REFERENCES {$tb_study}(id) ON DELETE CASCADE ON UPDATE CASCADE,
				FOREIGN KEY (card_id) REFERENCES {$tb_cards}(id) ON DELETE CASCADE ON UPDATE CASCADE
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

	public function create_answer_log(): void {
		global $wpdb;
		$table_name = SP_TABLE_ANSWER_LOG;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			return;
		}

		$tb_study = SP_TABLE_STUDY;
		$tb_cards = SP_TABLE_CARDS;
		// SQL statement to create the table
		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            study_id BIGINT(20) UNSIGNED NOT NULL,
            card_id BIGINT(20) UNSIGNED,
            last_card_updated_at DATETIME,
            accepted_change_comment TEXT,
            question TEXT,
            answer TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (study_id) REFERENCES {$tb_study}(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (card_id) REFERENCES {$tb_cards}(id) ON DELETE CASCADE ON UPDATE CASCADE
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
}

