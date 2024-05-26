<?php

//if(!defined('ABSPATH')) {
//	exit;
//}

if ( ! defined( 'DB_HOST' ) ) {
	define( 'DB_HOST', 'localhost' );
}
if ( ! defined( 'DB_NAME' ) ) {
	define( 'DB_NAME', 'Test-Site-Wordpress' );
}
if ( ! defined( 'DB_USER' ) ) {
	define( 'DB_USER', 'root' );
}
if ( ! defined( 'DB_PASSWORD' ) ) {
	define( 'DB_PASSWORD', 'aaaa' );
}

require_once __DIR__ . '/vendor/autoload.php';

return
	[
		'paths'         => [
			'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
			'seeds'      => '%%PHINX_CONFIG_DIR%%/db/seeds'
		],
		'environments'  => [
			'default_migration_table' => 'phinxlog',
			'default_environment'     => 'development',
			'production'              => [
				'adapter' => 'mysql',
				'host'    => DB_HOST,
				'name'    => DB_NAME,
				'user'    => DB_USER,
				'pass'    => DB_PASSWORD,
				'port'    => '3306',
				'charset' => defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8',
				'prefix'  => 'wptr_'
			],
			'development'             => [
				'adapter' => 'mysql',
				'host'    => DB_HOST,
				'name'    => DB_NAME,
				'user'    => DB_USER,
				'pass'    => DB_PASSWORD,
				'port'    => '3306',
				'charset' => defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8',
				'prefix'  => 'wp_'
			],
			'testing'                 => [
				'adapter' => 'mysql',
				'host'    => DB_HOST,
				'name'    => DB_NAME,
				'user'    => DB_USER,
				'pass'    => DB_PASSWORD,
				'port'    => '3306',
				'charset' => defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8',
			]
		],
		'version_order' => 'creation'
	];
