<?php

	namespace StudyPlanner\Pages;

	use StudyPlanner\Initializer;
	use StudyPlanner\Libs\Settings;

	/**
	 * Class AdminEndpoints
	 */
	class Admin_Tags {
		/**
		 * @var self $instance
		 */
		private static $instance;


		/**
		 * AdminAuth constructor.
		 */
		private function __construct() {
		}

		public static function get_instance() : self {
			if ( self::$instance ) {
				return self::$instance;
			}
			self::$instance = new self();
			self::$instance->initialize();

			return self::$instance;
		}

		public function initialize() : void {
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
			add_action( 'init', array( $this, 'init' ) );
		}

		public function init() : void {
			add_action( 'admin_menu', [ $this, "add_admin_menu" ], 11 );
			add_action( 'in_admin_header', function () {
				remove_all_actions( 'user_admin_notices' );
				remove_all_actions( 'admin_notices' );
			} );
		}

		/**
		 * Add admin menus
		 */
		public function add_admin_menu() : void {
			add_submenu_page(
				'study-planner',
				'Tags',
				'Tags',
				'manage_options',
				Settings::SLUG_TAGS,
				array( $this, 'load_view' )
			);
			$url = Initializer::get_admin_url( Settings::SLUG_TAGS);
			Initializer::add_to_localize( 'page_tags', $url );
		}

		public function load_view() : void {
			do_action( 'sp_enqueue_default_admin_tags' );
			\StudyPlanner\load_template( 'admin/admin-tags' );
		}

		public function get_page_data() : array {

			return [

			];
		}

		public function localize_data() : void {
//			Initializer::add_to_localize( 'deck_groups', $this->get_page_data() );
		}

		public function register_scripts() : void {
			$dis = $this;
//			$css = Initializer::$css_url . '/admin/admin-deck-groups.css';
			$css = Initializer::$js_url . '/admin/admin-tags.css';
			$js  = Initializer::$js_url . '/admin/admin-tags.js';

			wp_register_style( 'sp-admin-tags', $css, [], Initializer::$script_version );
			wp_register_script( 'sp-admin-tags', $js, [ 'jquery' ], Initializer::$script_version, true );

			// enqueue the scripts
			add_action( 'sp_enqueue_default_admin_tags', function () use ( $dis ) {
				do_action( 'sp_enqueue_default_admin_scripts' );
				wp_enqueue_style( 'sp-admin-tags' );
				wp_enqueue_script( 'sp-admin-tags' );

				$dis->localize_data();
			} );
		}


	}