<?php


namespace StudyPlannerPro\Db;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Model\CardGroup;
use Model\Deck;
use Model\DeckGroup;
use Model\Topic;
use StudyPlannerPro\Libs\Settings;

use function StudyPlannerPro\sp_get_db_prefix;

class Initialize_Db {
	private static $instance;
	private $capsule;
	private $schema_builder;

	public static function get_instance() {
		if ( self::$instance ) {
			return self::$instance;
		}
		self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		$this->initialize();
		// $this->define_prefixes();
		// $this->create_tables();
		// $this->create_default_rows();
		// add_action( 'admin_init', [$this,'create_default_rows']);

		// Initialize_Db::get_instance()->create_tables();
		// Initialize_Db::get_instance()->create_default_rows();

		// Check if deck_group table exists.
		$deck_group_table_exists = $this->schema_builder->hasTable( SP_TABLE_DECK_GROUPS );
		if ( $deck_group_table_exists ) {
			$this->create_tables();
			$this->create_default_rows();
			$this->assign_all_card_groups_to_uncategorized_topic();
		}
	}

	public function initialize() {
		$capsule       = new Capsule();
		$this->capsule = $capsule;
		$capsule->addConnection(
			array(
				'driver'   => 'mysql',
				'host'     => DB_HOST,
				'database' => DB_NAME,
				'username' => DB_USER,
				'password' => DB_PASSWORD,
				'strict'   => false,
			)
		);
		$capsule->setAsGlobal();
		$capsule->bootEloquent();
		$schema_builder       = $capsule->connection()->getSchemaBuilder();
		$this->schema_builder = $schema_builder;
		// todo remove after test
		Manager::enableQueryLog();
	}

	private function define_prefixes() {
	}

	public function create_default_rows() {
		$this->crete_default_deck_group();
	}

	private function crete_default_deck_group(): void {
		$deck_group = DeckGroup::query()
		                       ->where( 'name', '=', 'Uncategorized' )
		                       ->first();
		if ( $deck_group ) {
			$this->create_default_deck( $deck_group );

			return;
		}

		$deck_group = DeckGroup::query()
		                       ->where( 'name', '=', 'Uncategorized' )
		                       ->withTrashed();
		if ( ! empty( $deck_group ) ) {
			$deck_group->forceDelete();
			$deck_group = DeckGroup::firstOrCreate( array( 'name' => 'Uncategorized' ) );
		}
		$id = $deck_group->id;

		update_option( Settings::OP_UNCATEGORIZED_DECK_GROUP_ID, $id );
		$this->create_default_deck( $deck_group );
	}

	private function create_default_deck( $deck_group ): void {
		$deck = Deck::query()
		            ->where( 'name', '=', 'Uncategorized' )
		            ->where( 'deck_group_id', '=', $deck_group->id )
		            ->first();
		if ( ! empty( $deck ) ) {
			$this->create_default_topic( $deck );

			return;
		}

		$deck = Deck::where( 'name', 'Uncategorized' );
		if ( ! empty( $deck ) ) {
			$deck->delete();
		}

		$deck = Deck::create(
			array(
				'name'          => 'Uncategorized',
				'deck_group_id' => $deck_group->id,
			)
		);
		update_option( Settings::OP_UNCATEGORIZED_DECK_ID, $deck->id );
		$this->create_default_topic( $deck );
	}

	private function create_default_topic( $deck ): void {
		$topic = Topic::query()
		              ->where( 'name', '=', 'Uncategorized' )
		              ->where( 'deck_id', '=', $deck->id )
		              ->first();

		if ( ! empty( $topic ) ) {
			update_option( Settings::OP_UNCATEGORIZED_TOPIC_ID, $topic->id );

			return;
		}

		$topic = Topic::where( 'name', 'Uncategorized' );
		if ( ! empty( $topic ) ) {
			$topic->delete();
		}

		$topic = Topic::create(
			array(
				'name'    => 'Uncategorized',
				'deck_id' => $deck->id,
			)
		);
		update_option( Settings::OP_UNCATEGORIZED_TOPIC_ID, $topic->id );
	}

