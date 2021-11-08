<?php


	namespace StudyPlanner\Helpers;


	use Model\DeckGroup;
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
			add_action( 'admin_sp_ajax_admin_update_deck_group', array( $this, 'ajax_admin_update_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_load_deck_group', array( $this, 'ajax_admin_load_deck_group' ) );
		}

		public function ajax_admin_load_deck_group( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'post' => $post,
//			] );

			$params         = $post[ Common::VAR_2 ]['params'];
			$per_page       = (int) sanitize_text_field( $params['per_page'] );
			$page           = (int) sanitize_text_field( $params['page'] );
			$search_keyword = sanitize_text_field( $params['search_keyword'] );
			$status         = 'publish';
//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'post'            => $post,
//				'$params'         => $params,
//				'$per_page'       => $per_page,
//				'$page'           => $page,
//				'$search_keyword' => $search_keyword,
//				'$status'         => $status,
//			] );

			$deck_groups = DeckGroup::get_deck_groups( [
				'search'   => $search_keyword,
				'page'     => $page,
				'per_page' => $per_page,
			] );

//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'post'            => $post,
//				'$params'         => $params,
//				'$per_page'       => $per_page,
//				'$page'           => $page,
//				'$search_keyword' => $search_keyword,
//				'$deck_groups'    => $deck_groups,
//				'$status'         => $status,
//			] );


			Common::send_success( 'Deck group loaded.', $deck_groups );

		}

		public function ajax_admin_update_deck_group( $post ) : void {
			Common::send_error( [
				'ajax_admin_update_deck_group',
				'post' => $post,
			] );

			$all             = $post[ Common::VAR_2 ];
			$deck_group_name = sanitize_text_field( $all['deck_group_name'] );

			$deck_group = DeckGroup::firstOrCreate( [ 'name' => $deck_group_name ] );

			Common::send_error( [
				'ajax_admin_create_new_deck_group',
				'post'             => $post,
				'$deck_group_name' => $deck_group_name,
				'$deck_group'      => $deck_group,
			] );

			Common::send_success( 'Deck group created.' );

		}

		public function ajax_admin_create_new_deck_group( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_create_new_deck_group',
//				'post' => $post,
//			] );

			$all             = $post[ Common::VAR_2 ];
			$deck_group_name = sanitize_text_field( $all['deck_group_name'] );

			$deck_group = DeckGroup::firstOrCreate( [ 'name' => $deck_group_name ] );

			Common::send_error( [
				'ajax_admin_create_new_deck_group',
				'post'             => $post,
				'$deck_group_name' => $deck_group_name,
				'$deck_group'      => $deck_group,
			] );

			Common::send_success( 'Deck group created.' );

		}


	}