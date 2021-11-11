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

	$capsule = new Capsule;
	$capsule->addConnection( [
		"driver"   => "mysql",
		"host"     => DB_HOST,
		"database" => DB_NAME,
		"username" => DB_USER,
		"password" => DB_PASSWORD,
	] );

	// Set the event dispatcher used by Eloquent models... (optional)

//	$capsule->setEventDispatcher(new Dispatcher(new Container));

	$capsule->setAsGlobal();
	$capsule->bootEloquent();
	$schema_builder = $capsule->connection()->getSchemaBuilder();

	global $wpdb;
	$prefix = $wpdb->prefix . 'sp_';
	define( 'SP_DB_PREFIX', $prefix );
	$table_deck_groups = $prefix . 'deck_groups';
	$table_tags        = $prefix . 'tags';


	if ( ! $schema_builder->hasTable( $table_deck_groups ) ) {
		Capsule::schema()->create( $table_deck_groups, function ( Blueprint $table ) {
			$table->increments( 'id' );
			$table->string( 'name' )->unique();
			$table->softDeletes();
			$table->timestamps();
		} );
	}

	if ( ! $schema_builder->hasTable( $table_tags ) ) {
		Capsule::schema()->create( $table_tags, function ( Blueprint $table ) {
			$table->increments( 'id' );
			$table->string( 'name' )->unique();
			$table->softDeletes();
			$table->timestamps();
		} );
	}

