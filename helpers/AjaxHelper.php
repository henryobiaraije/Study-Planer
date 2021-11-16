<?php


	namespace StudyPlanner\Helpers;


	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Eloquent\Model;
	use Model\Card;
	use Model\CardGroup;
	use Model\CardGroups;
	use Model\Deck;
	use Model\DeckGroup;
	use PDOException;
	use PHPMailer\PHPMailer\Exception;
	use StudyPlanner\Initializer;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Libs\Settings;
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
			// <editor-fold desc="Deck Group">
			add_action( 'admin_sp_ajax_admin_create_new_deck_group', array( $this, 'ajax_admin_create_new_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_update_deck_group', array( $this, 'ajax_admin_update_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_load_deck_group', array( $this, 'ajax_admin_load_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_search_deck_group', array( $this, 'ajax_admin_search_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_delete_deck_group', array( $this, 'ajax_admin_delete_deck_group' ) );
			add_action( 'admin_sp_ajax_admin_trash_deck_group', array( $this, 'ajax_admin_trash_deck_group' ) );
			// </editor-fold desc="Deck Group">
			// <editor-fold desc="Deck">
			add_action( 'admin_sp_ajax_admin_load_decks', array( $this, 'ajax_admin_load_decks' ) );
			add_action( 'admin_sp_ajax_admin_search_decks', array( $this, 'ajax_admin_search_decks' ) );
			add_action( 'admin_sp_ajax_admin_create_new_deck', array( $this, 'ajax_admin_create_new_deck' ) );
			add_action( 'admin_sp_ajax_admin_update_decks', array( $this, 'ajax_admin_update_decks' ) );
			add_action( 'admin_sp_ajax_admin_trash_decks', array( $this, 'ajax_admin_trash_decks' ) );
			add_action( 'admin_sp_ajax_admin_delete_decks', array( $this, 'ajax_admin_delete_decks' ) );
			// </editor-fold desc="Deck">
			// <editor-fold desc="Tag">
			add_action( 'admin_sp_ajax_admin_create_tag', array( $this, 'ajax_admin_create_tag' ) );
			add_action( 'admin_sp_ajax_admin_load_tags', array( $this, 'ajax_admin_load_tags' ) );
			add_action( 'admin_sp_ajax_admin_search_tags', array( $this, 'ajax_admin_search_tags' ) );
			add_action( 'admin_sp_ajax_admin_trash_tags', array( $this, 'ajax_admin_trash_tags' ) );
			add_action( 'admin_sp_ajax_admin_delete_tags', array( $this, 'ajax_admin_delete_tags' ) );
			// </editor-fold desc="Tag">
			// <editor-fold desc="Others">
			add_action( 'admin_sp_ajax_admin_create_new_basic_card', array( $this, 'ajax_admin_create_new_basic_card' ) );
			add_action( 'admin_sp_ajax_admin_load_image_attachment', array( $this, 'ajax_admin_load_image_attachment' ) );
			add_action( 'admin_sp_ajax_admin_load_basic_card', array( $this, 'ajax_admin_load_basic_card' ) );
			add_action( 'admin_sp_ajax_admin_update_basic_card', array( $this, 'admin_update_basic_card' ) );
			add_action( 'admin_sp_ajax_admin_load_cards_groups', array( $this, 'ajax_admin_load_cards_groups' ) );
			add_action( 'admin_sp_ajax_admin_create_new_gap_card', array( $this, 'ajax_admin_create_new_gap_card' ) );
			// </editor-fold desc="Card">
		}

		public function ajax_admin_create_new_gap_card( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_create_new_gap_card',
//				'post' => $post,
//			] );

			$all                 = $post[ Common::VAR_2 ];
			$e_cards             = $all['cards'];
			$e_card_group        = $all['cardGroup'];
			$e_deck              = $e_card_group['deck'];
			$bg_image_id         = (int) sanitize_text_field( $e_card_group['bg_image_id'] );
			$whole_question      = $e_card_group['whole_question'];
			$e_set_bg_as_default = $all['set_bg_as_default'];
			$schedule_at         = $e_card_group['scheduled_at'];
			$reverse             = $e_card_group['reverse'];
			$e_tags              = $e_card_group['tags'];
			$cg_name             = sanitize_text_field( $e_card_group['name'] );
			if ( empty( $schedule_at ) ) {
				$schedule_at = Common::getDateTime();
			} else {
				$schedule_at = Common::format_datetime( $schedule_at );
			}
			if ( empty( $e_deck ) ) {
				Common::send_error( 'Please select a deck' );
			}
			if ( empty( $whole_question ) ) {
				Common::send_error( 'Please provide a question' );
			}
			if ( empty( $e_cards ) ) {
				Common::send_error( 'No cards will be created' );
			}
			if ( empty( $e_tags ) ) {
				Common::send_error( 'No tag selected' );
			}
			if ( empty( $bg_image_id ) ) {
				$bg_image_id = get_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, 0 );
				if ( empty( $bg_image_id ) ) {
					Common::send_error( 'Please select a background image.' );
				}
			}

			$e_deck_id = $e_card_group['deck']['id'];
			$deck      = Deck::find( $e_deck_id );
			if ( empty( $deck ) ) {
				Common::send_error( 'Invalid deck' );
			}

			Manager::beginTransaction();
			$card_group                 = new CardGroup();
			$card_group->whole_question = $whole_question;
			$card_group->card_type      = 'gap';
			$card_group->scheduled_at   = $schedule_at;
			$card_group->bg_image_id    = $bg_image_id;
			$card_group->name           = $cg_name;
			$card_group->deck_id        = $e_deck_id;
			$card_group->reverse        = $reverse;
			$card_group->save();
			$card_group->tags()->detach();
			foreach ( $e_tags as $one ) {
				$tag_id = $one['id'];
				$tag    = Tag::find( $tag_id );
				if ( ! empty( $tag ) ) {
					$card_group->tags()->save( $tag );
				}
			}
			foreach ( $e_cards as $one_card ) {
				$question            = $one_card['question'];
				$answer              = $one_card['answer'];
				$hash                = $one_card['hash'];
				$card                = new Card();
				$card->question      = $question;
				$card->hash          = $hash;
				$card->answer        = $answer;
				$card->card_group_id = $card_group->id;
				$card->save();
//				Common::send_error( [
//					'ajax_admin_create_new_basic_card',
//					'post'                 => $post,
//					'$one_card'            => $one_card,
//					'toSql'                => $card_group->toSql(),
//					'$reverse'             => $reverse,
//					'$hash'                => $hash,
//					'$question'            => $question,
//					'$e_card_group'        => $e_card_group,
//					'$whole_question'      => $whole_question,
//					'$e_set_bg_as_default' => $e_set_bg_as_default,
//					'$bg_image_id'         => $bg_image_id,
//					'$answer'              => $answer,
//					'$deck'                => $deck,
//					'$cg_name'             => $cg_name,
//					'$e_tags'              => $e_tags,
//					'$schedule_at'         => $schedule_at,
//				] );

			}

			Manager::commit();

			if ( $e_set_bg_as_default ) {
				update_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id );
			}
			// Create card group


//			Common::send_error( [
//				'ajax_admin_create_new_basic_card',
//				'post'                 => $post,
//				'$e_card'              => $e_card,
//				'toSql'                => $card_group->toSql(),
//				'$reverse'             => $reverse,
//				'$e_card_group'        => $e_card_group,
//				'$question'            => $question,
//				'$e_set_bg_as_default' => $e_set_bg_as_default,
//				'$bg_image_id'         => $bg_image_id,
//				'$answer'              => $answer,
//				'$deck'                => $deck,
//				'$cg_name'             => $cg_name,
//				'$e_tags'              => $e_tags,
//				'$schedule_at'         => $schedule_at,
//			] );

			$edit_page = Initializer::get_admin_url( Settings::SLUG_GAP_CARD )
			             . '&card-group=' . $card_group->id;

			Common::send_success( 'Created successfully.', $edit_page );

		}

		public function ajax_admin_load_cards_groups( $post ) : void {
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

			$card_groups = CardGroup::get_card_groups( [
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			] );
			$totals      = CardGroup::get_totals();

//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'post'            => $post,
//				'$params'         => $params,
//				'$per_page'       => $per_page,
//				'$page'           => $page,
//				'$search_keyword' => $search_keyword,
//				'$card_groups'    => $card_groups,
//				'$status'         => $status,
//			] );


			Common::send_success( 'Card group loaded.', [
				'details' => $card_groups,
				'totals'  => $totals,
			], [
//				'post' => $post,
			] );

		}


		public function ajax_admin_load_image_attachment( $post ) : void {

//			Common::send_error( [
//				'ajax_admin_load_image_attachment',
//				'post' => $post,
//			] );

			$all = $post[ Common::VAR_2 ];
			$id  = (int) sanitize_text_field( $all['id'] );
			if ( $id < 1 ) {
				Common::send_error( 'Invalid image id' );
			}
			$attachment_url = wp_get_attachment_image_url( $id, 'full' );
			if ( empty( $attachment_url ) ) {
				$default_bg_id = get_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, 0 );
				if ( ! empty( $default_bg_id ) ) {
					$attachment_url = wp_get_attachment_image_url( $default_bg_id, 'full' );
				}
			}

//			Common::send_error( [
//				'ajax_admin_create_new_basic_card',
//				'post'        => $post,
//				'$id'         => $id,
//				'$attachment_url' => $attachment_url,
//			] );


			Common::send_success( 'Image found', $attachment_url );

		}

		public function admin_update_basic_card( $post ) : void {
//			Common::send_error( [
//				'admin_update_basic_card',
//				'post' => $post,
//			] );

			$all                 = $post[ Common::VAR_2 ];
			$e_card              = $all['card'];
			$e_card_group        = $all['cardGroup'];
			$e_deck              = $e_card_group['deck'];
			$e_card_group_id     = $e_card_group['id'];
			$bg_image_id         = (int) sanitize_text_field( $e_card_group['bg_image_id'] );
			$question            = $e_card_group['whole_question'];
			$answer              = $e_card['answer'];
			$e_set_bg_as_default = $all['set_bg_as_default'];
			$schedule_at         = $e_card_group['scheduled_at'];
			$reverse             = $e_card_group['reverse'];
			$e_cards             = $e_card_group['cards'];
			$cg_name             = sanitize_text_field( $e_card_group['name'] );
			if ( empty( $schedule_at ) ) {
				$schedule_at = Common::getDateTime();
			} else {
				$schedule_at = Common::format_datetime( $schedule_at );
			}
			if ( empty( $e_cards ) ) {
				Common::send_error( 'The card is empty' );
			}
			if ( empty( $e_deck ) ) {
				Common::send_error( 'Please select a deck' );
			}
			if ( empty( $question ) ) {
				Common::send_error( 'Please provide a question' );
			}
			if ( empty( $answer ) ) {
				Common::send_error( 'Please provide an answer' );
			}

			$e_deck_id = $e_card_group['deck']['id'];
			$e_tags    = $e_card_group['tags'];
			$deck      = Deck::find( $e_deck_id );
			if ( empty( $deck ) ) {
				Common::send_error( 'Invalid deck' );
			}
			$card_group = CardGroup::find( $e_card_group_id );
			if ( empty( $card_group ) ) {
				Common::send_error( 'Invalid card group' );
			}
			$card_id = $e_cards[0]['id'];
			$card    = Card::find( $card_id );
			if ( empty( $card ) ) {
				Common::send_error( 'Invalid card' );
			}

			Manager::beginTransaction();

			$card_group->whole_question = $question;
			$card_group->scheduled_at   = $schedule_at;
			$card_group->bg_image_id    = $bg_image_id;
			$card_group->name           = $cg_name;
			$card_group->deck_id        = $e_deck_id;
			$card_group->save();
			$card_group->tags()->detach();
			foreach ( $e_tags as $one ) {
				$tag_id = $one['id'];
				$tag    = Tag::find( $tag_id );
				if ( ! empty( $tag ) ) {
					$card_group->tags()->save( $tag );
				}
			}
			$card->question = $question;
			$card->answer   = $answer;
			$card->save();
			Manager::commit();

			if ( $e_set_bg_as_default ) {
				update_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id );
			}
			// Create card group


