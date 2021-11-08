<?php


	namespace StudyPlanner\Helpers;




	use StudyPlanner\Libs\Common;

	class AjaxHelper {
		/**
		 * @var self $instance
		 */
		private static $instance;

		private function __construct() {
		}

		public static function get_instance() : self {
			if ( self::$instance ) {
				return self::$instance;
			}

			self::$instance = new self();
			self::$instance->init_ajax();

			return self::$instance;
		}

		private function init_ajax() {
			add_action( 'admin_sp_ajax_admin_create_new_deck_group', array( $this, 'ajax_admin_create_new_deck_group' ) );
			}

		public function ajax_admin_create_new_deck_group( $post ) : void {
			Common::send_error( [
				'ajax_admin_create_new_deck_group',
				'post' => $post,
			] );

			$params         = $post[ Common::VAR_2 ]['params'];
			$per_page       = 5;
			$page           = 1;
			$search_keyword = sanitize_text_field( $params['search_keyword'] );
			$status         = 'publish';
			$endpoints      = Endpoint::get_public( [
				'search_keyword' => $search_keyword,
				'page'           => $page,
				'per_page'       => $per_page,
				'status'         => $status,
			] );
//			Common::send_error( [
//				'ajax_public_search_for_keyword',
//				'post'            => $post,
//				'$params'         => $params,
//				'$per_page'       => $per_page,
//				'$page'           => $page,
//				'$search_keyword' => $search_keyword,
//				'$status'         => $status,
//				'$endpoints'      => $endpoints,
//			] );
			Common::send_success( 'Loaded', [
				'details' => $endpoints,
			] );

		}


	}