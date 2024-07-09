<?php
/**
 * The initializer file
 */

namespace StudyPlannerPro;

use Illuminate\Database\Capsule\Manager;
use Model\DeckGroup;
use Model\Topic;
use StudyPlannerPro\Db\Initialize_Db;
use StudyPlannerPro\Helpers\AjaxFrontHelper;
use StudyPlannerPro\Helpers\AjaxHelper;
use StudyPlannerPro\Helpers\RunOnceHelpers;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Libs\Settings;
use StudyPlannerPro\Pages\Admin_Assign_Topic;
use StudyPlannerPro\Pages\Admin_Basic_Card;
use StudyPlannerPro\Pages\Admin_All_Cards;
use StudyPlannerPro\Pages\Admin_Collections;
use StudyPlannerPro\Pages\Admin_Gap_Card;
use StudyPlannerPro\Pages\Admin_Image_Card;
use StudyPlannerPro\Pages\Admin_Settings;
use StudyPlannerPro\Pages\Admin_Table_Card;
use StudyPlannerPro\Pages\Admin_Tags;
use StudyPlannerPro\Pages\AdminDeck;
use StudyPlannerPro\Pages\AdminDeckGroups;
use StudyPlannerPro\Pages\AdminTopics;
use StudyPlannerPro\Rest\Rest_File_Upload_Controller;
use StudyPlannerPro\Services\Debug_Data_Manager;
use StudyPlannerPro\Services\Log_Service;
use StudyPlannerPro\Shortcodes\Short_User_Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // exit if accessed directly
}

/**
 * Class Initializer
 *
 * Initializes the plugin
 *
 * @package StudyPlannerPro
 */
class Initializer {

	/**
	 * Debugging data.
	 * @var array $debug Debugging data.
	 */
	public static array $debug = array();

	/**
	 * @var bool $can_add_debug Whether to add debug data or not.
	 */
	public static bool $can_add_debug = true;

	/**
	 * Add debug data.
	 *
	 * @param array $value The value to add.
	 */
	public static function add_debug( array $value ): void {
		if ( ! self::$can_add_debug ) {
			return;
		}

		// Get the caller details using debug_backtrace.
		$caller = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 2 )[1];

		// Extract relevant information.
		$method = $caller['function'];
		$class  = $caller['class'] ?? null;
		$line   = $caller['line'];