//			Common::send_error( [
//				'ajax_admin_create_new_basic_card',
//				'post'                 => $post,
//				'$e_card'              => $e_card,
//				'toSql'                => $card_group->toSql(),
//				'$reverse'             => $reverse,
//				'$card'                => $card,
//				'$e_card_group_id'     => $e_card_group_id,
//				'$e_card_group'        => $e_card_group,
//				'$question'            => $question,
//				'$e_set_bg_as_default' => $e_set_bg_as_default,
//				'$bg_image_id'         => $bg_image_id,
//				'$answer'              => $answer,
//				'$deck'                => $deck,
//				'$cg_name'             => $cg_name,
//				'$e_tags'              => $e_tags,
//				'$schedule_at'         => $schedule_at,
//			] );

			$edit_page = Initializer::get_admin_url( Settings::SLUG_BASIC_CARD )
			             . '&action=card-edit'
			             . '&card-group=' . $card_group->id;

			Common::send_success( 'Updated successfully.', $edit_page );

		}


		public function ajax_admin_create_new_basic_card( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_create_new_basic_card',
//				'post' => $post,
//			] );

			$all                 = $post[ Common::VAR_2 ];
			$e_card              = $all['card'];
			$e_card_group        = $all['cardGroup'];
			$e_deck              = $e_card_group['deck'];
			$bg_image_id         = (int) sanitize_text_field( $e_card_group['bg_image_id'] );
			$question            = $e_card_group['whole_question'];
			$answer              = $e_card['answer'];
			$e_set_bg_as_default = $all['set_bg_as_default'];
			$schedule_at         = $e_card_group['scheduled_at'];
			$reverse             = $e_card_group['reverse'];
			$cg_name             = sanitize_text_field( $e_card_group['name'] );
			if ( empty( $schedule_at ) ) {
				$schedule_at = Common::getDateTime();
			} else {
				$schedule_at = Common::format_datetime( $schedule_at );
			}
			if ( empty( $e_deck ) ) {
				Common::send_error( 'Please select a deck' );
			}
			if ( empty( $question ) ) {
				Common::send_error( 'Please provide a question' );
			}
			if ( empty( $answer ) ) {
				Common::send_error( 'Please provide an answer' );
			}

			$e_deck_id = $e_card_group['deck']['id'];
			$e_tags    = $e_card_group['tags'];
			$deck      = Deck::find( $e_deck_id );
			if ( empty( $deck ) ) {
				Common::send_error( 'Invalid deck' );
			}

			Manager::beginTransaction();
			$card_group                 = new CardGroup();
			$card_group->whole_question = $question;
			$card_group->card_type      = 'basic';
			$card_group->scheduled_at   = $schedule_at;
			$card_group->bg_image_id    = $bg_image_id;
			$card_group->name           = $cg_name;
			$card_group->deck_id        = $e_deck_id;
			$card_group->reverse        = $reverse;
			$card_group->save();
			$card_group->tags()->detach();
			foreach ( $e_tags as $one ) {
				$tag_id = $one['id'];
				$tag    = Tag::find( $tag_id );
				if ( ! empty( $tag ) ) {
					$card_group->tags()->save( $tag );
				}
			}
			$card                = new Card();
			$card->question      = $question;
			$card->answer        = $answer;
			$card->card_group_id = $card_group->id;
			$card->save();
			Manager::commit();

			if ( $e_set_bg_as_default ) {
				update_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id );
			}
			// Create card group