	public function create_tables() {
		return;
//		$this->create_table_deck_group();
//		$this->create_table_deck();
//		$this->create_table_tags();
//		$this->create_table_taggable();
//		$this->create_table_taggable_excluded();
//		$this->create_table_card_group();
//		$this->create_table_cards();
//		$this->create_table_study();
//		$this->create_table_answered();
//		$this->create_table_study_log();
//		$this->create_answer_log();
//		$this->create_table_topics();
//		$this->create_table_collections();
//		$this->create_table_user_cards();
	}

	public function create_table_deck_group() {
		// Deck groups
		if ( ! $this->schema_builder->hasTable( SP_TABLE_DECK_GROUPS ) ) {
			Capsule::schema()->create(
				SP_TABLE_DECK_GROUPS,
				function ( Blueprint $table ) {
					$table->id();
					$table->string( 'name' )->unique();
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}
	}

	public function create_table_deck(): void {
		// Deck
		if ( ! $this->schema_builder->hasTable( SP_TABLE_DECKS ) ) {
			Capsule::schema()->create(
				SP_TABLE_DECKS,
				function ( Blueprint $table ) {
					$table->id();
					$table->string( 'name' )->unique();
					// $table->foreignId('deck_group_id')->references('id')->on(SP_TABLE_DECK_GROUPS);
					$table->foreignId( 'deck_group_id' )->constrained( SP_TABLE_DECK_GROUPS )->cascadeOnDelete()->cascadeOnUpdate();
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}
	}

	public function create_table_collections(): void {
		// Deck
		if ( ! $this->schema_builder->hasTable( SP_TABLE_COLLECTIONS ) ) {
			Capsule::schema()->create(
				SP_TABLE_COLLECTIONS,
				function ( Blueprint $table ) {
					$table->id();
					$table->string( 'name' )->unique();
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}
	}

	public function create_table_topics(): void {
		// Deck
		if ( ! $this->schema_builder->hasTable( SP_TABLE_TOPICS ) ) {
			Capsule::schema()->create(
				SP_TABLE_TOPICS,
				function ( Blueprint $table ) {
					$table->id();
					$table->string( 'name' )->unique();
					$table->foreignId( 'deck_id' )->constrained( SP_TABLE_DECKS )->cascadeOnDelete()->cascadeOnUpdate();
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}


	}

	public function create_table_user_cards(): void {
		// Deck
		if ( ! $this->schema_builder->hasTable( SP_TABLE_USER_CARDS ) ) {
			Capsule::schema()->create(
				SP_TABLE_USER_CARDS,
				function ( Blueprint $table ) {
					$table->id();
					$table->foreignId( 'user_id' )->constrained( SP_TABLE_USERS )->cascadeOnDelete()->cascadeOnUpdate();
					$table->foreignId( 'card_group_id' )->constrained( SP_TABLE_CARD_GROUPS )->cascadeOnDelete()->cascadeOnUpdate();
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}
	}

	public function create_table_tags() {
		// Tags
		if ( ! $this->schema_builder->hasTable( SP_TABLE_TAGS ) ) {
			Capsule::schema()->create(
				SP_TABLE_TAGS,
				function ( Blueprint $table ) {
					$table->id();
					$table->string( 'name' )->unique();
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}
	}

	public function create_table_taggable() {
		// Taggables
		if ( ! $this->schema_builder->hasTable( SP_TABLE_TAGGABLES ) ) {
			Capsule::schema()->create(
				SP_TABLE_TAGGABLES,
				function ( Blueprint $table ) {
					$table->id();
					// $table->foreignId('tag_id')->constrained(SP_TABLE_TAGS)->onDelete('cascade');
					$table->foreignId( 'tag_id' )->constrained( SP_TABLE_TAGS )->cascadeOnDelete()->cascadeOnUpdate();
					$table->string( 'taggable_id' );
					$table->string( 'taggable_type' );
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}
		// if (!$this->schema_builder->hasColumn(SP_TABLE_TAGGABLES, 'excluded')) {
		// Capsule::schema()->table(SP_TABLE_TAGGABLES, function (Blueprint $table) {
		// $table->boolean('excluded')->after('taggable_type');
		// });
		// }
	}

	public function create_table_taggable_excluded() {
		// Taggables Excluded
		if ( ! $this->schema_builder->hasTable( SP_TABLE_TAGGABLES_EXCLUDED ) ) {
			Capsule::schema()->create(
				SP_TABLE_TAGGABLES_EXCLUDED,
				function ( Blueprint $table ) {
					$table->id();
					// $table->foreignId('tag_id')->constrained(SP_TABLE_TAGS)->onDelete('cascade');
					$table->foreignId( 'tag_id' )->constrained( SP_TABLE_TAGS )->cascadeOnDelete()->cascadeOnUpdate();
					$table->string( 'taggable_id' );
					$table->string( 'taggable_type' );
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}
	}

	public function create_table_card_group() {
		// Card group
		if ( ! $this->schema_builder->hasTable( SP_TABLE_CARD_GROUPS ) ) {
			Capsule::schema()->create(
				SP_TABLE_CARD_GROUPS,
				function ( Blueprint $table ) {
					global $wpdb;
					$table->id();
					$table->foreignId( 'deck_id' )->constrained( SP_TABLE_DECKS )->cascadeOnDelete()->cascadeOnUpdate();
					// $table->foreignId('bg_image_id')->references('ID')->on($wpdb->prefix.'posts')->nullOnDelete()->cascadeOnUpdate();
					$table->bigInteger( 'bg_image_id' );
					$table->text( 'name' );
					$table->text( 'whole_question' );
					$table->string( 'card_type' );
					$table->dateTime( 'scheduled_at' );
					$table->boolean( 'reverse' );
					$table->string( 'image_type' )->nullable()->comment( 'The image Display type for image cards. e.g. hide_one_show_one' );
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}
		if ( ! $this->schema_builder->hasColumn( SP_TABLE_CARD_GROUPS, 'collection_id' ) ) {
			Capsule::schema()->table(
				SP_TABLE_CARD_GROUPS,
				function ( Blueprint $table ) {
					// $table
					// ->integer( 'collection_id' )
					// ->after( 'card_type' )
					// ->nullable();
					$table
						->foreignId( 'collection_id' )
						->constrained( SP_TABLE_COLLECTIONS )
						->cascadeOnDelete()
						->cascadeOnUpdate()
						->nullOnDelete();
				}
			);
		}
		if ( ! $this->schema_builder->hasColumn( SP_TABLE_CARD_GROUPS, 'topic_id' ) ) {
			Capsule::schema()->table(
				SP_TABLE_CARD_GROUPS,
				function ( Blueprint $table ) {
					$table
						->foreignId( 'topic_id' )
						->constrained( SP_TABLE_TOPICS )
						->cascadeOnDelete()
						->cascadeOnUpdate()
						->nullOnDelete();
				}
			);
		}
	}

	public function create_table_cards() {
		// Card
		if ( ! $this->schema_builder->hasTable( SP_TABLE_CARDS ) ) {
			Capsule::schema()->create(
				SP_TABLE_CARDS,
				function ( Blueprint $table ) {
					$table->id();
					// $table->foreignId('card_group_id')->references('id')->on(SP_TABLE_CARD_GROUPS);
					$table->foreignId( 'card_group_id' )->constrained( SP_TABLE_CARD_GROUPS )->cascadeOnDelete()->cascadeOnUpdate();
					$table->string( 'hash' );
					$table->string( 'c_number' );
					$table->text( 'question' );
					$table->text( 'answer' );
					$table->integer( 'x_position' )->nullable();
					$table->integer( 'y_position' )->nullable();
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}
	}

	public function create_table_study() {
		// Study
		if ( ! $this->schema_builder->hasTable( SP_TABLE_STUDY ) ) {
			Capsule::schema()->create(
				SP_TABLE_STUDY,
				function ( Blueprint $table ) {
					global $wpdb;
					$table->id();
					$table->foreignId( 'deck_id' )->constrained( SP_TABLE_DECKS )->cascadeOnUpdate()->cascadeOnDelete();
					$table->foreignId( 'user_id' )->references( 'ID' )->on( ( $wpdb->prefix . 'users' ) )->cascadeOnUpdate()->cascadeOnDelete();
					$table->boolean( 'all_tags' );
					$table->integer( 'no_to_revise' );
					$table->integer( 'no_of_new' );
					$table->integer( 'no_on_hold' );
					$table->boolean( 'revise_all' );
					$table->boolean( 'study_all_new' );
					$table->boolean( 'study_all_on_hold' );
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}

		// Add topic_id to study table.
		if ( ! $this->schema_builder->hasColumn( SP_TABLE_STUDY, 'topic_id' ) ) {
			Capsule::schema()->table(
				SP_TABLE_STUDY,
				function ( Blueprint $table ) {
					$table
						->foreignId( 'topic_id' )
						->constrained( SP_TABLE_TOPICS )
						->nullOnDelete();
				}
			);
		}
		// Add active column.
		if ( ! $this->schema_builder->hasColumn( SP_TABLE_STUDY, 'active' ) ) {
			Capsule::schema()->table(
				SP_TABLE_STUDY,
				function ( Blueprint $table ) {
					$table
						->boolean( 'active' )
						->default( 1 )
						->after( 'no_on_hold' )
						->comment( 'Whether the study is active or not.' );
				}
			);
		}
		// Alter deck_id to study table
		if ( $this->schema_builder->hasColumn( SP_TABLE_STUDY, 'deck_id' ) ) {
			Capsule
				::schema()
				->table(
					SP_TABLE_STUDY,
					function ( Blueprint $table ) {
						$prefix = sp_get_db_prefix();
						if ( $this->foreign_key_exists( SP_TABLE_STUDY, 'deck_id' ) ) {
							$table->dropForeign( $prefix . 'study_deck_id_foreign' );
						}
						if ( $this->has_index( SP_TABLE_STUDY, 'deck_id' ) ) {
							$table->dropIndex( $prefix . 'study_deck_id_foreign' );
						}
					}
				);
		}

	}

	private function foreign_key_exists( string $table_name, string $foreign_key ) {
		$foreignKeys = Capsule
			::schema()
			->getConnection()
			->getDoctrineSchemaManager()
			->listTableForeignKeys( $table_name );

		if ( ! $foreignKeys ) {
			return [];
		}

		$foreign_key_exist = false;
		// Find the foreign key.
		foreach ( $foreignKeys as $foreignKey ) {
			$local = $foreignKey->getLocalColumns();
			if ( $foreign_key === $local[0] ) {
				$foreign_key_exist = true;
				break;
			}
		}

		return $foreign_key_exist;
	}

	private function has_index( string $table_name, string $index_name ) {
		$indexes = Capsule
			::schema()
			->getConnection()
			->getDoctrineSchemaManager()
			->listTableIndexes( $table_name );

		if ( ! $indexes ) {
			return [];
		}

		$index_exist = false;
		// Find the foreign key.
		foreach ( $indexes as $index ) {
			$local = $index->getColumns();
			if ( $index_name === $local[0] ) {
				$index_exist = true;
				break;
			}
		}

		return $index_exist;
	}

	public function create_table_answered() {
		// Answered

		if ( ! $this->schema_builder->hasTable( SP_TABLE_ANSWERED ) ) {
			Capsule::schema()->create(
				SP_TABLE_ANSWERED,
				function ( Blueprint $table ) {
					global $wpdb;
					$table->id();
					// $table->foreignId('study_id')->references('id')->on(SP_TABLE_STUDY);
					$table->foreignId( 'study_id' )->constrained( SP_TABLE_STUDY )->cascadeOnDelete()->cascadeOnUpdate()->comment( 'The study id' );
					$table->bigInteger( 'card_id' )->index()->unsigned()->nullable();
					$table->foreign( 'card_id' )->references( 'id' )->on( SP_TABLE_CARDS )->cascadeOnUpdate()->nullOnDelete();
					// $table->text( 'answer' );
					$table->string( 'grade' )->nullable();
					$table->integer( 'ease_factor' )->nullable();
					$table->dateTime( 'next_due_at' )->nullable();
					$table->boolean( 'next_due_answered' )->nullable();
					$table->dateTime( 'started_at' )->nullable();
					$table->boolean( 'answered_as_new' )->nullable();
					$table->boolean( 'answered_as_revised' )->nullable();
					$table->integer( 'next_interval' )->nullable();
					$table->dateTime( 'rejected_at' )->nullable();
					$table->softDeletes();
					$table->timestamps();
				}
			);
		}
		// if ( ! $this->schema_builder->hasColumn( SP_TABLE_ANSWERED, 'question' ) ) {
		// Capsule::schema()->table( SP_TABLE_ANSWERED, function ( Blueprint $table ) {
		// $table->text( 'question' )->after( 'answer' );
		// } );
		// }
		if ( ! $this->schema_builder->hasColumn( SP_TABLE_ANSWERED, 'card_last_updated_at' ) ) {
			Capsule::schema()->table(
				SP_TABLE_ANSWERED,
				function ( Blueprint $table ) {
					$table->dateTime( 'card_last_updated_at' )->after( 'updated_at' )->nullable();
				}
			);
		}
		if ( ! $this->schema_builder->hasColumn( SP_TABLE_ANSWERED, 'accept_changes_comment' ) ) {
			Capsule::schema()->table(
				SP_TABLE_ANSWERED,
				function ( Blueprint $table ) {
					$table->text( 'accept_changes_comment' )->after( 'card_last_updated_at' )->nullable();
				}
			);
		}
	}

	public function create_answer_log() {
		// Answer log

		if ( ! $this->schema_builder->hasTable( SP_TABLE_ANSWER_LOG ) ) {
			Capsule::schema()->create(
				SP_TABLE_ANSWER_LOG,
				function ( Blueprint $table ) {
					global $wpdb;
					$table->id();
					$table->foreignId( 'study_id' )->constrained( SP_TABLE_STUDY )->cascadeOnDelete()->cascadeOnUpdate()->comment( 'The study id' );
					$table->bigInteger( 'card_id' )->index()->unsigned()->nullable();
					$table->foreign( 'card_id' )->references( 'id' )->on( SP_TABLE_CARDS )->cascadeOnUpdate()->nullOnDelete();
					$table->dateTime( 'last_card_updated_at' )->nullable();
					$table->text( 'accepted_change_comment' )->nullable();
					$table->text( 'question' )->nullable();
					$table->text( 'answer' )->nullable();
					$table->timestamps();
				}
			);
		}
	}

	public function create_table_study_log() {
		// Study Log
		if ( ! $this->schema_builder->hasTable( SP_TABLE_STUDY_LOG ) ) {
			Capsule::schema()->create(
				SP_TABLE_STUDY_LOG,
				function ( Blueprint $table ) {
					global $wpdb;
					$table->id();
					// $table->foreignId('study_id')->references('id')->on(SP_TABLE_STUDY);
					$table->foreignId( 'study_id' )->constrained( SP_TABLE_STUDY )->cascadeOnDelete()->cascadeOnUpdate();
					// $table->foreignId('card_id')->references('id')->on(SP_TABLE_CARDS);
					$table->foreignId( 'card_id' )->constrained( SP_TABLE_CARDS )->cascadeOnUpdate()->cascadeOnDelete();
					// $table->foreignId('answered_id')->references('id')->on(SP_TABLE_ANSWERED);
					// $table->foreignId('answered_id')->nullable()->constrained(SP_TABLE_ANSWERED)->cascadeOnDelete()->cascadeOnDelete();
					$table->string( 'action' );
					$table->dateTime( 'created_at' );
				}
			);
		}
	}

	/**
	 * Migration - Assign all card groups that does not have any topic to Uncategorized topic.
	 *
	 * @return void
	 */
	public function assign_all_card_groups_to_uncategorized_topic(): void {
		if ( ! wp_doing_ajax() ) {
			return; // Run only on ajax incase there are huge number of card groups, so that it does not affect the performance.
		}
		$already_assigned = get_option( Settings::OPTION_CARD_GROUPS_WITHOUT_TOPICS_IS_ASSIGNED_UNCATEGORIZED_TOPICS, false );
		if ( $already_assigned ) {
//			return;
		}


		$uncategorized_topic_id = get_option( Settings::OP_UNCATEGORIZED_TOPIC_ID );
//		Common::send_error( array(
//			'card_groups'            => '',
//			'uncategorized_topic_id' => $uncategorized_topic_id
//		) );
		if ( ! $uncategorized_topic_id ) {
			return;
		}
		$card_groups = CardGroup::query()->where( 'topic_id', '=', 0 )->get();
//		Common::send_error( array(
//			'card_groups' => $card_groups
//		) );
		foreach ( $card_groups as $card_group ) {
			$card_group->topic_id = $uncategorized_topic_id;
			$card_group->save();
		}

		update_option( Settings::OPTION_CARD_GROUPS_WITHOUT_TOPICS_IS_ASSIGNED_UNCATEGORIZED_TOPICS, true );
	}

}
