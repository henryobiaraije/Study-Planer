<?php
	/**
	 *  Plugin. Common functions.
	 *
	 * @class   Common
	 * @package StudyPlanner/Includes/Libs
	 */

	namespace StudyPlanner\Libs;

	defined( 'ABSPATH' ) || exit;

	/**
	 * Common Processing class
	 *
	 * @author MACHINE PEREERE Contact: mpereere@gmail.com
	 * Created on : Dec 26, 2019, 1:25:50 PM
	 */
	class Common {

		public static $hold_foot_script   = [];
		public static $hold_footer_script = [];
		public static $online_now         = false;
		public static $plugin_name        = "";

		/** Date settings */
		public static $dates_according_to_ip = false;

		/**
		 * Holds Default Plugin Directory
		 *
		 * @var string
		 */
		public static $plugin_dir = '';
		public static $images_dir = '';
		public static $assets_dir = '';
		public static $fonts_dir  = '';
		public static $fonts_url  = '';
		public static $plugin_url = "";
		public static $nonce_key  = "gase4g4g2f";

		public static $online_start_count = 1;
		/**  Development Or Production */
		public static $script_version = 1.0;
		public static $in_development = true;

		/* IMG */
		private static $instance = null;

		function __construct( $dates_according_to_ip = false ) {

			if ( $_SERVER["SERVER_NAME"] === "localhost" ) {
				//      self::$SCRIPT_VERSION = time();
			} else {
				self::$online_now = true;
			}
			if ( isset( $_SERVER['SERVER_NAME'] ) ) {
				if ( $_SERVER["SERVER_NAME"] === "localhost" ) {

				} else {
					self::$online_start_count = 0;
				}
			}

			if ( self::$online_now ) {

			}

//		Common::in_script( [
//			'online now' => self::$ONLINE_NOW,
//		] );
			$this->init_constants();
		}

		/**
		 * GetContents Obstart & Loads file & Obclean
		 *
		 * @param  string  $filename  The file name.
		 *
		 * @return string file content
		 */
		public static function get_contents( $filename ) {
			ob_start();
			require $filename;
			$code = ob_get_clean();
			//		$code = ob_get_contents();
			//		ob_end_clean();
			return $code;
		}

		public static function getDbArray( $get, $look_for ) {
			return isset( $get[ $look_for ] ) ? [ $get ] : $get;
		}

		public static function getInstance() {
			if ( self::$instance === null ) {
				self::$instance = new Common();
			}

			return self::$instance;
		}


		public static function verify_post( $post, $must_be_logged_in = false, $must_be_admin = false, $forget_nonce = false ) {
			if ( $must_be_admin ) {
				if ( ! current_user_can( 'administrator' ) ) {
					Common::send_error( 'Sorry, only admins can do this.', [
						'post' => $post,
					] );
				}
			}

			if ( true === $must_be_logged_in ) {
				if ( ! is_user_logged_in() ) {
					Common::send_error( 'Sorry, you must be logged in first.', [
						'post' => $post,
					] );
				}
			}

			if ( ! ( isset( $post[ Common::VAR_1 ] ) ) ) {
				Common::send_error( 'Unknown Request..', [
					'post' => $post,
				] );
			}
			if ( ! $forget_nonce || $must_be_admin ) {
				$nonce = $post[ Common::VAR_1 ];
				if ( ! wp_verify_nonce( $nonce, StudyPlanner::$nonce_key ) ) {
					Common::send_error( 'Session Expired. Please reload page.' );
				}
			}


		}

		private function init_constants() {
			self::$plugin_name = 'book-cover-builder-pro';
			self::$assets_dir  = ABSPATH . "wp-content/plugins/" . self::$plugin_name . '/assets';
			self::$plugin_dir  = ABSPATH . "wp-content/plugins/" . self::$plugin_name;
			self::$images_dir  = get_site_url() . "/wp-content/plugins/" . self::$plugin_name . '/assets/images';
			self::$fonts_url   = get_site_url() . "/wp-content/plugins/" . self::$plugin_name . '/assets/fonts';
			self::$plugin_url  = get_site_url() . "/wp-content/plugins/" . self::$plugin_name;
			if ( self::$in_development ) {
				self::$script_version = time();
			}

			add_action( 'mp_wr_good', [ $this, 'send_good' ], 10, 2 );
			add_action( 'mp_wr_bad', [ $this, 'send_bad' ], 10, 2 );
		}

		public static function money_format( $float, $decimal_place = 2, $decimal_point = '.' ) {
			$float = (float) $float;

			return number_format( $float, $decimal_place, $decimal_point, ',' );
		}

		public static function get_ajax_url() {
			return get_admin_url( 'wcmv-admin.js' ) . "admin-ajax.php";;
		}


		public static function in_script(
			$data_to_assign,
			$var_name = null,
			$echo_in_footer = false
		) {
			if ( $var_name === null ) {
				$var_name = "mppr_" . rand( 443, 4857839 );
			}
			// var_dump($data_to_assign);
			$ff = "<script>";
			$ff .= " var " . $var_name . " = " . wp_json_encode( $data_to_assign ) . "; ";
			$ff .= " console.log(" . $var_name . "); ";
			$ff .= "</script>";
			if ( $echo_in_footer === true ) {
				self::$hold_footer_script = " " . $ff . " ";
			} else {
				echo $ff;
			}
		}

		/**
		 * Checks if the current request is a WP REST API request.
		 *
		 * Case #1: After WP_REST_Request initialisation
		 * Case #2: Support "plain" permalink settings and check if `rest_route` starts with `/`
		 * Case #3: It can happen that WP_Rewrite is not yet initialized,
		 *          so do this (wp-settings.php)
		 * Case #4: URL Path begins with wp-json/ (your REST prefix)
		 *          Also supports WP installations in subfolders
		 *
		 * @returns boolean
		 * @author matzeeable
		 */
		public static function is_rest() {
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST // (#1)
			     || isset( $_GET['rest_route'] ) // (#2)
			        && strpos( $_GET['rest_route'], '/', 0 ) === 0 ) {
				return true;
			}

			// (#3)
			global $wp_rewrite;
			if ( $wp_rewrite === null ) {
				$wp_rewrite = new WP_Rewrite();
			}

			// (#4)
			$rest_url    = wp_parse_url( trailingslashit( rest_url() ) );
			$current_url = wp_parse_url( add_query_arg( array() ) );

			return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
		}

		public static function add_script( $data_to_assign, $var_name ) {
			if ( wp_doing_ajax() || self::is_rest() ) {
				return;
			}
			$ff = "<script>";
			$ff .= " var " . $var_name . " = " . wp_json_encode( $data_to_assign ) . "; ";
			$ff .= "</script>";
			echo $ff;

		}

		// Function to get the client IP address
		public static function get_ip_address() {
			$ipaddress = '';
			if ( getenv( 'HTTP_CLIENT_IP' ) ) {
				$ipaddress = getenv( 'HTTP_CLIENT_IP' );
			} else {
				if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
					$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
				} else {
					if ( getenv( 'HTTP_X_FORWARDED' ) ) {
						$ipaddress = getenv( 'HTTP_X_FORWARDED' );
					} else {
						if ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
							$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
						} else {
							if ( getenv( 'HTTP_FORWARDED' ) ) {
								$ipaddress = getenv( 'HTTP_FORWARDED' );
							} else {
								if ( getenv( 'REMOTE_ADDR' ) ) {
									$ipaddress = getenv( 'REMOTE_ADDR' );
								} else {
									$ipaddress = 'UNKNOWN';
								}
							}
						}
					}
				}
			}

			return $ipaddress;
		}

		public static function eko( $data, $message = "" ) {
			self::getInstance()->send_bad( $data, $message );
		}

		public static function post_already_exist( $post_title, $post_type ) {
			$exists_query = new \WP_Query( [
				'name'          => $post_title,
				'post_per_page' => 1,
				'post_status'   => 'publish',
				'post_type'     => $post_type,
			] );
			if ( ! empty( $exists_query->get_posts() ) ) {
				return true;
			}

			return false;
		}

		public static function post_already_exist_in_parent( $post_title, $post_type, $parent_id ) {
			$ret          = false;
			$exists_query = new \WP_Query( [
				'name'          => $post_title,
				'post_per_page' => 1,
				'post_status'   => 'publish',
				'post_type'     => $post_type,
				//				'post_parent'   => $parent_id,
			] );

			foreach ( $exists_query->get_posts() as $post ) {
				if ( $post->post_parent == $parent_id ) {
					$ret = true;
				}
			}

//			Common::send_failure( [
//				'post_already_exist_in_parent',
//				'exist_query' => $exists_query,
//				'$ret' => $ret,
//				'by_name' => get_page_by_title($post_title,ARRAY_A,$post_type),
//			] );
			return $ret;
		}

		public static function post_already_exist_in_parent2( $post_title, $post_type, $parent_id ) {
			$ret          = false;
			$exists_query = new \WP_Query( [
				'name'          => $post_title,
				'post_per_page' => 1,
				'post_status'   => 'publish',
				'post_type'     => $post_type,
				//				'post_parent'   => $parent_id,
			] );

			foreach ( $exists_query->get_posts() as $post ) {
				if ( $post->post_parent == $parent_id ) {
					$ret = true;
				}
			}

			Common::send_error( [
				'post_already_exist_in_parent',
				'exist_query' => $exists_query,
				'$ret'        => $ret,
				'by_name'     => get_page_by_title( $post_title, ARRAY_A, $post_type ),
			] );

			return $ret;
		}

		public static function post_already_exist_excluded( $post_title, $post_type, $excluded_post_id ) {
			$exists_query = new \WP_Query( [
				'name'          => $post_title,
				'post_per_page' => 1,
				'post_status'   => 'publish',
				'post_type'     => $post_type,
				'post__not_in'  => [ $excluded_post_id ],
			] );
			if ( ! empty( $exists_query->get_posts() ) ) {
				return true;
			}

			return false;
		}

		public static function in_script_no_var( $statment ) {
			$ff = "<script>";
			$ff .= $statment;
			$ff .= "</script>";
			echo $ff;
		}

		public static function in_script_footer( $data_to_assign, string $var_name = null, $echo_in_footer = false ) {
			if ( $var_name === null ) {
				$var_name = "mppr_" . rand( 443, 4857839 );
			}
			// var_dump($data_to_assign);
			$ff = "<script>";
			$ff .= " var " . $var_name . " = " . json_encode( $data_to_assign ) . "; ";
			$ff .= " console.log(" . $var_name . "); ";
			$ff .= "</script>";
			// if ($echo_in_footer === TRUE) {
			self::$hold_footer_script[] = $ff;
			// } else {
			//   echo $ff;
			// }
		}

		public static function getPost() {
			$data = json_decode( trim( preg_replace( '/\\\"/', "\"",
				$_POST["form_data"] ) ), true );
			if ( $data == null ) {
				$data = json_decode( stripslashes( $_POST["form_data"] ), true );
			}

			//    self::sendFailure(['data' => $data]);
			return $data['data'];
		}

		public static function echo_footer_scripts() {
			// die(self::$HOLD_FOOTER_SCRIPT);
			foreach ( self::$hold_footer_script as $value ) {
				echo $value;
			}
		}

		public static function in_pre( $a ) {
			$b = [ "<pre>", $a, "</pre>" ];
			var_dump( $b );

			return $b;
		}

		public static function get_page_url( $title ) {
			$page = get_page_by_title( $title );
			$url  = "";
			if ( $page ) {
				$page_id = $page->ID;
				$url     = get_permalink( $page_id );
				if ( empty( $url ) ) {
					$url = $page->guid;
				}
			}
			$url = trim( $url );

			return $url;
		}

		public static function get_page_url_by_id( $page_id ) {

			$url = get_permalink( $page_id );
			if ( empty( $url ) ) {
				$page = get_post( $page_id );
				$url  = $page->guid;
			}

			$url = trim( $url );

			return $url;
		}

		public static function send_success(
			$message,
			$data = [],
			$extra = [],
			$use = []
		) {
			self::sendOutStatic( self::$S_F_SUCCESS, $data, $message, $extra, $use = [] );
			//    add_action('mp_wr_good', [$this, 'send_good'], 10, 2);
		}

		public
		static function send_error(
			$message,
			$data = []
		) {
			//    self::sendOut(self::$S_F_FAILURE, $string);
			//    add_action('mp_wr_bad', [$this, 'send_bad'], 10, 2);
			self::sendOutStatic( self::$S_F_FAILURE, $data, $message );
		}

		public
		static function delete_chile_pages(
			$parent_page_name
		) {
			$parent_page = get_page_by_title( $parent_page_name );
			$args        = array(
				'post_parent' => $parent_page->ID,
				'post_type'   => 'page',
			);
			$posts       = get_posts( $args );
			//			Common::sendFailure( [
			//				'posts' => $posts,
			//			] );
			if ( is_array( $posts ) && count( $posts ) > 0 ) {
				foreach ( $posts as $po ) {
					wp_delete_post( $po->ID, true );
				}
			}
		}

		public
		static function delete_page(
			$page_name = '',
			$page_id = - 1
		) {
			if ( $page_id > - 1 ) {
				wp_delete_post( $page_id, true );

				return;
			}
			$post = get_page_by_title( $page_name );
			if ( $post ) {
				wp_delete_post( $post->ID, true );
			}
		}

		/**
		 * @param  string  $page_name
		 * @param  string  $parent_page_name
		 * @param  array   $meta  value pairs of meta and values
		 */
		public
		static function create_page(
			$page_name,
			$parent_page_name = '',
			$meta_values = []
		) {
			$new_page_id  = false;
			$post_details = [];

			if ( strlen( $parent_page_name ) > 2 ) {
				$parent_page = get_page_by_title( Start::PAGE_HOTEL );
				if ( $parent_page !== null ) {
					$post_details['post_parent'] = $parent_page->ID;
				}
			}
			if ( count( $meta_values ) > 0 ) {
				$post_details['meta_input'] = $meta_values;
			}

			$page = get_page_by_title( $page_name );
			if ( null === $page ) {
				$post_details = [
					'post_title'   => $page_name,
					'post_content' => '',
					'post_status'  => 'publish',
					'post_author'  => 1,
					'post_type'    => 'page',
				];
				$new_page_id  = wp_insert_post( $post_details );
			}

			return $new_page_id;
		}

		public static function sendSuccess( $string, array $array ) {
		}

		public
		function send_good(
			$data,
			$message
		) {
			// var_dump(['start',$data]);
			$arr                       = [];
			$arr[ self::$S_F_STATUS ]  = self::$S_F_SUCCESS;
			$arr[ self::$S_F_DATA ]    = $data;
			$arr[ self::$S_F_MESSAGE ] = $message;
			echo( json_encode( $arr ) );
			die;
		}

		public
		function send_bad(
			$data,
			$message
		) {
			// var_dump(['start',$data]);
			$arr                       = [];
			$arr[ self::$S_F_STATUS ]  = self::$S_F_FAILURE;
			$arr[ self::$S_F_DATA ]    = $data;
			$arr[ self::$S_F_MESSAGE ] = $message;
			echo( json_encode( $arr ) );
			die;
		}

		public
		static function sendOutStatic(
			$status,
			$data,
			$message,
			$extra = [],
			$use = []
		) {
			// var_dump(['start',$data]);
			$arr                                       = [];
			$arr[ self::$S_F_STATUS ]                  = $status;
			$arr[ self::$S_F_DATA ]                    = $data;
			$arr[ self::$S_F_MESSAGE ]                 = $message;
			$arr[ 'ghi30' . rand( 20, 483 ) . time() ] = $extra;
			$arr[ 'use_' . rand( 20, 764 ) . time() ]  = $use;
			echo( json_encode( $arr ) );
			die;
		}

		public
		function sendOut(
			$status,
			$data,
			$message
		) {
			// var_dump(['start',$data]);
			$arr                       = [];
			$arr[ self::$S_F_STATUS ]  = $status;
			$arr[ self::$S_F_DATA ]    = $data;
			$arr[ self::$S_F_MESSAGE ] = $message;
			echo( json_encode( $arr ) );
			die;
		}

		/**
		 * @param  int  $days  e.g. -1 for yesterday or +1 for tomorrow
		 *
		 * @return false|string
		 */
		public static function getDateTime( $days = 0, $hours = 0 ) {

			$date = date( 'Y-m-d H:i:s' );
			if ( $days !== 0 ) {
				$days = "$days days";
				$date = date( 'Y-m-d H:i:s', strtotime( $days ) );
			}
			if ( $hours !== 0 ) {
				$date = date( 'Y-m-d H:i:s', strtotime( $date ) + 60 * 60 );
			}

			return $date;

		}
		public static function format_datetime( $date ) {
			return date( 'Y-m-d H:i:s', strtotime( $date ) );

		}

		/**
		 * @param  int     $days
		 * @param  string  $format
		 *
		 * @return false|string
		 */
		public
		static function getDate(
			$days = 0,
			$format = 'Y-m-d'
		) {
			$date = date( $format );
			if ( $days !== 0 ) {
				$days = "$days days";
				$date = date( $format, strtotime( $days ) );
			}

			return $date;
		}

		public
		static function getTime() {

			return date( 'H:i:s' );
		}

		public
		static function getJunk(
			$deviceId = "nothing"
		) {
			$time       = time();
			$time_split = str_split( $time, 2 );
			$loop       = rand( 1, 5 );
			list( $_one, $_two, $_three, $_four, $_five ) = $time_split;
			if ( $loop == 1 ) {
				list( $_four, $_one, $_two, $_three, $_five ) = $time_split;
			} elseif ( $loop == 2 ) {
				list( $_two, $_one, $_three, $_four, $_five ) = $time_split;
			} elseif ( $loop == 3 ) {
				list( $_two, $_three, $_one, $_four, $_five ) = $time_split;
			} elseif ( $loop == 4 ) {
				list( $_two, $_three, $_four, $_one, $_five ) = $time_split;
			} elseif ( $loop == 5 ) {
				list( $_one, $_two, $_four, $_three, $_five ) = $time_split;
			}

			$time = time();
			$a    = rand( 1, 9999 );
			$b    = rand( 77, 4623 );
			$c    = md5( Common::getDateTime() );
			$f    = sha1( md5( Common::getDateTime() ) );
			$g    = sha1( strtotime( "now" ) );
			$e    = md5( sha1( rand( 15, 3425 ) ) );
			$d    = sha1( $deviceId );
			$dId  = sha1( md5( sha1( $deviceId . rand( 0, 8234 ) ) ) );
			$lg   = sha1( log( 4567.08794038 ) );
			$h    = md5( sha1( md5( sha1( rand( 12345, 253 ) + rand( 0,
					2211 ) ) ) ) );
			$ret  = sha1( md5( $a ) ) . md5( sha1( $b ) ) . $_one . $c . $d . $_two . $e . $_three . $f . $g . $dId . $_four . $lg . $_five . $h;
			$key  = "Key";
			//    $rrrr = strip_tags(stripslashes(stripcslashes(rtrim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $ret, MCRYPT_MODE_ECB))))));
			$rrrr = strip_tags( stripslashes( stripcslashes( rtrim( base64_encode( $ret ) ) ) ) );
			$rrrr = str_replace( "+", "", $rrrr );
			$rrrr = str_replace( "/", "", $rrrr );
			//    var_dump($rrrr);
			$rrrr = str_shuffle( $rrrr );

			return $rrrr;
		}

		public
		static function getJunkSmall(
			$length = 10
		) {
			//    $a = rand(0.56, 789504);
			//    $b = md5(rand(4, 18) . sha1($id));
			//    $d = sha1(C::getDateTime());
			//    $ret = $a . $b . $d;
			//    return $ret;
			$id = "YieH";

			$junk = self::getJunk( $id );
			$junk = str_replace( '0', '', $junk );
			$junk = str_replace( 'O', '', $junk );
			$junk = str_replace( 'I', '', $junk );
			$junk = str_replace( '1', '', $junk );
			$junk = str_replace( 'l', '', $junk );
			//    $sub = substr($junk, rand(0, 50), rand(40, 60));
			$sub = substr( $junk, 0, $length );

			return $sub;
		}

		public
		static function is_valid_post_variables(
			$post,
			$number
		) {
			$final    = false;
			$allValus = [
				self::VAR_0,
				self::VAR_1,
				self::VAR_2,
				self::VAR_3,
				self::VAR_4,
				self::VAR_5,
				self::VAR_6,
				self::VAR_7,
				self::VAR_8,
				self::VAR_9,
				self::VAR_10,
				self::VAR_11,
				self::VAR_12,
				self::VAR_13,
				self::VAR_14,
				self::VAR_15,
				self::VAR_16,
				self::VAR_17,
				self::VAR_18,
				self::VAR_19,
				self::VAR_20,
			];

			if ( is_array( $post ) && ( count( $post ) === $number ) ) {
				$final = true;
				for ( $a = 0; $a < $number; $a ++ ) {
					if ( ! array_key_exists( $allValus[ $a ], $post ) ) {
						$final = false;
					}
				}
			}
			if ( $final === false ) {
				self::send_error( "Invalid Request" );
			}

			return $final;
			//   self::organizeQuery($allValus, $have);
		}

		//  private $db_mid = "djijoepwijpihhph;";

		/**  */
		const VAR_0 = "var_0";

		const VAR_1 = "var_1";

		const VAR_2 = "var_2";

		const VAR_3 = "var_3";

		const VAR_4 = "var_4";

		const VAR_5 = "var_5";

		const VAR_6 = "var_6";

		const VAR_7 = "var_7";

		const VAR_8 = "var_8";

		const VAR_9 = "var_9";

		const VAR_10 = "var_10";

		const VAR_11 = "var_11";

		const VAR_12 = "var_12";

		const VAR_13 = "var_13";

		const VAR_14 = "var_14";

		const VAR_15 = "var_15";

		const VAR_16 = "var_16";

		const VAR_17 = "var_17";

		const VAR_18 = "var_18";

		const VAR_19 = "var_19";

		const VAR_20 = "var_20";

		/* success failure params */

		static $S_F_WHAT    = "what";
		static $S_F_DATA    = "data";
		static $S_F_MESSAGE = "message";
		static $S_F_STATUS  = "status";
		static $S_F_SUCCESS = "0";
		static $S_F_FAILURE = "1";
		static $S_F_ERROR   = "2";

	}
