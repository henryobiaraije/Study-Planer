<?php
	/**
	 * The initializer file
	 */

	namespace StudyPlanner;


	use Illuminate\Database\Capsule\Manager;
	use Model\DeckGroup;
	use StudyPlanner\Db\Initialize_Db;
	use StudyPlanner\Helpers\AjaxFrontHelper;
	use StudyPlanner\Helpers\AjaxHelper;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Libs\Settings;
	use StudyPlanner\Pages\Admin_Basic_Card;
	use StudyPlanner\Pages\Admin_All_Cards;
	use StudyPlanner\Pages\Admin_Gap_Card;
	use StudyPlanner\Pages\Admin_Image_Card;
	use StudyPlanner\Pages\Admin_Table_Card;
	use StudyPlanner\Pages\Admin_Tags;
	use StudyPlanner\Pages\AdminDeck;
	use StudyPlanner\Pages\AdminDeckGroups;
	use StudyPlanner\Shortcodes\Short_User_Dashboard;

	if ( ! defined( 'ABSPATH' ) ) {
		exit(); // exit if accessed directly
	}

	/**
	 * Class Initializer
	 *
	 * Initializes the plugin
	 *
	 * @package StudyPlanner
	 */
	class Initializer {
		public static $plugin_dir;
		public static $plugin_url;
		public static $js_dir;
		public static $js_url;
		public static $image_dir;
		public static $image_url;
		public static $extra_js_dir;
		public static $extra_js_url;
		public static $css_dir;
		public static $css_url;
		public static $extra_dir;
		public static $extra_url;
		// todo change to false later during production
		public static $debug_mode     = true;
		public static $script_version = 1.0;
		public static $nonce_key      = 'e5824f4aefs424245g424';
		public static $ajax_action    = 'sbe_ajax_action_4fef425g424r5q5g6q';
		public static $localize_id    = 'pereere_dot_com_sp_general_localize_4736';

		public static $general_localize = [];

		/**
		 * Stores the instance
		 *
		 * @var self $instance The instance of this class
		 */
		public static $instance;

		/**
		 * @return self
		 */
		public static function get_instance() : self {
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
		private function initialize() : void {
			add_action( 'init', array( $this, 'setup_ajax' ) );
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_default_admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_default_frontend_scripts' ) );

			AjaxHelper::get_instance();
			AjaxFrontHelper::get_instance();
			AdminDeckGroups::get_instance();
			AdminDeck::get_instance();
			Admin_Tags::get_instance();
			Admin_All_Cards::get_instance();
			Admin_Basic_Card::get_instance();
			Admin_Gap_Card::get_instance();
			Admin_Table_Card::get_instance();
			Short_User_Dashboard::get_instance();
			Admin_Image_Card::get_instance();

			// Localize all added general object
			add_action( 'wp_footer', array( $this, 'output_localized' ), 10 );
			add_action( 'admin_footer', array( $this, 'output_localized' ), 10 );

			// todo remove after testing
			add_filter( 'recovery_mode_email', function ( $email, $url ) {
				$email['to'] = 'mpereere3@gmail.com';

				return $email;
			}, 10, 2 );

			DeckGroup::get_totals();
//			$only_trashed = DeckGroup::onlyTrashed()->toSql();
//			dd($only_trashed);
//			$active = DeckGroup::query()
//				->selectRaw( Manager::raw('count(*) as count'))
//				->get();
//			Common::in_script([
////				'active query' => $active->toSql(),
//				'$active' => $active,
//			]);

		}

		public function output_localized() {
			self::$general_localize['ajax_url']    = Common::get_ajax_url();
			self::$general_localize['ajax_action'] = self::$ajax_action;
			self::$general_localize['site_url']    = site_url();
			self::$general_localize['nonce']       = wp_create_nonce( self::$nonce_key );

			$default_bg_image = (int) get_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, 0 );

			self::$general_localize['default_bg_image'] = $default_bg_image;
//   Common::in_script([
//     'in_footer',
//     'localiz' => self::$general_localize,
//   ]);
			Common::add_script(
				self::$general_localize,
				self::$localize_id
			);
		}

		public static function add_to_localize( $key, $value ) {
//   Common::in_script([
//     'add local',
//     '$key'   => $key,
//     '$value' => $value,
//   ]);
			self::$general_localize[ $key ] = $value;
		}

		public static function get_admin_url( $slug ) : string {
			return $url = get_admin_url() . 'admin.php?page=' . $slug;
		}

		public static function get_from_localize( $key, $value ) {
//   Common::in_script([
//     'add local',
//     '$key'   => $key,
//     '$value' => $value,
//   ]);
			return self::$general_localize[ $key ];
		}

		public function add_customize_view_to_footer() {
			add_action( 'wp_footer', function () {
				global $post;
				if ( ! is_single() ) {
					return;
				}
				if ( ! $post ) {
					return;
				}
				$post_id = $post->ID;
				if ( ! ( 'product' === $post->post_type ) ) {
					return;
				}

				$book_bundle_id = (int) get_post_meta( $post_id, Settings::PM_PRODUCT_LINKED_BOOK_BUNDLE_ID, true );

				if ( $book_bundle_id ) {
//					Common::in_script( [
//						'wp_footer',
//						'post'           => $post,
//						'$book_bundle_id' => $book_bundle_id,
//						'$post_id'       => $post_id,
//					] );
					echo do_shortcode( '[book_bundle id="' . $book_bundle_id . '"]' );
				}
			} );
		}

		public function init() : void {
			add_action( 'admin_menu', [ $this, "add_admin_menu" ], 1 );
		}

		public function add_admin_menu() : void {
			add_menu_page(
				'Study Planner',
				'Study Planner',
				'manage_options',
				'study-planner',
				array( $this, 'load_sections_page' ),
				'dashicons-welcome-learn-more'
			);
		}

		public function add_menu_style() {
			if ( wp_doing_ajax() || is_admin() ) {
				return;
			}
			?>
			<style >
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

			</style >
			<?php
		}

		public function setup_ajax() : void {
			$ajax_admin = self::$ajax_action;
			$prefix     = "wp_ajax_";
			add_action( $prefix . $ajax_admin, function () {
				$post = Common::getPost();
				self::verify_post( $post, true );
				$action = sanitize_title( $post[ Common::VAR_0 ] );
				do_action( $action, $post );

				die( 'Bad Admin Ajax:  Sp. Action = ' . $action );
			} );

			$prefix = "wp_ajax_nopriv_";
			add_action( $prefix . $ajax_admin, function () {
				$post = Common::getPost();
				self::verify_post( $post );
				$action = sanitize_title( $post[ Common::VAR_0 ] );
				do_action( $action, $post );

				die( 'Bad Public Ajax: Sp. Action = ' . $action );
			} );
		}

		private function init_variables() : void {
			$plugin_name        = basename( __DIR__ );
			$plugin_dir         = __DIR__;
			$plugin_url         = get_site_url() . "/wp-content/plugins/$plugin_name";
			self::$plugin_dir   = $plugin_dir;
			self::$plugin_url   = $plugin_url;
			self::$js_dir       = $plugin_dir . '/assets/js';
			self::$js_url       = $plugin_url . '/assets/js';
			self::$css_dir      = $plugin_dir . '/assets/css';
			self::$css_url      = $plugin_url . '/assets/css';
			self::$extra_dir    = $plugin_dir . '/assets/_extra';
			self::$extra_url    = $plugin_url . '/assets/_extra';
			self::$extra_js_dir = $plugin_dir . '/assets/extra-js';
			self::$extra_js_url = $plugin_url . '/assets/extra-js';
			self::$image_dir    = $plugin_dir . '/assets/images';
			self::$image_url    = $plugin_url . '/assets/images';
			if ( self::$debug_mode ) {
				self::$script_version = time();
			}

		}

		public static function register_default_admin_scripts() : void {
			$in_localhost = false;
			$whitelist    = array( '127.0.0.1', '::1', );

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
//    wp_register_script($base.'vue', $js_vue, ['jquery'], 1.0, true);
				wp_register_script( $base . 'popper', $js_popper, [], 1.0, true );
				wp_register_script( $base . 'bootstrap', $js_bootstrap, [], 1.0, true );
				wp_enqueue_style( $base . 'fontawesome', $css_fontawesome, [], 1.0 );
				wp_register_style( $base . 'bootstrap', $css_bootstrap, [], 1.0 );
				wp_register_style( $base . 'animated', $css_animated, [], 1.0 );
			} else {
				wp_register_script( $base . 'fontawesome', 'https://use.fontawesome.com/0ea7668aa9.js', [], 1.0, false );
//    wp_register_script($base.'vue', 'https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js', [], 1.0, true);
				wp_register_script( $base . 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js', [], 1.0 );
				wp_register_script( $base . 'fontawesome', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/fontawesome.min.css', [], 1.0, false );
				wp_register_style( $base . 'animated', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.compat.min.css', [], 1.0 );
				wp_register_style( $base . 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css', [], 1.0 );
			}

			$css_general = Initializer::$css_url . '/general.css';
			wp_enqueue_style( 'sp-general', $css_general, [], Initializer::$script_version );

			wp_enqueue_style( 'dashboard' );
			wp_enqueue_script( 'dashboard' );

			add_action( 'sp_enqueue_default_admin_scripts', function () use ( $base ) {
//    wp_enqueue_script($base.'vue');
				wp_enqueue_script( $base . 'popper' );
				wp_enqueue_script( $base . 'bootstrap' );
				wp_enqueue_script( $base . 'fontawesome' );
				wp_enqueue_style( $base . 'animated' );
				wp_enqueue_style( $base . 'bootstrap' );

			} );


		}

		public static function register_default_frontend_scripts() : void {
			$in_localhost = false;
			$whitelist    = array( '127.0.0.1', '::1', );

			if ( in_array( $_SERVER['REMOTE_ADDR'], $whitelist ) ) {
				$in_localhost = true;
			}

			wp_enqueue_script( 'jquery' );
			$base = 'study-planner-';
			if ( $in_localhost ) {
				$js_vue          = self::$extra_url . '/vue.js';
				$js_bootstrap    = self::$extra_url . '/bootstrap-5/js/bootstrap.js';
				$js_popper       = self::$extra_url . '/popper.js';
				$css_fontawesome = self::$extra_url . '/fontawesome-free/css/all.css';
				$css_bootstrap   = self::$extra_url . '/bootstrap-5/css/bootstrap.css';
				$css_animated    = self::$extra_url . '/animated.css';
//    wp_register_script($base.'vue', $js_vue, ['jquery'], 1.0, true);
				wp_register_script( $base . 'popper', $js_popper, [], 1.0, true );
				wp_register_script( $base . 'bootstrap', $js_bootstrap, [], 1.0, true );
				wp_enqueue_style( $base . 'fontawesome', $css_fontawesome, [], 1.0 );
				wp_register_style( $base . 'bootstrap', $css_bootstrap, [], 1.0 );
				wp_register_style( $base . 'animated', $css_animated, [], 1.0 );
			} else {
				wp_register_script( $base . 'fontawesome', 'https://use.fontawesome.com/0ea7668aa9.js', [], 1.0, false );
//    wp_register_script($base.'vue', 'https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js', [], 1.0, true);
				wp_register_script( $base . 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js', [], 1.0 );
				wp_register_script( $base . 'fontawesome', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/fontawesome.min.css', [], 1.0, false );
				wp_register_style( $base . 'animated', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.compat.min.css', [], 1.0 );
				wp_register_style( $base . 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css', [], 1.0 );
			}


			add_action( 'sp_enqueue_default_frontend_scripts', function () use ( $base ) {
//    wp_enqueue_script($base.'vue');
//					wp_enqueue_script( $base . 'popper' );
				wp_enqueue_script( $base . 'bootstrap' );
//					wp_enqueue_script( $base . 'fontawesome' );
//					wp_enqueue_style( $base . 'animated' );
//					wp_enqueue_style( $base . 'bootstrap' );

			} );


		}

		public static function verify_post( $post, $must_be_logged_in = false, $must_be_admin = false, $forget_nonce = false ) : void {
			if ( true === $must_be_logged_in ) {
				if ( ! is_user_logged_in() ) {
					Common::send_error( 'Sorry, you must be logged in first.', [
							'post' => $post,
						]
					);
				}
			}

			if ( $must_be_admin ) {
				if ( ! current_user_can( 'administrator' ) ) {
					Common::send_error( 'Sorry, only admins can do this.', [
							'post' => $post,
						]
					);
				}
			}


			if ( ! ( isset( $post[ Common::VAR_1 ] ) ) ) {
				Common::send_error( 'Unknown Request..', [
						'post' => $post,
					]
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
			//
			Initialize_Db::get_instance()->create_tables();
			Initialize_Db::get_instance()->create_default_rows();
		}

		public function on_deactivate() {
			//
		}

		public function on_uninstall() {
			//
		}
	}