		// Add caller details to the debug data.
		$key                 = count( self::$debug ) . '_' . time();
		self::$debug[ $key ] = array_merge( array(
			'__class'  => $class,
			'__method' => $method,
			'__line'   => $line,
		), $value );
	}


	/**
	 * Add debug data.
	 *
	 * @param array $value The value to add.
	 */
	public static function add_debug_with_key( string $key = '', array $value = array() ): void {
		if ( ! self::$can_add_debug ) {
			return;
		}

		// Get the caller details using debug_backtrace.
		$caller = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 2 )[1];

		// Extract relevant information.
		$method = $caller['function'];
		$class  = $caller['class'] ?? null;
		$line   = $caller['line'];

		// Add caller details to the debug data.

		if ( empty( $key ) ) {
			$key = count( self::$debug ) . '_' . time();
		} else {
			$key = count( self::$debug ) . '_' . $key;
		}
		self::$debug[ $key ] = array_merge( array(
			'__class'  => $class,
			'__method' => $method,
			'__line'   => $line,
		), $value );
	}

	public static $plugin_dir;
	public static $plugin_url;
	public static $js_dir;
	public static $js_url;
	public static $image_dir;
	public static $image_url;
	public static $extra_js_dir;
	public static $extra_js_url;
	public static $css_dir;
	public static $plugin_name;
	public static $css_url;
	public static $extra_dir;
	public static $extra_url;
	// todo change to false later during production
	public static $debug_mode = true;
	public static $script_version = '5.0.2';
	public static $nonce_key = 'e5824f448200111383345g424';
	public static $ajax_action = 'sp_pro_ajax_action_4fef425g424r5q5g6q';
	public static $localize_id = 'pereere_dot_com_sp_pro_general_localize_4736';

	public static array $general_localize = array();

	/**
	 * An instance of the Log Service.
	 *
	 * @var null|Log_Service $log_service An instance of the Log Service.
	 */
	public ?Log_Service $log_service = null;

	/**
	 * Stores the instance
	 *
	 * @var self $instance The instance of this class
	 */
	public static ?Initializer $instance = null;

	/**
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null !== self::$instance ) {
			return self::$instance;
		}
		self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		$this->init_variables();
		$this->initialize();
	}

	/**
	 * Initialize everything
	 */
	private function initialize(): void {
		add_action( 'init', array( $this, 'setup_ajax' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_default_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_default_frontend_scripts' ) );

		RunOnceHelpers::get_instance();
		AjaxHelper::get_instance();
		AjaxFrontHelper::get_instance();
		AdminDeckGroups::get_instance();
		AdminDeck::get_instance();
		AdminTopics::get_instance();
		Admin_Tags::get_instance();
		Admin_All_Cards::get_instance();
		Admin_Basic_Card::get_instance();
		Admin_Gap_Card::get_instance();
		Admin_Table_Card::get_instance();
		Short_User_Dashboard::get_instance();
		Admin_Image_Card::get_instance();
		Admin_Settings::get_instance();
		Admin_Collections::get_instance();
		Admin_Assign_Topic::get_instance();
		( new Debug_Data_Manager() )->init();

		( new Rest_File_Upload_Controller() )->init();

		$this->initialize_services();

		// Localize all added general object
		add_action( 'wp_footer', array( $this, 'output_localized' ), 10 );
		add_action( 'admin_footer', array( $this, 'output_localized' ), 10 );

		// todo remove after testing
		add_filter(
			'recovery_mode_email',
			function ( $email, $url ) {
				$email['to'] = 'mpereere3@gmail.com';

				return $email;
			},
			10,
			2
		);

//		DeckGroup::get_totals();
		// $only_trashed = DeckGroup::onlyTrashed()->toSql();
		// dd($only_trashed);
		// $active = DeckGroup::query()
		// ->selectRaw( Manager::raw('count(*) as count'))
		// ->get();
		// Common::in_script([
		// 'active query' => $active->toSql(),
		// '$active' => $active,
		// ]);
	}

	/**
	 * Initialize services.
	 *
	 * @return void
	 */
	public function initialize_services(): void {
		$this->log_service = new Log_Service( self::$plugin_dir . '/logs.log' );
	}

	public function get_plugin_version(): string {
		// return wp current plugin version.
		$plugin_data = get_plugin_data( __FILE__ );

		return $plugin_data['Version'];
	}

	public function output_localized() {
		self::$general_localize['ajax_url']    = Common::get_ajax_url();
		self::$general_localize['ajax_action'] = self::$ajax_action;
		self::$general_localize['site_url']    = site_url();
		self::$general_localize['nonce']       = wp_create_nonce( self::$nonce_key );
		self::$general_localize['rest_nonce']  = wp_create_nonce( 'wp_rest' );

		$default_bg_image = (int) get_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, 0 );

		self::$general_localize['default_bg_image'] = $default_bg_image;
		// C:\laragon\www\Test-Site-Wordpress\wp-content\plugins\study-planner-pro\assets2\vue-second\images\settings-small.png
		self::$general_localize['icon_settings_image'] = self::$plugin_url . '/assets2/vue-second/images/settings-small.png';
        self::$general_localize['is_admin'] = current_user_can( 'administrator' );
		// Common::in_script([
		// 'in_footer',
		// 'localiz' => self::$general_localize,
		// ]);
		Common::add_script(
			self::$general_localize,
			self::$localize_id
		);
	}

	public static function add_to_localize( $key, $value ) {
		// Common::in_script([
		// 'add local',
		// '$key'   => $key,
		// '$value' => $value,
		// ]);
		self::$general_localize[ $key ] = $value;
	}

	public static function get_admin_url( $slug ): string {
		return $url = get_admin_url() . 'admin.php?page=' . $slug;
	}

	public static function get_from_localize( $key, $value ) {
		// Common::in_script([
		// 'add local',
		// '$key'   => $key,
		// '$value' => $value,
		// ]);
		return self::$general_localize[ $key ];
	}

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 1 );
	}

	public function add_admin_menu(): void {
		add_menu_page(
			'Study Planner Pro',
			'Study Planner Pro',
			'manage_options',
			'study-planner-pro',
			array( $this, 'load_sections_page' ),
			'dashicons-welcome-learn-more'
		);

		// Remove the first menu item.
		remove_submenu_page( 'study-planner-pro', 'study-planner-pro' );
	}

	public function add_menu_style() {
		if ( wp_doing_ajax() || is_admin() ) {
			return;
		}
		?>
        <style>
            .site-primary-header-wrap {
                margin: 0;
            }

            /*.site-header-primary-section-right site-header-section ast-flex ast-grid-right-section{*/
            /*    */
            /*}*/
            @media (min-width: 900px) {
                .site-header-primary-section-left.site-header-section.ast-flex.site-header-section-left {
                    justify-content: flex-start;
                }

                .site-header-primary-section-right.site-header-section.ast-flex.ast-grid-right-section {
                    justify-content: flex-start;
                }
            }

        </style>
		<?php
	}

	public function setup_ajax(): void {
		$ajax_admin = self::$ajax_action;
		$prefix     = 'wp_ajax_';
		add_action(
			$prefix . $ajax_admin,
			function () {
				$post = Common::getPost();
				self::verify_post( $post, true );
				$action = sanitize_title( $post[ Common::VAR_0 ] );
//				Common::send_success( 'error', [
//					'action' => $action,
//				] );
				do_action( $action, $post );

				die( 'Bad Admin Ajax:  Sp. Action = ' . $action );
			}
		);

		$prefix = 'wp_ajax_nopriv_';
		add_action(
			$prefix . $ajax_admin,
			function () {
				$post = Common::getPost();
				self::verify_post( $post );
				$action = sanitize_title( $post[ Common::VAR_0 ] );

				do_action( $action, $post );

				die( 'Bad Public Ajax: Sp. Action = ' . $action );
			}
		);
	}

	private function init_variables(): void {
		$plugin_name       = basename( __DIR__ );
		$plugin_dir        = __DIR__;
		$plugin_url        = get_site_url() . "/wp-content/plugins/$plugin_name";
		self::$plugin_dir  = $plugin_dir;
		self::$plugin_url  = $plugin_url;
		self::$plugin_name = $plugin_name;
		// self::$js_dir       = $plugin_dir . '/assets/js';
		self::$js_dir = $plugin_dir . '/assets2/vue-project/dist/assets';
		// self::$js_url       = $plugin_url . '/assets/js';
		self::$js_url       = $plugin_url . '/assets2/vue-project/dist/assets';
		self::$css_dir      = $plugin_dir . '/assets/css';
		self::$css_url      = $plugin_url . '/assets/css';
		self::$extra_dir    = $plugin_dir . '/assets/_extra';
		self::$extra_url    = $plugin_url . '/assets/_extra';
		self::$extra_js_dir = $plugin_dir . '/assets/extra-js';
		self::$extra_js_url = $plugin_url . '/assets/extra-js';
		self::$image_dir    = $plugin_dir . '/assets/images';
		self::$image_url    = $plugin_url . '/assets/images';
		if ( self::$debug_mode ) {
//			self::$script_version = time();
		}
	}

	public static function register_default_admin_scripts(): void {
		$in_localhost = false;
		$whitelist    = array( '127.0.0.1', '::1' );

		if ( in_array( $_SERVER['REMOTE_ADDR'], $whitelist ) ) {
			$in_localhost = true;
		}

		wp_enqueue_script( 'jquery' );
		$base = 'SearchBarEndpoints';
		if ( $in_localhost ) {
			$js_vue          = self::$extra_url . '/vue.js';
			$js_bootstrap    = self::$extra_url . '/bootstrap-5/js/bootstrap.js';
			$js_popper       = self::$extra_url . '/popper.js';
			$css_fontawesome = self::$extra_url . '/fontawesome-free/css/all.css';
			$css_bootstrap   = self::$extra_url . '/bootstrap-5/css/bootstrap.css';
			$css_animated    = self::$extra_url . '/animated.css';
			// wp_register_script($base.'vue', $js_vue, ['jquery'], 1.0, true);
			wp_register_script( $base . 'popper', $js_popper, array(), 1.0, true );
			wp_register_script( $base . 'bootstrap', $js_bootstrap, array(), 1.0, true );
			wp_enqueue_style( $base . 'fontawesome', $css_fontawesome, array(), 1.0 );
			wp_register_style( $base . 'bootstrap', $css_bootstrap, array(), 1.0 );
			wp_register_style( $base . 'animated', $css_animated, array(), 1.0 );
		} else {
			wp_register_script( $base . 'fontawesome',
				'https://use.fontawesome.com/0ea7668aa9.js',
				array(),
				1.0,
				false );
			// wp_register_script($base.'vue', 'https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js', [], 1.0, true);
			wp_register_script( $base . 'bootstrap',
				'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js',
				array(),
				1.0 );
			wp_register_script(
				$base . 'fontawesome',
				'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/fontawesome.min.css',
				array(),
				1.0,
				false
			);
			wp_register_style( $base . 'animated',
				'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.compat.min.css',
				array(),
				1.0 );
			wp_register_style( $base . 'bootstrap',
				'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css',
				array(),
				1.0 );
		}

		$css_general = self::$css_url . '/general.css';
		// wp_enqueue_style('sp-general', $css_general, [], Initializer::$script_version);

		wp_enqueue_style( 'dashboard' );
		wp_enqueue_script( 'dashboard' );

		add_action(
			'sp_enqueue_default_admin_scripts',
			function () use ( $base ) {
				// wp_enqueue_script($base.'vue');
				wp_enqueue_script( $base . 'popper' );
				wp_enqueue_script( $base . 'bootstrap' );
				wp_enqueue_script( $base . 'fontawesome' );
				wp_enqueue_style( $base . 'animated' );
				wp_enqueue_style( $base . 'bootstrap' );
			}
		);
	}

	public static function register_default_frontend_scripts(): void {
		$in_localhost = false;
		$whitelist    = array( '127.0.0.1', '::1' );

		if ( in_array( $_SERVER['REMOTE_ADDR'], $whitelist ) ) {
			$in_localhost = true;
		}

		wp_enqueue_script( 'jquery' );
		$base = 'study-planner-pro-';
		if ( $in_localhost ) {
			$js_vue          = self::$extra_url . '/vue.js';
			$js_bootstrap    = self::$extra_url . '/bootstrap-5/js/bootstrap.js';
			$js_popper       = self::$extra_url . '/popper.js';
			$css_fontawesome = self::$extra_url . '/fontawesome-free/css/all.css';
			$css_bootstrap   = self::$extra_url . '/bootstrap-5/css/bootstrap.css';
			$css_animated    = self::$extra_url . '/animated.css';
			// wp_register_script($base.'vue', $js_vue, ['jquery'], 1.0, true);
			wp_register_script( $base . 'popper', $js_popper, array(), 1.0, true );
			wp_register_script( $base . 'bootstrap', $js_bootstrap, array(), 1.0, true );
			wp_enqueue_style( $base . 'fontawesome', $css_fontawesome, array(), 1.0 );
			wp_register_style( $base . 'bootstrap', $css_bootstrap, array(), 1.0 );
			wp_register_style( $base . 'animated', $css_animated, array(), 1.0 );
		} else {
			wp_register_script( $base . 'fontawesome',
				'https://use.fontawesome.com/0ea7668aa9.js',
				array(),
				1.0,
				false );
			// wp_register_script($base.'vue', 'https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js', [], 1.0, true);
			wp_register_script( $base . 'bootstrap',
				'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js',
				array(),
				1.0 );
			wp_register_script(
				$base . 'fontawesome',
				'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/fontawesome.min.css',
				array(),
				1.0,
				false
			);
			wp_register_style( $base . 'animated',
				'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.compat.min.css',
				array(),
				1.0 );
			wp_register_style( $base . 'bootstrap',
				'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css',
				array(),
				1.0 );
		}

		add_action(
			'sp_enqueue_default_frontend_scripts',
			function () use ( $base ) {
				// wp_enqueue_script($base.'vue');
				// wp_enqueue_script( $base . 'popper' );
				wp_enqueue_script( $base . 'bootstrap' );
				// wp_enqueue_script( $base . 'fontawesome' );
				// wp_enqueue_style( $base . 'animated' );
				// wp_enqueue_style( $base . 'bootstrap' );
			}
		);
	}

	public static function verify_post(
		$post,
		$must_be_logged_in = false,
		$must_be_admin = false,
		$forget_nonce = false
	): void {
		if ( true === $must_be_logged_in ) {
			if ( ! is_user_logged_in() ) {
				Common::send_error(
					'Sorry, you must be logged in first.',
					array(
						'post' => $post,
					)
				);
			}
		}

		if ( $must_be_admin ) {
			if ( ! current_user_can( 'administrator' ) ) {
				Common::send_error(
					'Sorry, only admins can do this.',
					array(
						'post' => $post,
					)
				);
			}
		}

		if ( ! ( isset( $post[ Common::VAR_1 ] ) ) ) {
			Common::send_error(
				'Unknown Request..',
				array(
					'post' => $post,
				)
			);
		}
		if ( ! $forget_nonce || $must_be_admin ) {
			$nonce = $post[ Common::VAR_1 ];
			if ( ! wp_verify_nonce( $nonce, self::$nonce_key ) ) {
				Common::send_error( 'Session Expired. Please reload page.' );
			}
		}
	}

	public function on_activate() {
//		phinx_migrate();
//		Topic::make_sure_card_groups_with_real_topic_also_have_the_right_parent_deck();
		//
		// Initialize_Db::get_instance()->create_tables();
		// Initialize_Db::get_instance()->create_default_rows();
	}

	public function on_deactivate() {
	}

	public function on_uninstall() {
	}
}
