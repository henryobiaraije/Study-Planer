<?php

// Initialize database


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Model\Deck;
use Model\DeckGroup;
use StudyPlanner\Libs\Settings;

$capsule = new Capsule();
$capsule->addConnection(
	array(
		'driver'   => 'mysql',
		'host'     => DB_HOST,
		'database' => DB_NAME,
		'username' => DB_USER,
		'password' => DB_PASSWORD,
	)
);

// Set the event dispatcher used by Eloquent models... (optional)

// $capsule->setEventDispatcher(new Dispatcher(new Container));

$capsule->setAsGlobal();
$capsule->bootEloquent();
$schema_builder = $capsule->connection()->getSchemaBuilder();

global $wpdb;
$prefix = $wpdb->prefix . 'sp_';
define( 'SP_DB_PREFIX', $prefix );
$table_deck_groups = SP_DB_PREFIX . 'deck_groups';
$table_tags        = SP_DB_PREFIX . 'tags';
$table_taggables   = SP_DB_PREFIX . 'taggables';
$table_decks       = SP_DB_PREFIX . 'decks';
$table_collections = SP_DB_PREFIX . 'collections';
define( 'SP_TABLE_DECK_GROUPS', $table_deck_groups );
define( 'SP_TABLE_TAGS', $table_tags );
define( 'SP_TABLE_TAGGABLES', $table_taggables );
define( 'SP_TABLE_DECKS', $table_decks );
define( 'SP_TABLE_COLLECTIONS', $table_collections );

// Deck groups
if ( ! $schema_builder->hasTable( $table_deck_groups ) ) {
	Capsule::schema()->create(
		$table_deck_groups,
		function ( Blueprint $table ) {
			$table->increments( 'id' );
			$table->string( 'name' )->unique();
			$table->softDeletes();
			$table->timestamps();
		}
	);
}

// Deck
if ( ! $schema_builder->hasTable( $table_decks ) ) {
	Capsule::schema()->create(
		$table_decks,
		function ( Blueprint $table ) {
			$table->id();
			$table->string( 'name' )->unique();
			$table->foreignId( 'deck_group_id' )->constrained();
			$table->softDeletes();
			$table->timestamps();
		}
	);
}
// Tags
if ( ! $schema_builder->hasTable( $table_tags ) ) {
	Capsule::schema()->create(
		$table_tags,
		function ( Blueprint $table ) {
			$table->id( 'id' );
			$table->string( 'name' )->unique();
			$table->softDeletes();
			$table->timestamps();
		}
	);
}
// Taggables
if ( ! $schema_builder->hasTable( $table_taggables ) ) {
	Capsule::schema()->create(
		$table_taggables,
		function ( Blueprint $table ) use ( $table_tags ) {
			// $table->foreignId( 'tag_id' )->references( 'id')->on($table_tags)->onDelete( 'cascade');
			$table->id();
			$table->foreignId( 'tag_id' )->constrained( $table_tags )->onDelete( 'cascade' );
			$table->string( 'taggable_id' );
			$table->string( 'taggable_type' );
			$table->softDeletes();
			$table->timestamps();
		}
	);
}


// Db defaults
// try {
// $uncategorized_deck_groups = DeckGroup::query()->firstOrFail( [ 'name' => 'Uncategorized' ] );
// } catch ( Exception $e ) {
// $deck_group = DeckGroup::firstOrCreate( [ 'name', 'Uncategorized' ] );
// update_option( Settings::OP_UNCATEGORIZED_DECK_GROUP_ID, $deck_group->id );
// }
// try {
// $uncategorized_deck_groups = Deck::query()->firstOrFail( [ 'name' => 'Uncategorized' ] );
// } catch ( Exception $e ) {
// $deck_group = Deck::firstOrCreate( [ 'name', 'Uncategorized' ] );
// update_option( Settings::OP_UNCATEGORIZED_DECK_ID, $deck_group->id );
// }
