<?php

	// Initialize database

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Illuminate\Database\Capsule\Manager as Capsule;
	use Illuminate\Support\Facades\Schema;

	$capsule = new Capsule;
	$capsule->addConnection( [
		"driver"   => "mysql",
		"host"     => DB_HOST,
		"database" => DB_NAME,
		"username" => DB_USER,
		"password" => DB_PASSWORD,
	] );
	$capsule->setAsGlobal();
	$capsule->bootEloquent();

	global $wpdb;
	$table_deck_groups = $wpdb->prefix . 'sp_deck_group';

	$schema_builder = $capsule->connection()->getSchemaBuilder();
	if ( ! $schema_builder->hasTable( $table_deck_groups ) ) {
		Capsule::schema()->create( $table_deck_groups, function ( $table ) {
			$table->increments( 'id' );
			$table->string( 'name' )->unique();
			$table->timestamps();
		} );
	}
