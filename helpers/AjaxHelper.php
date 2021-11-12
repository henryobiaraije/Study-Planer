<?php


	namespace StudyPlanner\Helpers;


	use Illuminate\Database\Capsule\Manager;
	use Model\Deck;
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
			add_action( 'admin_sp_ajax_admin_search_deck_group', array( $this, 'ajax_admin_search_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_delete_deck_group', array( $this, 'ajax_admin_delete_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_trash_deck_group', array( $this, 'ajax_admin_trash_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_create_tag', array( $this, 'ajax_admin_create_tag' ) );
			add_action( 'admin_sp_ajax_admin_load_tags', array( $this, 'ajax_admin_load_tags' ) );
			add_action( 'admin_sp_ajax_admin_search_tags', array( $this, 'ajax_admin_search_tags' ) );
			add_action( 'admin_sp_ajax_admin_trash_tags', array( $this, 'ajax_admin_trash_tags' ) );
			add_action( 'admin_sp_ajax_admin_delete_tags', array( $this, 'ajax_admin_delete_tags' ) );
			add_action( 'admin_sp_ajax_admin_load_decks', array( $this, 'ajax_admin_load_decks' ) );
			add_action( 'admin_sp_ajax_admin_create_new_deck', array( $this, 'ajax_admin_create_new_deck' ) );
		}

		// <editor-fold desc="Tags">
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

			Common::send_success( 'Created successfully.', $create );

		}

		public function ajax_admin_search_tags( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_load_tags',
//				'post' => $post,
//			] );

			$params         = $post[ Common::VAR_2 ]['params'];
			$per_page       = (int) sanitize_text_field( $params['per_page'] );
			$page           = (int) sanitize_text_field( $params['page'] );
			$search_keyword = sanitize_text_field( $params['search_keyword'] );
			$status         = sanitize_text_field( $params['status'] );
//			Common::send_error( [
//				'ajax_admin_load_tags',
//				'post'            => $post,
//				'$params'         => $params,
//				'$per_page'       => $per_page,
//				'$page'           => $page,
//				'$search_keyword' => $search_keyword,
//				'$status'         => $status,
//			] );

			$items  = Tag::get_tags( [
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			] );
			$totals = Tag::get_totals();

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


			Common::send_success( 'Tag loaded.', [
				'details' => $items,
				'totals'  => $totals,
			], [
//				'post' => $post,
			] );

		}

		public function ajax_admin_load_tags( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_load_tags',
//				'post' => $post,
//			] );

			$params         = $post[ Common::VAR_2 ]['params'];
			$per_page       = (int) sanitize_text_field( $params['per_page'] );
			$page           = (int) sanitize_text_field( $params['page'] );
			$search_keyword = sanitize_text_field( $params['search_keyword'] );
			$status         = sanitize_text_field( $params['status'] );
//			Common::send_error( [
//				'ajax_admin_load_tags',
//				'post'            => $post,
//				'$params'         => $params,
//				'$per_page'       => $per_page,
//				'$page'           => $page,
//				'$search_keyword' => $search_keyword,
//				'$status'         => $status,
//			] );

			$items  = Tag::get_tags( [
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			] );
			$totals = Tag::get_totals();

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


			Common::send_success( 'Tag loaded.', [
				'details' => $items,
				'totals'  => $totals,
			], [
//				'post' => $post,
			] );

		}

		public function ajax_admin_trash_tags( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_trash_tags',
//				'post' => $post,
//			] );

			$all  = $post[ Common::VAR_2 ];
			$args = wp_parse_args(
				$all,
				[
					'items' => [],
				] );
			foreach ( $args['items'] as $one ) {
				$id = (int) sanitize_text_field( $one['id'] );
				Tag::query()->where( 'id', '=', $id )->delete();
//				Common::send_error( [
//					'ajax_admin_create_new_deck_group',
//					'post'  => $post,
//					'$all'  => $all,
//					'$id'   => $id,
//					'$args' => $args,
//					'$one' => $one,
//				] );
			}


			Common::send_success( 'Trashed successfully.' );

		}

		public function ajax_admin_delete_tags( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_delete_tags',
//				'post' => $post,
//			] );

			$all  = $post[ Common::VAR_2 ];
			$args = wp_parse_args(
				$all,
				[
					'items' => [],
				] );
			foreach ( $args['items'] as $one ) {
				$id = (int) sanitize_text_field( $one['id'] );
				Tag::query()->where( 'id', '=', $id )->forceDelete();
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

		// </editor-fold desc="Tags">

		public function ajax_admin_load_decks( $post ) : void {
			Common::send_error( [
				'ajax_admin_load_deck_group',
				'post' => $post,
			] );

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

		public function ajax_admin_create_new_deck( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_create_new_deck',
//				'post' => $post,
//			] );

			$all        = $post[ Common::VAR_2 ];
			$name       = sanitize_text_field( $all['name'] );
			$deck_group = $all['deck_group'];
			$tags       = $all['tags'];

			if ( empty( $deck_group ) ) {
				Common::send_error( 'Please select a deck group' );
			}

			$deck          = Deck::firstOrCreate( [ 'name' => $name ] );
			$deck_group_id = (int) sanitize_text_field( $deck_group['id'] );
			$deck_group    = DeckGroup::find( $deck_group_id );
			$deck_group->decks()->save( $deck );
			foreach ( $tags as $one ) {
				$tag = Tag::find( $one['id'] );
				$deck->tags()->save( $tag );
//				Common::send_error( [
//					'ajax_admin_create_new_deck_group',
//					'post'           => $post,
//					'$deck_group_id' => $deck_group_id,
//					'$tags'          => $tags,
//					'$name'          => $name,
//					'$tag'           => $tag,
////				'$deck_group'      => $deck_group,
//				] );
			}

//			Common::send_error( [
//				'ajax_admin_create_new_deck_group',
//				'post'           => $post,
//				'$deck_group_id' => $deck_group_id,
//				'$tags'          => $tags,
//				'$name'          => $name,
//			] );

			Common::send_success( 'Deck created.' );

		}

		// <editor-fold desc="Deck Groups">

		public function ajax_admin_search_deck_group( $post ) : void {
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

			$deck_groups = DeckGroup::get_deck_simple( [
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
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


			Common::send_success( 'Deck group found.', $deck_groups );

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


			Common::send_success( 'Trashed successfully.' );

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
				$name       = sanitize_text_field( $group['name'] );
				$id         = (int) sanitize_text_field( $group['id'] );
				$update_id  = DeckGroup::query()->where( 'id', '=', $id )->update( [
					'name' => $name,
				] );
				$deck_group = DeckGroup::find( $id );
				$deck_group->tags()->detach();
				foreach ( $group['tags'] as $one ) {
					$tag_id = (int) sanitize_text_field( $one['id'] );
					$tag    = Tag::find( $tag_id );
					$deck_group->tags()->save( $tag );
				}

//				Common::send_error( [
//					'ajax_admin_create_new_deck_group',
//					'post'        => $post,
//					'$all'        => $all,
//					'$name'       => $name,
//					'$id'         => $id,
//					'$args'       => $args,
//					'$group'      => $group,
//					'$deck_group' => $deck_group,
//					'$update_id'  => $update_id,
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
			$tags            = $all['tags'];

			$create     = DeckGroup::firstOrCreate( [ 'name' => $deck_group_name ] );
			$deck_group = DeckGroup::find( $create->id );
			foreach ( $tags as $one ) {
				$tag = Tag::find( $one['id'] );
				$deck_group->tags()->save( $tag );
//				Common::send_error( [
//					'ajax_admin_create_new_deck_group',
//					'post'             => $post,
//					'toSql'             => $deck_group->tags()->toSql(),
//					'$deck_group_name' => $deck_group_name,
//					'$tags'            => $tags,
//					'$tag'            => $tag,
////				'$deck_group'      => $deck_group,
//				] );
			}

//			Common::send_error( [
//				'ajax_admin_create_new_deck_group',
//				'post'             => $post,
//				'$deck_group_name' => $deck_group_name,
//				'$tags'            => $tags,
////				'$deck_group'      => $deck_group,
//			] );

			Common::send_success( 'Deck group created.' );

		}

		// </editor-fold >


	}