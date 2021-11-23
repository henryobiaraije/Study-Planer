<?php


	namespace StudyPlanner\Db;


	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Capsule\Manager as Capsule;
	use Illuminate\Database\Eloquent\ModelNotFoundException;
	use Illuminate\Database\Schema\Blueprint;
	use Model\Deck;
	use Model\DeckGroup;
	use PDOException;
	use StudyPlanner\Libs\Settings;

	class Initialize_Db {

		private static $instance;
		private        $capsule;
		private        $schema_builder;

		public static function get_instance() {
			if ( self::$instance ) {
				return self::$instance;
			}
			self::$instance = new self();

			return self::$instance;
		}

		private function __construct() {
			$this->initialize();
			$this->define_prefixes();
			$this->create_tables();
			$this->create_default_rows();
//			add_action( 'admin_init', [$this,'create_default_rows']);
		}

		public function initialize() {
			$capsule       = new Capsule;
			$this->capsule = $capsule;
			$capsule->addConnection( [
				"driver"   => "mysql",
				"host"     => DB_HOST,
				"database" => DB_NAME,
				"username" => DB_USER,
				"password" => DB_PASSWORD,
			] );
			$capsule->setAsGlobal();
			$capsule->bootEloquent();
			$schema_builder       = $capsule->connection()->getSchemaBuilder();
			$this->schema_builder = $schema_builder;

			// todo remove after test
			Manager::enableQueryLog();

		}

		private function define_prefixes() {
			global $wpdb;
			$prefix = $wpdb->prefix . 'sp_';
			define( 'SP_DB_PREFIX', $prefix );
			define( 'SP_TABLE_DECK_GROUPS', SP_DB_PREFIX . 'deck_groups' );
			define( 'SP_TABLE_TAGS', SP_DB_PREFIX . 'tags' );
			define( 'SP_TABLE_TAGGABLES', SP_DB_PREFIX . 'taggables' );
			define( 'SP_TABLE_DECKS', SP_DB_PREFIX . 'decks' );
			define( 'SP_TABLE_CARD_GROUPS', SP_DB_PREFIX . 'card_groups' );
			define( 'SP_TABLE_CARDS', SP_DB_PREFIX . 'cards' );
			define( 'SP_TABLE_STUDY', SP_DB_PREFIX . 'study' );
			define( 'SP_TABLE_ANSWERED', SP_DB_PREFIX . 'answered' );
		}

		private function create_default_rows() {

			$this->crete_default_deck_group();

		}

		private function crete_default_deck_group() {
			try {
				$deck_group = DeckGroup::query()->firstOrFail(
					[ 'name' => 'Uncategorized' ]
				);
				$this->create_default_deck( $deck_group );
			} catch ( PDOException $e ) {
				$deck_group = DeckGroup::firstOrCreate( [ 'name' => 'Uncategorized' ] );
				update_option( Settings::OP_UNCATEGORIZED_DECK_GROUP_ID, $deck_group->id );
				$this->create_default_deck( $deck_group );
			}
		}

		private function create_default_deck( $deck_group ) {
			try {
				$deck = Deck::query()
					->where( 'name', '=', 'Uncategorized' )
					->where( 'deck_group_id', '=', $deck_group->id )
					->firstOrFail();
			} catch ( ModelNotFoundException $e ) {
				$deck = Deck::where( 'name', 'Uncategorized' );
				$deck->delete();
				$deck = Deck::firstOrCreate( [
					'name'          => 'Uncategorized',
					'deck_group_id' => $deck_group->id,
				] );
				update_option( Settings::OP_UNCATEGORIZED_DECK_ID, $deck->id );
			}
		}

		private function create_tables() {

			// Deck groups
			if ( ! $this->schema_builder->hasTable( SP_TABLE_DECK_GROUPS ) ) {
				Capsule::schema()->create( SP_TABLE_DECK_GROUPS, function ( Blueprint $table ) {
					$table->id();
					$table->string( 'name' )->unique();
					$table->softDeletes();
					$table->timestamps();
				} );
			}

			// Deck
			if ( ! $this->schema_builder->hasTable( SP_TABLE_DECKS ) ) {
				Capsule::schema()->create( SP_TABLE_DECKS, function ( Blueprint $table ) {
					$table->id();
					$table->string( 'name' )->unique();
					$table->foreignId( 'deck_group_id' )->references( 'id' )->on( SP_TABLE_DECK_GROUPS );
					$table->softDeletes();
					$table->timestamps();
				} );
			}
			// Tags
			if ( ! $this->schema_builder->hasTable( SP_TABLE_TAGS ) ) {
				Capsule::schema()->create( SP_TABLE_TAGS, function ( Blueprint $table ) {
					$table->id();
					$table->string( 'name' )->unique();
					$table->softDeletes();
					$table->timestamps();
				} );
			}

			// Taggables
			if ( ! $this->schema_builder->hasTable( SP_TABLE_TAGGABLES ) ) {
				Capsule::schema()->create( SP_TABLE_TAGGABLES, function ( Blueprint $table ) {
					$table->id();
					$table->foreignId( 'tag_id' )->constrained( SP_TABLE_TAGS )->onDelete( 'cascade' );
					$table->string( 'taggable_id' );
					$table->string( 'taggable_type' );
					$table->softDeletes();
					$table->timestamps();
				} );
			}

			// Card group
			if ( ! $this->schema_builder->hasTable( SP_TABLE_CARD_GROUPS ) ) {
				Capsule::schema()->create( SP_TABLE_CARD_GROUPS, function ( Blueprint $table ) {
					global $wpdb;
					$table->id();
					$table->foreignId( 'deck_id' )->constrained( SP_TABLE_DECKS );
					$table->foreignId( 'bg_image_id' )->references( 'ID' )->on( $wpdb->prefix . 'posts' );
					$table->text( 'name' );
					$table->text( 'whole_question' );
					$table->string( 'card_type' );
					$table->dateTime( 'scheduled_at' );
					$table->boolean( 'reverse' );
					$table->softDeletes();
					$table->timestamps();
				} );
			}

			// Card
			if ( ! $this->schema_builder->hasTable( SP_TABLE_CARDS ) ) {
				Capsule::schema()->create( SP_TABLE_CARDS, function ( Blueprint $table ) {
					$table->id();
					$table->foreignId( 'card_group_id' )->references( 'id' )->on( SP_TABLE_CARD_GROUPS );
					$table->string( 'hash' );
					$table->string( 'c_number' );
					$table->text( 'question' );
					$table->text( 'answer' );
					$table->integer( 'x_position' );
					$table->integer( 'y_position' );
					$table->softDeletes();
					$table->timestamps();
				} );
			}

			// Study
			if ( ! $this->schema_builder->hasTable( SP_TABLE_STUDY ) ) {
				Capsule::schema()->create( SP_TABLE_STUDY, function ( Blueprint $table ) {
					global $wpdb;
					$table->id();
					$table->foreignId( 'deck_id' )->references( 'id' )->on( SP_TABLE_DECKS );
					$table->foreignId( 'user_id' )->references( 'ID' )->on( $wpdb->prefix . 'users' );
					$table->boolean( 'all_tags' );
					$table->integer( 'no_to_revise' );
					$table->integer( 'no_of_new' );
					$table->integer( 'no_on_hold' );
					$table->boolean( 'revise_all' );
					$table->boolean( 'study_all_new' );
					$table->boolean( 'study_all_on_hold' );
					$table->softDeletes();
					$table->timestamps();
				} );
			}

			// Answered
			if ( ! $this->schema_builder->hasTable( SP_TABLE_ANSWERED ) ) {
				Capsule::schema()->create( SP_TABLE_ANSWERED, function ( Blueprint $table ) {
					global $wpdb;
					$table->id();
					$table->foreignId( 'study_id' )->references( 'id' )->on( SP_TABLE_STUDY );
					$table->foreignId( 'card_id' )->references( 'id' )->on( SP_TABLE_CARDS );
//					$table->foreignId( 'user_id' )->references( 'ID' )->on( $wpdb->prefix . 'users' );
					$table->text( 'answer' );
					$table->string( 'grade' );
					$table->integer( 'ease_factor' );
					$table->dateTime( 'next_due_at' );
					$table->integer( 'next_interval' );
					$table->dateTime( 'rejected_at' );
					$table->softDeletes();
					$table->timestamps();
				} );
			}
		}


	}