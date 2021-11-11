<?php


	namespace StudyPlanner\Helpers;


	use Model\DeckGroup;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Models\Tag;

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
			add_action( 'admin_sp_ajax_admin_delete_deck_group', array( $this, 'ajax_admin_delete_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_trash_deck_group', array( $this, 'ajax_admin_trash_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_create_tag', array( $this, 'ajax_admin_create_tag' ) );
		}

		public function ajax_admin_create_tag( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_create_tag',
//				'post' => $post,
//			] );

			$all  = $post[ Common::VAR_2 ];
			$name = sanitize_text_field( $all['name'] );

			$create = Tag::firstOrCreate( [ 'name' => $name ] );

//			Common::send_error( [
//				'ajax_admin_create_tag',
//				'post'  => $post,
//				'$name' => $name,
//			] );

			Common::send_success( 'Created successfully.' );

		}

		// <editor-fold desc="Deck Groups">
		public function ajax_admin_load_deck_group( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'post' => $post,
//			] );

			$params         = $post[ Common::VAR_2 ]['params'];
			$per_page       = (int) sanitize_text_field( $params['per_page'] );
			$page           = (int) sanitize_text_field( $params['page'] );
			$search_keyword = sanitize_text_field( $params['search_keyword'] );
			$status         = sanitize_text_field( $params['status'] );
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
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			] );
			$totals      = DeckGroup::get_totals();

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


			Common::send_success( 'Deck group loaded.', [
				'details' => $deck_groups,
				'totals'  => $totals,
			], [
				'post' => $post,
			] );

		}

		public function ajax_admin_trash_deck_group( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_trash_deck_group',
//				'post' => $post,
//			] );

			$all  = $post[ Common::VAR_2 ];
			$args = wp_parse_args(
				$all,
				[
					'deck_groups' => [],
				] );
			foreach ( $args['deck_groups'] as $group ) {
				$id = (int) sanitize_text_field( $group['id'] );
				DeckGroup::query()->where( 'id', '=', $id )->delete();
//				Common::send_error( [
//					'ajax_admin_create_new_deck_group',
//					'post'  => $post,
//					'$all'  => $all,
//					'$id'   => $id,
//					'$args' => $args,
//				] );
			}


			Common::send_success( 'Deleted successfully.' );

		}

		public function ajax_admin_delete_deck_group( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_delete_deck_group',
//				'post' => $post,
//			] );

			$all  = $post[ Common::VAR_2 ];
			$args = wp_parse_args(
				$all,
				[
					'deck_groups' => [],
				] );
			foreach ( $args['deck_groups'] as $group ) {
				$id = (int) sanitize_text_field( $group['id'] );
				DeckGroup::query()->where( 'id', '=', $id )->forceDelete();
//				Common::send_error( [
//					'ajax_admin_create_new_deck_group',
//					'post'  => $post,
//					'$all'  => $all,
//					'$name' => $name,
//					'$id'   => $id,
//					'$args' => $args,
//				] );
			}


			Common::send_success( 'Deleted.' );

		}

		public function ajax_admin_update_deck_group( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_update_deck_group',
//				'post' => $post,
//			] );

			$all  = $post[ Common::VAR_2 ];
			$args = wp_parse_args(
				$all,
				[
					'deck_groups' => [],
				] );
			foreach ( $args['deck_groups'] as $group ) {
				$name = sanitize_text_field( $group['name'] );
				$id   = (int) sanitize_text_field( $group['id'] );
				DeckGroup::query()->where( 'id', '=', $id )->update( [
					'name' => $name,
				] );
//				Common::send_error( [
//					'ajax_admin_create_new_deck_group',
//					'post'  => $post,
//					'$all'  => $all,
//					'$name' => $name,
//					'$id'   => $id,
//					'$args' => $args,
//				] );
			}


			Common::send_success( 'Saved.' );

		}

		public function ajax_admin_create_new_deck_group( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_create_new_deck_group',
//				'post' => $post,
//			] );

			$all             = $post[ Common::VAR_2 ];
			$deck_group_name = sanitize_text_field( $all['deck_group_name'] );

			$deck_group = DeckGroup::firstOrCreate( [ 'name' => $deck_group_name ] );

//			Common::send_error( [
//				'ajax_admin_create_new_deck_group',
//				'post'             => $post,
//				'$deck_group_name' => $deck_group_name,
//				'$deck_group'      => $deck_group,
//			] );

			Common::send_success( 'Deck group created.' );

		}
		// </editor-fold >


	}