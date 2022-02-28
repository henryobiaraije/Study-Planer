<?php


	namespace StudyPlanner\Pages;

	use StudyPlanner\StudyPlanner;
	use StudyPlanner\Includes\Libs\Common;
	use StudyPlanner\Interfaces\AdminPageInterface;
	use StudyPlanner\Libs\Settings;
	use StudyPlanner\Models\Link;

	/**
		* Class BookBundles
		*/
	class ShortcodeSearchBar {
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
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
			add_action( 'init', array( $this, 'init' ) );

		}

		public function init() : void {
			if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
				return;
			}

			add_shortcode( 'search_bar_endpoint', [ $this, 'load_view' ] );
//				Common::send_error( [
//					'is_admin ' => is_admin(),
//					'is_ajax'   => wp_doing_ajax(),
//					'is_cron'   => wp_doing_cron(),
//				] );
		}

		public function load_view()  {
			if(is_admin()){
				return;
			}
			$text =
				"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::2cacdbffa2d26744ecc8d222d4328f8a";
//			Common::in_script( [
//				'text' => base64_decode( $text ),
//			] );
			do_action( 'sbe_enqueue_scripts_sc_search_bar' );
			$template = sbe_get_template_path( 'shortcodes/sc-search-bar' );
			$html     = Common::get_contents( $template );
//			Common::in_script([
//				'htm' => $html,
//				'$template' => $template,
//			]);
			return $html;
		}

		public function get_page_data() {

		}

		public function localize_data() : void {
			$this->get_page_data();
		}

		public function register_scripts() : void {
			$dis = $this;
			$css = StudyPlanner::$css_url . '/public/sc-search-bar.css';
			$js  = StudyPlanner::$js_url . '/public/sc-search-bar.js';

			wp_register_style( 'sbe-search-bar', $css, [], StudyPlanner::$script_version );
			wp_register_script( 'sbe-search-bar', $js, [ 'jquery' ], StudyPlanner::$script_version, true );

			// enqueue the scripts
			add_action( 'sbe_enqueue_scripts_sc_search_bar', function () use ( $dis ) {
				do_action( 'sbe_enqueue_default_frontend_scripts' );
				wp_enqueue_style( 'sbe-search-bar' );
				wp_enqueue_script( 'sbe-search-bar' );

				$dis->localize_data();
			} );
		}


	}