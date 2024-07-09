<?php

//if(!defined('ABSPATH')) {
//	exit;
//}

if ( ! defined( 'DB_HOST' ) ) {
	define( 'DB_HOST', 'localhost' );
}
if ( ! defined( 'DB_NAME' ) ) {
//	define( 'DB_NAME', 'Test-Site-Wordpress' );
	define( 'DB_NAME', 'medabceu_wp354' ); // pyra
}
if ( ! defined( 'DB_USER' ) ) {
//	define( 'DB_USER', 'root' );
	define( 'DB_USER', 'medabceu_wp354' );
}
if ( ! defined( 'DB_PASSWORD' ) ) {
//	define( 'DB_PASSWORD', 'aaaa' );
	define( 'DB_PASSWORD', '4Sp7t)61[b' );
}

require_once __DIR__ . '/vendor/autoload.php';

if( ! function_exists( 'error_log' ) ) {
//	function phinx_migrate() {
//		$phinx = new \Phinx\Console\PhinxApplication();
//		$wrap  = new \Symfony\Component\Console\Input\StringInput( 'migrate' );
//		$phinx->run( $wrap );
//	}

	function error_log( $message ) {
		echo $message;
	}
}

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
			],
			'development'             => [
				'adapter' => 'mysql',
				'host'    => DB_HOST,
				'name'    => DB_NAME,
				'user'    => DB_USER,
				'pass'    => DB_PASSWORD,
				'port'    => '3306',
				'charset' => defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8',
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