//			Common::send_error( [
//				'ajax_admin_create_new_basic_card',
//				'post'                 => $post,
//				'$e_card'              => $e_card,
//				'toSql'                => $card_group->toSql(),
//				'$reverse'             => $reverse,
//				'$e_card_group'        => $e_card_group,
//				'$question'            => $question,
//				'$e_set_bg_as_default' => $e_set_bg_as_default,
//				'$bg_image_id'         => $bg_image_id,
//				'$answer'              => $answer,
//				'$deck'                => $deck,
//				'$cg_name'             => $cg_name,
//				'$e_tags'              => $e_tags,
//				'$schedule_at'         => $schedule_at,
//			] );

			$edit_page = Initializer::get_admin_url( Settings::SLUG_BASIC_CARD )
			             . '&card-group=' . $card_group->id;

			Common::send_success( 'Created successfully.', $edit_page );

		}

		public function ajax_admin_load_basic_card( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_create_new_basic_card',
//				'post' => $post,
//			] );

			$all           = $post[ Common::VAR_2 ];
			$card_group_id = (int) sanitize_text_field( $all['card_group_id'] );

			$card_group = CardGroup::with( 'tags', 'cards', 'deck' )->find( $card_group_id );
			if ( empty( $card_group ) ) {
				Common::send_error( 'Invalid card group' );
			}
//			$cards = $card_group->cards;

//			Common::send_error( [
//				'ajax_admin_create_new_basic_card',
//				'post'           => $post,
//				'$card_group_id' => $card_group_id,
//				'$card_group'    => $card_group,
////				'$cards'         => $cards,
//			] );


			Common::send_success( 'Loaded successfully.', [
				'card_group' => $card_group,
			] );

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

		// <editor-fold desc="Deck">
		public function ajax_admin_trash_decks( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_trash_deck_group',
//				'post' => $post,
//			] );

			$all  = $post[ Common::VAR_2 ];
			$args = wp_parse_args(
				$all,
				[
					'decks' => [],
				] );
			foreach ( $args['decks'] as $item ) {
				$id = (int) sanitize_text_field( $item['id'] );
				Deck::find( $id )->delete();
//				Deck::query()->where( 'id', '=', $id )->delete();
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

		public function ajax_admin_delete_decks( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_trash_deck_group',
//				'post' => $post,
//			] );

			$all  = $post[ Common::VAR_2 ];
			$args = wp_parse_args(
				$all,
				[
					'decks' => [],
				] );
			foreach ( $args['decks'] as $item ) {
				$id   = (int) sanitize_text_field( $item['id'] );
				$deck = Deck::withTrashed()->find( $id );
				$deck->tags()->detach();
				$deck->forceDelete();
//				Deck::query()->where( 'id', '=', $id )->delete();
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

		public function ajax_admin_load_decks( $post ) : void {
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

			$decks  = Deck::get_decks( [
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			] );
			$totals = Deck::get_totals();

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


			Common::send_success( 'Decks loaded.', [
				'details' => $decks,
				'totals'  => $totals,
			], [
				'post' => $post,
			] );

		}

		public function ajax_admin_search_decks( $post ) : void {
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

			$items = Deck::get_deck_simple( [
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


			Common::send_success( 'Decks  found.', $items );

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

			$deck_group_id = (int) sanitize_text_field( $deck_group['id'] );
			$deck_group    = DeckGroup::find( $deck_group_id );
			$deck          = new Deck();
			$deck->name    = $name;
			$deck->deck_group()->associate( $deck_group );
			$deck->save();

			$deck->tags()->detach();
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

		public function ajax_admin_update_decks( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_update_decks',
//				'post' => $post,
//			] );

			$all = $post[ Common::VAR_2 ];

			$decks = $all['decks'];
			foreach ( $decks as $one_deck ) {
				$name          = sanitize_text_field( $one_deck['name'] );
				$e_deck_group  = $one_deck['deck_group'];
				$tags          = $one_deck['tags'];
				$deck_id       = (int) sanitize_text_field( $one_deck['id'] );
				$deck_group_id = (int) sanitize_text_field( $e_deck_group['id'] );

				if ( empty( $e_deck_group ) ) {
					Common::send_error( 'Please select a deck group' );
				}

				$deck = Deck::find( $deck_id );
				$deck->update( [ 'name' => $name, 'deck_group_id' => $deck_group_id ] );
//				Common::send_error( [
//					'ajax_admin_create_new_deck_group',
//					'post'           => $post,
//					'$deck_group_id' => $deck_group_id,
//					'$tags'          => $tags,
//					'$name'          => $name,
//					'$e_deck_group'  => $e_deck_group,
//				] );
				$deck->tags()->detach();
				foreach ( $tags as $one ) {
					$tag_id = (int) sanitize_text_field( $one['id'] );
					$tag    = Tag::find( $tag_id );
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
			}

			Common::send_success( 'Saved.' );

		}

		// </editor-fold desc="Deck">

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

			$deck_groups = DeckGroup::get_deck_groups_simple( [
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
				$id         = (int) sanitize_text_field( $group['id'] );
				$deck_group = DeckGroup::withTrashed()->find( $id );
				$deck_group->tags()->detach();
				$deck_group->forceDelete();
//				DeckGroup::query()->where( 'id', '=', $id )->forceDelete();
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
			$deck_group->tags()->detach();
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

		// </editor-fold  desc="Deck Groups" >


	}