<?php


namespace StudyPlannerPro\Shortcodes;


use Model\Study;
use StudyPlannerPro\Initializer;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Services\FileService;

use function StudyPlannerPro\get_template_path;
use function StudyPlannerPro\sp_get_user_study;

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

	public static function get_instance(): self {
		if ( self::$instance ) {
			return self::$instance;
		}
		self::$instance = new self();
		self::$instance->initialize();

		return self::$instance;
	}

	final public function initialize(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		//   add_action('init', array($this, 'init'));
		add_action( 'init', function () {
			if ( ( ! wp_doing_ajax() ) && ( ! is_admin() ) ) {
				add_shortcode( 'sp_pro_user_dashboard', [ $this, 'load_view' ] );
			}
		} );
	}

	public function init(): void {
	}

	final public function load_view( $attr ): string {
		if ( ! is_user_logged_in() ) {
			$login_url = wp_login_url( get_permalink() );

			return sprintf( "<div class='sp sp-user-not-logged-in wrap' >
					<p>Please <a href='%s' >login</a> to view this page.</p>
				</div>",
				$login_url
			);
		}
		do_action( 'sp_enqueue_scripts_sc_user_dashboard' );
//        $html = Common::get_contents(get_template_path('shortcodes/sc-user-dashboard'));
		$html = '<div class="sp sp-user-dashboard wrap"></div>';
		$html .= '
			<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
				<div style="text-align: center;flex: 12;font-size: 50px;" >
				<i class="fa fa-spin fa-spinner" ></i ></div >
			</div>
			
			<style>
			.v-dialog {
				  z-index: 9999999999 !important;
			}
			</style>
		';
		$html .= '<div class="admin-1"></div';
		$html .= "
			<script>
				<link
				  rel=\"stylesheet\"
				  href=\"https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp\"
				/>	
			</script>	
		";

		return $html;
	}

	public function get_page_data() {
	}

	public function localize_data(): void {
	}

	final public function register_scripts(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$dis = $this;
//        $css         = Initializer::$js_url.'/public/sc-user-dashboard.css';
//        $js          = Initializer::$js_url.'/public/sc-user-dashboard.js';
		$d3js        = '//d3js.org/d3.v3.min.js';
		$js_heatmap  = '//cdn.jsdelivr.net/cal-heatmap/3.3.10/cal-heatmap.min.js';
		$css_heatmap = '//cdn.jsdelivr.net/cal-heatmap/3.3.10/cal-heatmap.css';
		$js          = FileService::mp_get_js_url( 'main-admin' );
		$css         = FileService::mp_get_css_url( 'main-admin' );

		$js_dir      = Initializer::$plugin_url . '/assets2/vue-second/js';
		$js_admin_1  = $js_dir . '/admin/admin-1.js';
		$css_admin_1 = $js_dir . '/admin/admin-1.css';

		wp_register_style( 'admin-1122', $css_admin_1, [], Initializer::$script_version );
		wp_register_script( 'admin-1122', $js_admin_1, [ 'jquery' ], Initializer::$script_version, true );

		wp_register_style( 'sp-sc-user-dashboard', $css, [], Initializer::$script_version );
		wp_register_script( 'sp-sc-user-dashboard', $js, [ 'jquery' ], Initializer::$script_version, true );

		wp_register_script( 'sp-d3-js', $d3js, [ 'jquery' ], 1, true );
		wp_register_script( 'sp-js-heatmap', $js_heatmap, [ 'jquery' ], 1, true );
		wp_register_style( 'sp-css-heatmap', $css_heatmap, [], 1 );

		// enqueue the scripts
		add_action( 'sp_enqueue_scripts_sc_user_dashboard', function () use ( $dis ) {
			do_action( 'sp_enqueue_default_frontend_scripts' );
			wp_enqueue_style( 'sp-sc-user-dashboard' );
			wp_enqueue_script( 'sp-sc-user-dashboard' );

			wp_enqueue_script( 'sp-d3-js' );
			wp_enqueue_script( 'sp-js-heatmap' );
			wp_enqueue_style( 'sp-css-heatmap' );
			wp_enqueue_style( 'admin-1122');
			wp_enqueue_script( 'admin-1122');

			$dis->localize_data();
		} );
	}


}