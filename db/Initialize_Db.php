<?php


	namespace StudyPlanner\Db;


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
		}

		private function define_prefixes() {
			global $wpdb;
			$prefix = $wpdb->prefix . 'sp_';
			define( 'SP_DB_PREFIX', $prefix );
			$table_deck_groups = SP_DB_PREFIX . 'deck_groups';
			$table_tags        = SP_DB_PREFIX . 'tags';
			$table_taggables   = SP_DB_PREFIX . 'taggables';
			$table_decks       = SP_DB_PREFIX . 'decks';
			define( 'SP_TABLE_DECK_GROUPS', $table_deck_groups );
			define( 'SP_TABLE_TAGS', $table_tags );
			define( 'SP_TABLE_TAGGABLES', $table_taggables );
			define( 'SP_TABLE_DECKS', $table_decks );
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
					$table->increments( 'id' );
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
					$table->id( 'id' );
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

		}


	}