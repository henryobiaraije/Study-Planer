<?php


	namespace StudyPlanner\Shortcodes;


	use StudyPlanner\Initializer;
	use StudyPlanner\Libs\Common;
	use function StudyPlanner\get_template_path;

	/**
	 * Class BookBundles
	 */
	class Short_User_Dashboard {
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

		final public function initialize() : void {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
//   add_action('init', array($this, 'init'));
			add_action( 'init', function () {
				if ( ( ! wp_doing_ajax() ) && ( ! is_admin() ) ) {
					add_shortcode( 'sp_user_dashboard', [ $this, 'load_view' ] );
				}
			} );
		}

		public function init() : void {

		}

		final public function load_view( $attr ) : string {

			do_action( 'sp_enqueue_scripts_sc_user_dashboard' );
			$html = Common::get_contents( get_template_path( 'shortcodes/sc-user-dashboard' ) );

			return $html;
		}

		public function get_page_data() {

		}

		public function localize_data() : void {
//			Bra_Size_Calculator::add_to_localize( 'calculator_settings', $this->get_page_data() );
		}

		final public function register_scripts() : void {
			$dis = $this;
			$css = Initializer::$js_url . '/public/sc-user-dashboard.css';
			$js  = Initializer::$js_url . '/public/sc-user-dashboard.js';

			wp_register_style( 'sp-sc-user-dashboard', $css, [], Initializer::$script_version );
			wp_register_script( 'sp-sc-user-dashboard', $js, [ 'jquery' ], Initializer::$script_version, true );

			// enqueue the scripts
			add_action( 'sp_enqueue_scripts_sc_user_dashboard', function () use ( $dis ) {
//				do_action( 'bsc_enqueue_default_frontend_scripts' );
				wp_enqueue_style( 'sp-sc-user-dashboard' );
				wp_enqueue_script( 'sp-sc-user-dashboard' );

				$dis->localize_data();
			} );
		}


	}