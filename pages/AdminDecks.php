<?php

namespace StudyPlannerPro\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

use StudyPlannerPro\Initializer;
use StudyPlannerPro\Libs\Settings;
use StudyPlannerPro\Services\FileService;

/**
 * Class AdminDeck
 */
class AdminDeck {
	/**
	 * @var self $instance
	 */
	private static $instance;

	/**
	 * AdminAuth constructor.
	 */
	private function __construct() {
	}

	public static function get_instance(): self {
		if ( self::$instance ) {
			return self::$instance;
		}
		self::$instance = new self();
		self::$instance->initialize();

		return self::$instance;
	}

	public function initialize(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 11 );
		add_action(
			'in_admin_header',
			function () {
				remove_all_actions( 'user_admin_notices' );
				remove_all_actions( 'admin_notices' );
			}
		);
	}

	/**
	 * Add admin menus
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'study-planner-pro',
			'Decks',
			'Decks',
			'manage_options',
			Settings::SLUG_DECKS,
			array( $this, 'load_view' )
		);
		$url = Initializer::get_admin_url( Settings::SLUG_DECKS );
		Initializer::add_to_localize( 'page_decks', $url );
	}

	public function load_view(): void {
		do_action( 'sp_enqueue_default_admin_decks' );
		// \StudyPlannerPro\load_template( 'admin/admin-decks' );
		echo '<div class="sp admin-decks wrap"></div>';
		echo '<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
				<div style="text-align: center;flex: 12;font-size: 50px;" >
				<i class="fa fa-spin fa-spinner" ></i ></div >
			</div >
		';
	}

	public function get_page_data(): array {

		return array();
	}

	public function localize_data(): void {
		Initializer::add_to_localize( 'deck_groups', $this->get_page_data() );
	}

	public function register_scripts(): void {
		$dis = $this;
		// $css = Initializer::$css_url . '/admin/admin-deck-groups.css';
		// $css = Initializer::$js_url . '/admin/admin-decks.css';
		// $js  = Initializer::$js_url . '/admin/admin-decks.js';
//		$js  = FileService::mp_get_js_url( 'main-admin' );
//		$css = FileService::mp_get_css_url( 'main-admin' );

		$js  = FileService::mp_get_js_url_second( '/admin/admin-decks' );
		$css = FileService::mp_get_css_url_second( '/admin/admin-decks' );

		wp_register_style( 'sp-admin-decks', $css, array(), Initializer::$script_version );
		wp_register_script( 'sp-admin-decks', $js, array( 'jquery' ), Initializer::$script_version, true );

		// enqueue the scripts
		add_action(
			'sp_enqueue_default_admin_decks',
			function () use ( $dis ) {
				do_action( 'sp_enqueue_default_admin_scripts' );
				wp_enqueue_style( 'sp-admin-decks' );
				wp_enqueue_script( 'sp-admin-decks' );

				$dis->localize_data();
			}
		);
	}
}
