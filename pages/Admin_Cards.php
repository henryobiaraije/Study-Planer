<?php

	namespace StudyPlanner\Pages;

	use StudyPlanner\Initializer;

	/**
	 * Class AdminEndpoints
	 */
	class Admin_Cards {
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
				'All Cards',
				'All Cards',
				'manage_options',
				'study-planner-deck-cards',
				array( $this, 'load_view' )
			);
		}

		public function load_view() : void {
			do_action( 'sp_enqueue_default_admin_cards' );
			\StudyPlanner\load_template( 'admin/admin-cards' );
		}

		public function get_page_data() : array {

			return [

			];
		}

		public function localize_data() : void {
			Initializer::add_to_localize( 'deck_groups', $this->get_page_data() );
		}

		public function register_scripts() : void {
			$dis = $this;
//			$css = Initializer::$css_url . '/admin/admin-deck-groups.css';
			$css  = Initializer::$js_url . '/admin/admin-cards.css';
			$js   = Initializer::$js_url . '/admin/admin-cards.js';

			wp_register_style( 'sp-admin-cards', $css, [], Initializer::$script_version );
			wp_register_script( 'sp-admin-cards', $js, [ 'jquery' ], Initializer::$script_version, true );

			// enqueue the scripts
			add_action( 'sp_enqueue_default_admin_cards', function () use ( $dis ) {
				do_action( 'sp_enqueue_default_admin_scripts' );
				wp_enqueue_style( 'sp-admin-cards' );
				wp_enqueue_script( 'sp-admin-cards' );

				$dis->localize_data();
			} );

		}


	}