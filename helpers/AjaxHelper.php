<?php


namespace StudyPlanner\Helpers;

use Illuminate\Database\Capsule\Manager;
use Model\Answered;
use Model\Card;
use Model\CardGroup;
use Model\Deck;
use Model\DeckGroup;
use Model\Topic;
use PDOException;
use StudyPlanner\Initializer;
use StudyPlanner\Libs\Common;
use StudyPlanner\Libs\Settings;
use StudyPlanner\Models\Collections;
use StudyPlanner\Models\Tag;
use StudyPlanner\Models\UserCard;
use function StudyPlanner\get_default_image_display_type;
use function StudyPlanner\get_mature_card_days;
use function StudyPlanner\get_uncategorized_deck_group_id;
use function StudyPlanner\get_uncategorized_deck_id;

class AjaxHelper {
	// <editor-fold desc="General">
	/**
	 * @var self $instance
	 */
	private static $instance;

	private function __construct() {
	}

	public static function get_instance(): self {
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
		// <editor-fold desc="Topics">
		add_action( 'admin_sp_ajax_admin_load_topics', array( $this, 'ajax_admin_load_topics' ) );
		add_action( 'admin_sp_ajax_admin_search_topics', array( $this, 'ajax_admin_search_topics' ) );
		add_action( 'admin_sp_ajax_admin_create_new_topic', array( $this, 'ajax_admin_create_new_topic' ) );
		add_action( 'admin_sp_ajax_admin_update_topics', array( $this, 'ajax_admin_update_topics' ) );
		add_action( 'admin_sp_ajax_admin_trash_topics', array( $this, 'ajax_admin_trash_topics' ) );
		add_action( 'admin_sp_ajax_admin_delete_topics', array( $this, 'ajax_admin_delete_topics' ) );
		// </editor-fold desc="Topics">
		// <editor-fold desc="Tag">
		add_action( 'admin_sp_ajax_admin_create_tag', array( $this, 'ajax_admin_create_tag' ) );
		add_action( 'admin_sp_ajax_admin_update_tags', array( $this, 'ajax_admin_update_tags' ) );
		add_action( 'admin_sp_ajax_admin_load_tags', array( $this, 'ajax_admin_load_tags' ) );
		add_action( 'admin_sp_ajax_admin_search_tags', array( $this, 'ajax_admin_search_tags' ) );
		add_action( 'admin_sp_ajax_admin_trash_tags', array( $this, 'ajax_admin_trash_tags' ) );
		add_action( 'admin_sp_ajax_admin_delete_tags', array( $this, 'ajax_admin_delete_tags' ) );
		// </editor-fold desc="Tag">
		// <editor-fold desc="Others">
		add_action( 'admin_sp_ajax_admin_create_new_basic_card', array( $this, 'ajax_admin_create_new_basic_card' ) );
		add_action( 'admin_sp_ajax_admin_load_image_attachment', array( $this, 'ajax_admin_load_image_attachment' ) );
		add_action( 'admin_sp_ajax_admin_load_basic_card', array( $this, 'ajax_admin_load_basic_card' ) );
		add_action( 'admin_sp_ajax_admin_trash_cards', array( $this, 'ajax_admin_trash_cards' ) );
		add_action( 'admin_sp_ajax_admin_restore_card_group', array( $this, 'ajax_admin_restore_card_group' ) );
		add_action( 'admin_sp_ajax_admin_delete_card_group', array( $this, 'ajax_admin_delete_card_group' ) );
		add_action( 'admin_sp_ajax_admin_update_basic_card', array( $this, 'admin_update_basic_card' ) );
		add_action( 'admin_sp_ajax_admin_load_cards_groups', array( $this, 'ajax_admin_load_cards_groups' ) );
		add_action( 'admin_sp_ajax_admin_create_new_gap_card', array( $this, 'ajax_admin_create_new_gap_card' ) );
		add_action( 'admin_sp_ajax_admin_update_gap_card', array( $this, 'ajax_admin_update_gap_card' ) );
		add_action( 'admin_sp_ajax_admin_update_table_card', array( $this, 'ajax_admin_update_table_card' ) );
		add_action( 'admin_sp_ajax_admin_update_image_card', array( $this, 'ajax_admin_update_image_card' ) );
		add_action( 'admin_sp_ajax_admin_create_new_table_card', array( $this, 'ajax_admin_create_new_table_card' ) );
		add_action( 'admin_sp_ajax_admin_create_new_image_card', array( $this, 'ajax_admin_create_new_image_card' ) );
		add_action( 'admin_sp_ajax_admin_load_settings', array( $this, 'ajax_admin_load_settings' ) );
		add_action( 'admin_sp_ajax_admin_update_settings', array( $this, 'ajax_admin_update_settings' ) );
		// </editor-fold desc="Card">
		// <editor-fold desc="Collections">
		add_action( 'admin_sp_ajax_admin_load_collections', array( $this, 'ajax_admin_load_collections' ) );
		add_action( 'admin_sp_ajax_admin_search_collections', array( $this, 'ajax_admin_search_collections' ) );
		add_action( 'admin_sp_ajax_admin_create_new_collection', array( $this, 'ajax_admin_create_new_collection' ) );
		add_action( 'admin_sp_ajax_admin_update_collections', array( $this, 'ajax_admin_update_collections' ) );
		add_action( 'admin_sp_ajax_admin_trash_collections', array( $this, 'ajax_admin_trash_collections' ) );
		add_action( 'admin_sp_ajax_admin_delete_collections', array( $this, 'ajax_admin_delete_collections' ) );
		add_action( 'admin_sp_ajax_admin_publish_collections', array( $this, 'ajax_admin_publish_collections' ) );
		// </editor-fold desc="Collections">
		// <editor-fold desc="All Cards">
		add_action( 'admin_sp_ajax_admin_search_all_cards', array( $this, 'ajax_admin_search_all_cards' ) );
		// </editor-fold desc="All Cards">
		// <editor-fold desc="User Cards">

		add_action( 'admin_sp_ajax_admin_save_user_cards', array( $this, 'ajax_admin_save_user_cards' ) );
		add_action( 'admin_sp_ajax_admin_assign_topics', array( $this, 'ajax_admin_assign_topics' ) );

		// </editor-fold desc="User Cards">
	}

	// </editor-fold desc="General">

	// <editor-fold desc="General">

	public function ajax_admin_save_user_cards( $post ): void {
		$params        = $post[ Common::VAR_2 ]['params'];
		$cards_ids     = $params['cards_ids'];
		$topic_id      = is_array( $params['topic'] ) ? $params['topic']['id'] : null;
		$deck_id       = is_array( $params['deck'] ) ? $params['deck']['id'] : null;
		$deck_group_id = is_array( $params['deck_group'] ) ? $params['deck_group']['id'] : null;
		// 'selected_cards' as 'selected_cards' | 'selected_group' | 'selected_deck' | 'selected_topic'
		$what_to_do = $params['what_to_do'];

		if ( 'selected_group' === $what_to_do ) {
			if ( empty( $deck_group_id ) ) {
				Common::send_error( 'Please select a deck group' );
			}
			$deck_group = DeckGroup
				::find( $deck_group_id )
				->with( 'decks.card_groups.cards' )
				->get()->all();

			if ( $deck_group() ) {

			}
		} elseif ( null !== $deck_id ) {

		} elseif ( null !== $topic_id ) {

		}

		Common::send_success( 'User Cards', $items );
	}

	// </editor-fold desc="General">

	//    <editor-fold desc="All Cards">
	public function ajax_admin_search_all_cards( $post ): void {
		$params                       = $post[ Common::VAR_2 ]['params'];
		$per_page                     = (int) sanitize_text_field( $params['per_page'] );
		$page                         = (int) sanitize_text_field( $params['page'] );
		$search_keyword               = sanitize_text_field( $params['search_keyword'] );
		$status                       = sanitize_text_field( $params['status'] );
		$e_deck_group                 = $params['deck_group'];
		$e_deck                       = $params['deck'];
		$e_topic                      = $params['topic'];
		$e_card_types                 = $params['card_types'];
		$e_front_end                  = $params['from_frontend'] === true;
		$e_for_add_to_study_deck      = $params['for_add_to_study_deck'] === true;
		$e_for_remove_from_study_deck = $params['for_remove_from_study_deck'] === true;
		$e_for_new_cards              = $params['for_new_cards'] === true;

		$deck_group_id = is_array( $e_deck_group ) ? $e_deck_group['id'] : null;
		$deck_id       = is_array( $e_deck ) ? $e_deck['id'] : null;
		$topic_id      = is_array( $e_topic ) ? $e_topic['id'] : null;
		$card_types    = is_array( $e_card_types ) ? $e_card_types : array();

		$user_id = get_current_user_id();

		$items = CardGroup::get_card_groups_simple_with_ordering(
			array(
				'search'                     => $search_keyword,
				'page'                       => $page,
				'per_page'                   => $per_page,
				'only_trashed'               => ( 'trash' === $status ) ? true : false,
				'deck_group_id'              => $deck_group_id,
				'deck_id'                    => $deck_id,
				'topic_id'                   => $topic_id,
				'card_types'                 => $card_types,
				'from_front_end'             => $e_front_end,
				'for_add_to_study_deck'      => $e_for_add_to_study_deck,
				'for_remove_from_study_deck' => $e_for_remove_from_study_deck,
				'for_new_cards'              => $e_for_new_cards,
				'user_id'                    => $user_id,
				'order_by_deck_group_name'   => true,
				'order_by_deck_name'         => true,
				'order_by_topic'             => true,
			)
		);

		Common::send_success( 'Cards  found.', $items );
	}

	//    </editor-fold desc="All Cards">

	// <editor-fold desc="Image Cards">

	public function ajax_admin_update_settings( $post ): void {

		$all              = $post[ Common::VAR_2 ];
		$settings         = $all['settings'];
		$mature_card_days = (int) sanitize_text_field( $settings['mature_card_days'] );
		update_option( Settings::OPTION_MATURED_CARD_DAYS, $mature_card_days );

		Common::send_success( 'Saved successfully.' );

	}

	public function ajax_admin_load_settings( $post ): void {
		// Common::send_error([
		// 'ajax_admin_load_settings',
		// 'post' => $post,
		// ]);

		$all = $post[ Common::VAR_2 ];

		$mature_card_days = get_mature_card_days();

		$settings = array(
			'mature_card_days' => $mature_card_days,
		);

		Common::send_success( 'Settings loaded.', $settings );

	}

	public function ajax_admin_create_new_image_card( $post ): void {

		$all                 = $post[ Common::VAR_2 ];
		$e_cards             = $all['cards'];
		$e_card_group        = $all['cardGroup'];
		$e_deck              = $e_card_group['deck'];
		$e_collection        = $e_card_group['collection'];
		$e_topic             = $e_card_group['topic'];
		$bg_image_id         = (int) sanitize_text_field( $e_card_group['bg_image_id'] );
		$whole_question      = $e_card_group['whole_question'];
		$whole_question      = wp_json_encode( $whole_question );
		$e_set_bg_as_default = $all['set_bg_as_default'];
		$schedule_at         = $e_card_group['scheduled_at'];
		$reverse             = $e_card_group['reverse'];
		$image_type          = $e_card_group['image_type'];
		$e_tags              = $e_card_group['tags'];
		$cg_name             = sanitize_text_field( $e_card_group['name'] );
		if ( ! in_array( $image_type, get_default_image_display_type() ) ) {
			Common::send_error( 'Please select a valid image display type' );
		}
		if ( empty( $schedule_at ) ) {
			$schedule_at = Common::getDateTime();
		} else {
			$schedule_at = Common::format_datetime( $schedule_at );
		}
		if ( empty( $e_deck ) ) {
			Common::send_error( 'Please select a deck' );
		}
		if ( empty( $e_topic ) ) {
			Common::send_error( 'Please select a topic' );
		}
		if ( empty( $whole_question ) ) {
			// Common::send_error( 'Please provide a question' );
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
		$e_topic_id = $e_topic['id'];
		$topic      = Topic::find( $e_topic_id );
		if ( empty( $topic ) ) {
			Common::send_error( 'Invalid topic' );
		}
		$e_collection_id = null;
		if ( is_array( $e_collection ) ) {
			$e_collection_id = $e_collection['id'];
		}

		// Common::send_error( [
		// 'ajax_admin_create_new_basic_card',
		// 'post'                 => $post,
		// '$reverse'             => $reverse,
		// '$whole_question'      => $whole_question,
		// '$e_cards'             => $e_cards,
		// '$e_card_group'        => $e_card_group,
		// '$e_set_bg_as_default' => $e_set_bg_as_default,
		// '$bg_image_id'         => $bg_image_id,
		// '$deck'                => $deck,
		// '$cg_name'             => $cg_name,
		// '$image_type'             => $image_type,
		// '$e_tags'              => $e_tags,
		// '$schedule_at'         => $schedule_at,
		// ] );

		Manager::beginTransaction();
		$card_group                 = new CardGroup();
		$card_group->whole_question = $whole_question;
		$card_group->card_type      = 'image';
		$card_group->scheduled_at   = $schedule_at;
		$card_group->bg_image_id    = $bg_image_id;
		$card_group->name           = $cg_name;
		$card_group->image_type     = $image_type;
		$card_group->deck_id        = $e_deck_id;
		$card_group->reverse        = $reverse;
		if ( $e_collection_id ) {
			$card_group->collection_id = $e_collection_id;
		}
		if ( $e_topic_id ) {
			$card_group->topic_id = $e_topic_id;
		}
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
			$question            = wp_json_encode( $one_card['question'] );
			$answer              = wp_json_encode( $one_card['answer'] );
			$hash                = $one_card['hash'];
			$c_number            = $one_card['c_number'];
			$card                = new Card();
			$card->question      = $question;
			$card->hash          = $hash;
			$card->answer        = $answer;
			$card->c_number      = $c_number;
			$card->card_group_id = $card_group->id;
			$card->save();
			// Common::send_error( [
			// 'ajax_admin_create_new_basic_card',
			// 'post'                 => $post,
			// '$one_card'            => $one_card,
			// 'toSql'                => $card_group->toSql(),
			// '$reverse'             => $reverse,
			// '$hash'                => $hash,
			// '$question'            => $question,
			// '$e_card_group'        => $e_card_group,
			// '$whole_question'      => $whole_question,
			// '$e_set_bg_as_default' => $e_set_bg_as_default,
			// '$bg_image_id'         => $bg_image_id,
			// '$answer'              => $answer,
			// '$deck'                => $deck,
			// '$cg_name'             => $cg_name,
			// '$e_tags'              => $e_tags,
			// '$schedule_at'         => $schedule_at,
			// ] );

		}

		Manager::commit();

		if ( $e_set_bg_as_default ) {
			update_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id );
		}
		// Create card group

		// Common::send_error( [
		// 'ajax_admin_create_new_basic_card',
		// 'post'                 => $post,
		// 'toSql'                => $card_group->toSql(),
		// '$reverse'             => $reverse,
		// '$e_card_group'        => $e_card_group,
		// '$question'            => $question,
		// '$e_set_bg_as_default' => $e_set_bg_as_default,
		// '$bg_image_id'         => $bg_image_id,
		// '$e_cards'              => $e_cards,
		// '$answer'              => $answer,
		// '$deck'                => $deck,
		// '$cg_name'             => $cg_name,
		// '$e_tags'              => $e_tags,
		// '$schedule_at'         => $schedule_at,
		// ] );

		$edit_page = Initializer::get_admin_url( Settings::SLUG_IMAGE_CARD )
		             . '&card-group=' . $card_group->id;

		Common::send_success( 'Created successfully.', $edit_page );

	}

	public function ajax_admin_update_image_card( $post ): void {
		$all                 = $post[ Common::VAR_2 ];
		$e_cards             = $all['cards'];
		$e_card_group        = $all['cardGroup'];
		$e_deck              = $e_card_group['deck'];
		$e_collection        = $e_card_group['collection'];
		$e_topic             = $e_card_group['topic'];
		$bg_image_id         = (int) sanitize_text_field( $e_card_group['bg_image_id'] );
		$whole_question      = wp_json_encode( $e_card_group['whole_question'] );
		$e_set_bg_as_default = $all['set_bg_as_default'];
		$schedule_at         = $e_card_group['scheduled_at'];
		$reverse             = $e_card_group['reverse'];
		$e_tags              = $e_card_group['tags'];
		$cg_name             = sanitize_text_field( $e_card_group['name'] );
		$image_type          = $e_card_group['image_type'];
		if ( ! in_array( $image_type, get_default_image_display_type() ) ) {
			Common::send_error( 'Please select a valid image display type' );
		}
		if ( empty( $schedule_at ) ) {
			$schedule_at = Common::getDateTime();
		} else {
			$schedule_at = Common::format_datetime( $schedule_at );
		}
		if ( empty( $e_deck ) ) {
			Common::send_error( 'Please select a deck' );
		}
		if ( empty( $e_topic ) ) {
			Common::send_error( 'Please select a topic' );
		}
		if ( empty( $whole_question ) ) {
			// Common::send_error( 'Please provide a question' );
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
		$cg_id      = (int) sanitize_text_field( $e_card_group['id'] );
		$card_group = CardGroup::find( $cg_id );
		if ( empty( $card_group ) ) {
			Common::send_error( 'Invalid Card group' );
		}

		$e_topic_id = $e_topic['id'];
		$topic      = Topic::find( $e_topic_id );
		if ( empty( $topic ) ) {
			Common::send_error( 'Invalid topic' );
		}
		$e_collection_id = null;
		if ( is_array( $e_collection ) ) {
			$e_collection_id = $e_collection['id'];
		}

		Manager::beginTransaction();
		$card_group->whole_question = $whole_question;
		$card_group->scheduled_at   = $schedule_at;
		$card_group->bg_image_id    = $bg_image_id;
		$card_group->name           = $cg_name;
		$card_group->deck_id        = $e_deck_id;
		$card_group->reverse        = false;
		$card_group->image_type     = $image_type;
		if ( $e_collection_id ) {
			$card_group->collection_id = $e_collection_id;
		}
		if ( $e_topic_id ) {
			$card_group->topic_id = $e_topic_id;
		}
		$card_group->save();
		$card_group->tags()->detach();
		foreach ( $e_tags as $one ) {
			$tag_id = $one['id'];
			$tag    = Tag::find( $tag_id );
			if ( ! empty( $tag ) ) {
				$card_group->tags()->save( $tag );
			}
		}

		$c_numbers_updated = array();
		foreach ( $e_cards as $one_card ) {
			$question = wp_json_encode( $one_card['question'] );
			$answer   = wp_json_encode( $one_card['answer'] );
			$c_number = $one_card['c_number'];
			$card_id  = $one_card['id'];
			$hash     = $one_card['hash'];
			$card     = new Card();
			if ( ! empty( $card_id ) ) {
				$card = Card::find( $card_id );
				if ( empty( $card ) ) {
					$card = new Card();
				}
			}
			$card->question      = $question;
			$card->answer        = $answer;
			$card->hash          = $hash;
			$card->c_number      = $c_number;
			$card->card_group_id = $card_group->id;
			$card->save();
			$c_numbers_updated[] = $c_number;
			// Common::send_error( [
			// 'ajax_admin_create_new_basic_card',
			// 'post'                 => $post,
			// '$one_card'            => $one_card,
			// '$card_id'            => $card_id,
			// 'toSql'                => $card_group->toSql(),
			// '$reverse'             => $reverse,
			// '$question'            => $question,
			// '$e_card_group'        => $e_card_group,
			// '$whole_question'      => $whole_question,
			// '$e_set_bg_as_default' => $e_set_bg_as_default,
			// '$bg_image_id'         => $bg_image_id,
			// '$answer'              => $answer,
			// '$deck'                => $deck,
			// '$card'                => $card,
			// '$cg_name'             => $cg_name,
			// '$e_tags'              => $e_tags,
			// '$schedule_at'         => $schedule_at,
			// ] );
		}

		// Delete cards without not updated
		$cards_to_delete = $all_cards = CardGroup::find( $cg_id )->cards()
		                                         ->whereNotIn( 'c_number', $c_numbers_updated )->get()->pluck( 'id' )->all();

		Answered::whereIn( 'card_id', $cards_to_delete )->forceDelete();
		Card::whereIn( 'id', $cards_to_delete )->forceDelete();
		// $all_cards       = CardGroup::find($cg_id)->cards()
		// ->whereNotIn('c_number', $c_numbers_updated)
		// ->forceDelete();
		//
		// Common::send_error([
		// 'ajax_admin_create_new_basic_card',
		// 'post'                  => $post,
		// '$all_cards'            => $all_cards,
		// 'toSql'                 => $card_group->toSql(),
		// '$reverse'              => $reverse,
		// '$cards_to_delete'      => $cards_to_delete,
		// 'type $cards_to_delete' => gettype($cards_to_delete),
		// '$e_card_group'         => $e_card_group,
		// '$question'             => $question,
		// '$e_set_bg_as_default'  => $e_set_bg_as_default,
		// '$bg_image_id'          => $bg_image_id,
		// '$answer'               => $answer,
		// '$deck'                 => $deck,
		// '$cg_name'              => $cg_name,
		// '$e_tags'               => $e_tags,
		// '$e_cards'              => $e_cards,
		// '$schedule_at'          => $schedule_at,
		// '$c_numbers_updated'    => $c_numbers_updated,
		// ]);
		Manager::commit();
		if ( $e_set_bg_as_default ) {
			update_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id );
		}

		$edit_page = Initializer::get_admin_url( Settings::SLUG_IMAGE_CARD )
		             . '&card-group=' . $card_group->id;

		Common::send_success( 'Updated successfully.', $edit_page );

	}

	// </editor-fold desc="/Image Cards">

	// <editor-fold desc="Table Cards">

	public function ajax_admin_create_new_table_card( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_create_new_table_card',
		// 'post' => $post,
		// ] );

		$all                 = $post[ Common::VAR_2 ];
		$e_cards             = $all['cards'];
		$e_card_group        = $all['cardGroup'];
		$e_deck              = $e_card_group['deck'];
		$e_collection        = $e_card_group['collection'];
		$e_topic             = $e_card_group['topic'];
		$bg_image_id         = (int) sanitize_text_field( $e_card_group['bg_image_id'] );
		$whole_question      = $e_card_group['whole_question'];
		$whole_question      = wp_json_encode( $whole_question );
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
		if ( empty( $e_topic ) ) {
			Common::send_error( 'Please select a topic' );
		}
		if ( empty( $whole_question ) ) {
			// Common::send_error( 'Please provide a question' );
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

		$e_topic_id = $e_topic['id'];
		$topic      = Topic::find( $e_topic_id );
		if ( empty( $topic ) ) {
			Common::send_error( 'Invalid topic' );
		}
		$e_collection_id = null;
		if ( is_array( $e_collection ) ) {
			$e_collection_id = $e_collection['id'];
		}

		Manager::beginTransaction();
		$card_group                 = new CardGroup();
		$card_group->whole_question = $whole_question;
		$card_group->card_type      = 'table';
		$card_group->scheduled_at   = $schedule_at;
		$card_group->bg_image_id    = $bg_image_id;
		$card_group->name           = $cg_name;
		$card_group->deck_id        = $e_deck_id;
		$card_group->reverse        = $reverse;
		if ( $e_collection_id ) {
			$card_group->collection_id = $e_collection_id;
		}
		if ( $e_topic_id ) {
			$card_group->topic_id = $e_topic_id;
		}
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
			$question            = wp_json_encode( $one_card['question'] );
			$answer              = wp_json_encode( $one_card['answer'] );
			$hash                = $one_card['hash'];
			$c_number            = $one_card['c_number'];
			$card                = new Card();
			$card->question      = $question;
			$card->hash          = $hash;
			$card->answer        = $answer;
			$card->c_number      = $c_number;
			$card->card_group_id = $card_group->id;
			$card->save();
			// Common::send_error( [
			// 'ajax_admin_create_new_basic_card',
			// 'post'                 => $post,
			// '$one_card'            => $one_card,
			// 'toSql'                => $card_group->toSql(),
			// '$reverse'             => $reverse,
			// '$hash'                => $hash,
			// '$question'            => $question,
			// '$e_card_group'        => $e_card_group,
			// '$whole_question'      => $whole_question,
			// '$e_set_bg_as_default' => $e_set_bg_as_default,
			// '$bg_image_id'         => $bg_image_id,
			// '$answer'              => $answer,
			// '$deck'                => $deck,
			// '$cg_name'             => $cg_name,
			// '$e_tags'              => $e_tags,
			// '$schedule_at'         => $schedule_at,
			// ] );

		}

		Manager::commit();

		if ( $e_set_bg_as_default ) {
			update_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id );
		}
		// Create card group

		// Common::send_error( [
		// 'ajax_admin_create_new_basic_card',
		// 'post'                 => $post,
		// 'toSql'                => $card_group->toSql(),
		// '$reverse'             => $reverse,
		// '$e_card_group'        => $e_card_group,
		// '$question'            => $question,
		// '$e_set_bg_as_default' => $e_set_bg_as_default,
		// '$bg_image_id'         => $bg_image_id,
		// '$answer'              => $answer,
		// '$deck'                => $deck,
		// '$cg_name'             => $cg_name,
		// '$e_tags'              => $e_tags,
		// '$schedule_at'         => $schedule_at,
		// ] );

		$edit_page = Initializer::get_admin_url( Settings::SLUG_TABLE_CARD )
		             . '&card-group=' . $card_group->id;

		Common::send_success( 'Created successfully.', $edit_page );

	}

	public function ajax_admin_update_table_card( $post ): void {
		$all                 = $post[ Common::VAR_2 ];
		$e_cards             = $all['cards'];
		$e_card_group        = $all['cardGroup'];
		$e_deck              = $e_card_group['deck'];
		$e_collection        = $e_card_group['collection'];
		$e_topic             = $e_card_group['topic'];
		$bg_image_id         = (int) sanitize_text_field( $e_card_group['bg_image_id'] );
		$whole_question      = wp_json_encode( $e_card_group['whole_question'] );
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
		if ( empty( $e_topic ) ) {
			Common::send_error( 'Please select a topic' );
		}
		if ( empty( $whole_question ) ) {
			// Common::send_error( 'Please provide a question' );
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
		$cg_id      = (int) sanitize_text_field( $e_card_group['id'] );
		$card_group = CardGroup::find( $cg_id );
		if ( empty( $card_group ) ) {
			Common::send_error( 'Invalid Card group' );
		}


		$e_topic_id = $e_topic['id'];
		$topic      = Topic::find( $e_topic_id );
		if ( empty( $topic ) ) {
			Common::send_error( 'Invalid topic' );
		}
		$e_collection_id = null;
		if ( is_array( $e_collection ) ) {
			$e_collection_id = $e_collection['id'];
		}
		Manager::beginTransaction();
		$card_group->whole_question = $whole_question;
		$card_group->scheduled_at   = $schedule_at;
		$card_group->bg_image_id    = $bg_image_id;
		$card_group->name           = $cg_name;
		$card_group->deck_id        = $e_deck_id;
		$card_group->reverse        = false;
		if ( $e_collection_id ) {
			$card_group->collection_id = $e_collection_id;
		}
		if ( $e_topic_id ) {
			$card_group->topic_id = $e_topic_id;
		}
		$card_group->save();
		$card_group->tags()->detach();
		foreach ( $e_tags as $one ) {
			$tag_id = $one['id'];
			$tag    = Tag::find( $tag_id );
			if ( ! empty( $tag ) ) {
				$card_group->tags()->save( $tag );
			}
		}
		$c_numbers_updated = array();
		foreach ( $e_cards as $one_card ) {
			$question = wp_json_encode( $one_card['question'] );
			$answer   = wp_json_encode( $one_card['answer'] );
			$c_number = $one_card['c_number'];
			$card_id  = $one_card['id'];
			$hash     = $one_card['hash'];
			$card     = new Card();
			if ( ! empty( $card_id ) ) {
				$card = Card::find( $card_id );
				if ( empty( $card ) ) {
					$card = new Card();
				}
			}
			$card->question      = $question;
			$card->answer        = $answer;
			$card->hash          = $hash;
			$card->c_number      = $c_number;
			$card->card_group_id = $card_group->id;
			$card->save();
			$c_numbers_updated[] = $c_number;
			// Common::send_error( [
			// 'ajax_admin_create_new_basic_card',
			// 'post'                 => $post,
			// '$one_card'            => $one_card,
			// '$card_id'            => $card_id,
			// 'toSql'                => $card_group->toSql(),
			// '$reverse'             => $reverse,
			// '$question'            => $question,
			// '$e_card_group'        => $e_card_group,
			// '$whole_question'      => $whole_question,
			// '$e_set_bg_as_default' => $e_set_bg_as_default,
			// '$bg_image_id'         => $bg_image_id,
			// '$answer'              => $answer,
			// '$deck'                => $deck,
			// '$card'                => $card,
			// '$cg_name'             => $cg_name,
			// '$e_tags'              => $e_tags,
			// '$schedule_at'         => $schedule_at,
			// ] );
		}
		Manager::commit();
		// Delete cards without not updated
		$all_cards = CardGroup::find( $cg_id )->cards()
		                      ->whereNotIn( 'c_number', $c_numbers_updated )
		                      ->forceDelete();
		//
		// Common::send_error( [
		// 'ajax_admin_create_new_basic_card',
		// 'post'                 => $post,
		// '$all_cards'           => $all_cards,
		// 'toSql'                => $card_group->toSql(),
		// '$reverse'             => $reverse,
		// '$e_card_group'        => $e_card_group,
		// '$question'            => $question,
		// '$e_set_bg_as_default' => $e_set_bg_as_default,
		// '$bg_image_id'         => $bg_image_id,
		// '$answer'              => $answer,
		// '$deck'                => $deck,
		// '$cg_name'             => $cg_name,
		// '$e_tags'              => $e_tags,
		// '$e_cards'             => $e_cards,
		// '$schedule_at'         => $schedule_at,
		// '$c_numbers_updated'   => $c_numbers_updated,
		// ] );

		if ( $e_set_bg_as_default ) {
			update_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id );
		}

		$edit_page = Initializer::get_admin_url( Settings::SLUG_TABLE_CARD )
		             . '&card-group=' . $card_group->id;

		Common::send_success( 'Updated successfully.', $edit_page );

	}

	// </editor-fold desc="Table Cards">

	// <editor-fold desc="Gap Cards">
	public function ajax_admin_update_gap_card( $post ): void {

		$all                 = $post[ Common::VAR_2 ];
		$e_cards             = $all['cards'];
		$e_card_group        = $all['cardGroup'];
		$e_deck              = $e_card_group['deck'];
		$e_collection        = $e_card_group['collection'];
		$e_topic             = $e_card_group['topic'];
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
		if ( empty( $e_topic ) ) {
			Common::send_error( 'Please select a topic' );
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
		$cg_id      = (int) sanitize_text_field( $e_card_group['id'] );
		$card_group = CardGroup::find( $cg_id );
		if ( empty( $card_group ) ) {
			Common::send_error( 'Invalid Card group' );
		}

		$e_topic_id = $e_topic['id'];
		$topic      = Topic::find( $e_topic_id );
		if ( empty( $topic ) ) {
			Common::send_error( 'Invalid topic' );
		}
		$e_collection_id = null;
		if ( is_array( $e_collection ) ) {
			$e_collection_id = $e_collection['id'];
		}

		Manager::beginTransaction();
		$card_group->whole_question = $whole_question;
		$card_group->scheduled_at   = $schedule_at;
		$card_group->bg_image_id    = $bg_image_id;
		$card_group->name           = $cg_name;
		$card_group->deck_id        = $e_deck_id;
		$card_group->reverse        = false;
		if ( $e_collection_id ) {
			$card_group->collection_id = $e_collection_id;
		}
		if ( $e_topic_id ) {
			$card_group->topic_id = $e_topic_id;
		}
		$card_group->save();
		$card_group->tags()->detach();
		foreach ( $e_tags as $one ) {
			$tag_id = $one['id'];
			$tag    = Tag::find( $tag_id );
			if ( ! empty( $tag ) ) {
				$card_group->tags()->save( $tag );
			}
		}
		$c_numbers_updated = array();
		foreach ( $e_cards as $one_card ) {
			$question = $one_card['question'];
			$answer   = $one_card['answer'];
			$c_number = $one_card['c_number'];
			$card_id  = $one_card['id'];
			$hash     = $one_card['hash'];
			$card     = new Card();
			if ( ! empty( $card_id ) ) {
				$card = Card::find( $card_id );
				if ( empty( $card ) ) {
					$card = new Card();
				}
			}
			$card->question      = $question;
			$card->answer        = $answer;
			$card->hash          = $hash;
			$card->c_number      = $c_number;
			$card->card_group_id = $card_group->id;
			$card->save();
			$c_numbers_updated[] = $c_number;
			// Common::send_error( [
			// 'ajax_admin_create_new_basic_card',
			// 'post'                 => $post,
			// '$one_card'            => $one_card,
			// '$card_id'            => $card_id,
			// 'toSql'                => $card_group->toSql(),
			// '$reverse'             => $reverse,
			// '$question'            => $question,
			// '$e_card_group'        => $e_card_group,
			// '$whole_question'      => $whole_question,
			// '$e_set_bg_as_default' => $e_set_bg_as_default,
			// '$bg_image_id'         => $bg_image_id,
			// '$answer'              => $answer,
			// '$deck'                => $deck,
			// '$card'                => $card,
			// '$cg_name'             => $cg_name,
			// '$e_tags'              => $e_tags,
			// '$schedule_at'         => $schedule_at,
			// ] );
		}
		Manager::commit();
		// Delete cards without not updated
		$all_cards = CardGroup::find( $cg_id )->cards()
		                      ->whereNotIn( 'c_number', $c_numbers_updated )
		                      ->forceDelete();

		if ( $e_set_bg_as_default ) {
			update_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id );
		}

		$edit_page = Initializer::get_admin_url( Settings::SLUG_GAP_CARD )
		             . '&card-group=' . $card_group->id;

		Common::send_success( 'Updated successfully.', $edit_page );

	}

	public function ajax_admin_create_new_gap_card( $post ): void {

		$all                 = $post[ Common::VAR_2 ];
		$e_cards             = $all['cards'];
		$e_card_group        = $all['cardGroup'];
		$e_deck              = $e_card_group['deck'];
		$e_collection        = $e_card_group['collection'];
		$e_topic             = $e_card_group['topic'];
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
		if ( empty( $e_topic ) ) {
			Common::send_error( 'Please select a topic' );
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
		$e_topic_id = $e_topic['id'];
		$topic      = Topic::find( $e_topic_id );
		if ( empty( $topic ) ) {
			Common::send_error( 'Invalid topic' );
		}
		$e_collection_id = null;
		if ( is_array( $e_collection ) ) {
			$e_collection_id = $e_collection['id'];
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
		if ( $e_collection_id ) {
			$card_group->collection_id = $e_collection_id;
		}
		if ( $e_topic_id ) {
			$card_group->topic_id = $e_topic_id;
		}
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
			$c_number            = $one_card['c_number'];
			$card                = new Card();
			$card->question      = $question;
			$card->hash          = $hash;
			$card->answer        = $answer;
			$card->c_number      = $c_number;
			$card->card_group_id = $card_group->id;
			$card->save();
			// Common::send_error( [
			// 'ajax_admin_create_new_basic_card',
			// 'post'                 => $post,
			// '$one_card'            => $one_card,
			// 'toSql'                => $card_group->toSql(),
			// '$reverse'             => $reverse,
			// '$hash'                => $hash,
			// '$question'            => $question,
			// '$e_card_group'        => $e_card_group,
			// '$whole_question'      => $whole_question,
			// '$e_set_bg_as_default' => $e_set_bg_as_default,
			// '$bg_image_id'         => $bg_image_id,
			// '$answer'              => $answer,
			// '$deck'                => $deck,
			// '$cg_name'             => $cg_name,
			// '$e_tags'              => $e_tags,
			// '$schedule_at'         => $schedule_at,
			// ] );

		}

		Manager::commit();

		if ( $e_set_bg_as_default ) {
			update_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id );
		}
		// Create card group

		// Common::send_error( [
		// 'ajax_admin_create_new_basic_card',
		// 'post'                 => $post,
		// '$e_card'              => $e_card,
		// 'toSql'                => $card_group->toSql(),
		// '$reverse'             => $reverse,
		// '$e_card_group'        => $e_card_group,
		// '$question'            => $question,
		// '$e_set_bg_as_default' => $e_set_bg_as_default,
		// '$bg_image_id'         => $bg_image_id,
		// '$answer'              => $answer,
		// '$deck'                => $deck,
		// '$cg_name'             => $cg_name,
		// '$e_tags'              => $e_tags,
		// '$schedule_at'         => $schedule_at,
		// ] );

		$edit_page = Initializer::get_admin_url( Settings::SLUG_GAP_CARD )
		             . '&card-group=' . $card_group->id;

		Common::send_success( 'Created successfully.', $edit_page );

	}

	public function ajax_admin_load_cards_groups( $post ): void {
		$params         = $post[ Common::VAR_2 ]['params'];
		$per_page       = (int) sanitize_text_field( $params['per_page'] );
		$page           = (int) sanitize_text_field( $params['page'] );
		$search_keyword = sanitize_text_field( $params['search_keyword'] );
		$status         = sanitize_text_field( $params['status'] );
		$card_groups    = CardGroup::get_card_groups(
			array(
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			)
		);
		$totals         = CardGroup::get_totals();

		Common::send_success(
			'Card group loaded.',
			array(
				'details' => $card_groups,
				'totals'  => $totals,
			),
			array(// 'post' => $post,
			)
		);

	}

	// </editor-fold desc="Gap Cards">

	// <editor-fold desc="Basic Cards">
	public function ajax_admin_delete_card_group( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_trash_cards',
		// 'post' => $post,
		// ] );

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'card_groups' => array(),
			)
		);
		Manager::beginTransaction();
		foreach ( $args['card_groups'] as $card_group ) {
			$id    = (int) sanitize_text_field( $card_group['id'] );
			$group = CardGroup::with( 'cards', 'tags' )->withTrashed()->find( $id );
			$group->tags()->detach();
			$group->cards()->withTrashed()->forceDelete();
			$group->forceDelete();
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'        => $post,
			// '$card_group' => $card_group,
			// '$cards'      => $cards,
			// '$all'        => $all,
			// '$id'         => $id,
			// '$args'       => $args,
			// '$group'      => $group,
			// ] );
		}

		Manager::commit();

		Common::send_success( 'Deleted successfully.' );

	}

	public function ajax_admin_restore_card_group( $post ): void {
		// Common::send_error([
		// 'ajax_admin_restore_card_group',
		// 'post' => $post,
		// ]);

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'card_groups' => array(),
			)
		);
		Manager::beginTransaction();
		foreach ( $args['card_groups'] as $card_group ) {
			$id    = (int) sanitize_text_field( $card_group['id'] );
			$group = CardGroup::withTrashed()->with( 'cards' )->find( $id );

			$the_cards = $group->cards()->withTrashed()->get();
			foreach ( $the_cards as $card ) {
				$card->answered()->withTrashed()->restore();
				$card->restore();
				// Common::send_error([
				// 'ajax_admin_trash_cards',
				// 'post'              => $post,
				// '$the_cards'        => $the_cards,
				// '$card->answered()' => $card->answered()->get(),
				// ]);
			}
			$group->restore();
			// Common::send_error([
			// 'ajax_admin_trash_cards',
			// 'post'       => $post,
			// '$the_cards' => $the_cards,
			// ]);

			// $group->cards()->each(function($card){
			// $card->answered()->delete();
			// });
			// Deck::query()->where( 'id', '=', $id )->delete();
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'  => $post,
			// '$card_group'  => $card_group,
			// '$all'  => $all,
			// '$id'   => $id,
			// '$args' => $args,
			// '$group' => $group,
			// ] );
		}

		Manager::commit();

		Common::send_success( 'Restored successfully.' );

	}

	public function ajax_admin_trash_cards( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_trash_cards',
		// 'post' => $post,
		// ] );

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'card_groups' => array(),
			)
		);
		Manager::beginTransaction();
		foreach ( $args['card_groups'] as $card_group ) {
			$id    = (int) sanitize_text_field( $card_group['id'] );
			$group = CardGroup::with( 'cards' )->find( $id );

			$the_cards = $group->cards()->get();
			foreach ( $the_cards as $card ) {
				$card->answered()->delete();
				$card->delete();
				// Common::send_error([
				// 'ajax_admin_trash_cards',
				// 'post'              => $post,
				// '$the_cards'        => $the_cards,
				// '$card->answered()' => $card->answered()->get(),
				// ]);
			}
			$group->delete();
			// Common::send_error([
			// 'ajax_admin_trash_cards',
			// 'post'       => $post,
			// '$the_cards' => $the_cards,
			// ]);
			// $group->cards()->each(function($card){
			// $card->answered()->delete();
			// });
			// Deck::query()->where( 'id', '=', $id )->delete();
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'  => $post,
			// '$card_group'  => $card_group,
			// '$all'  => $all,
			// '$id'   => $id,
			// '$args' => $args,
			// '$group' => $group,
			// ] );
		}

		Manager::commit();

		Common::send_success( 'Trashed successfully.' );

	}

	public function admin_update_basic_card( $post ): void {
		// Common::send_error( [
		// 'admin_update_basic_card',
		// 'post' => $post,
		// ] );

		$all                 = $post[ Common::VAR_2 ];
		$e_card              = $all['card'];
		$e_card_group        = $all['cardGroup'];
		$e_deck              = $e_card_group['deck'];
		$e_topic             = $e_card_group['topic'];
		$e_card_group_id     = $e_card_group['id'];
		$bg_image_id         = (int) sanitize_text_field( $e_card_group['bg_image_id'] );
		$question            = $e_card_group['whole_question'];
		$answer              = $e_card['answer'];
		$e_set_bg_as_default = $all['set_bg_as_default'];
		$schedule_at         = $e_card_group['scheduled_at'];
		$reverse             = $e_card_group['reverse'];
		$e_cards             = $e_card_group['cards'];
		$e_collection        = $e_card_group['collection'];
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
		if ( empty( $e_topic ) ) {
			Common::send_error( 'Please select a topic' );
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

		$e_topic_id = $e_topic['id'];
		$topic      = Topic::find( $e_topic_id );
		if ( empty( $topic ) ) {
			Common::send_error( 'Invalid topic' );
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

		$collection_id = null;
		if ( is_array( $e_collection ) ) {
			$collection_id = $e_collection['id'];
		}

		Manager::beginTransaction();

		$card_group->whole_question = $question;
		$card_group->scheduled_at   = $schedule_at;
		$card_group->bg_image_id    = $bg_image_id;
		$card_group->name           = $cg_name;
		$card_group->deck_id        = $e_deck_id;
		if ( $collection_id ) {
			$card_group->collection_id = $collection_id;
		}
		if ( $e_topic_id ) {
			$card_group->topic_id = $e_topic_id;
		}
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

		// Common::send_error( [
		// 'ajax_admin_create_new_basic_card',
		// 'post'                 => $post,
		// '$e_card'              => $e_card,
		// 'toSql'                => $card_group->toSql(),
		// '$reverse'             => $reverse,
		// '$card'                => $card,
		// '$e_card_group_id'     => $e_card_group_id,
		// '$e_card_group'        => $e_card_group,
		// '$question'            => $question,
		// '$e_set_bg_as_default' => $e_set_bg_as_default,
		// '$bg_image_id'         => $bg_image_id,
		// '$answer'              => $answer,
		// '$deck'                => $deck,
		// '$cg_name'             => $cg_name,
		// '$e_tags'              => $e_tags,
		// '$schedule_at'         => $schedule_at,
		// ] );

		$edit_page = Initializer::get_admin_url( Settings::SLUG_BASIC_CARD )
		             . '&action=card-edit'
		             . '&card-group=' . $card_group->id;

		Common::send_success( 'Updated successfully.', $edit_page );

	}

	public function ajax_admin_create_new_basic_card( $post ): void {
		$all                 = $post[ Common::VAR_2 ];
		$e_card              = $all['card'];
		$e_card_group        = $all['cardGroup'];
		$e_deck              = $e_card_group['deck'];
		$e_topic             = $e_card_group['topic'];
		$bg_image_id         = (int) sanitize_text_field( $e_card_group['bg_image_id'] );
		$question            = $e_card_group['whole_question'];
		$answer              = $e_card['answer'];
		$hash                = $e_card['hash'];
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
		if ( empty( $e_topic ) ) {
			Common::send_error( 'Please select a topic' );
		}
		if ( empty( $question ) ) {
			Common::send_error( 'Please provide a question' );
		}
		if ( empty( $answer ) ) {
			Common::send_error( 'Please provide an answer' );
		}
		if ( empty( $bg_image_id ) ) {
			$bg_image_id = get_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, 0 );
			if ( empty( $bg_image_id ) ) {
				Common::send_error( 'Please select a background image.' );
			}
		}

		$e_deck_id = $e_card_group['deck']['id'];
		$e_tags    = $e_card_group['tags'];
		$deck      = Deck::find( $e_deck_id );
		if ( empty( $deck ) ) {
			Common::send_error( 'Invalid deck' );
		}
		$e_topic_id = $e_topic['id'];
		$topic      = Topic::find( $e_topic_id );
		if ( empty( $topic ) ) {
			Common::send_error( 'Invalid topic' );
		}

		$collection_id = null;
		if ( ! empty( $e_card_group['collection'] ) ) {
			$collection_id = $e_card_group['collection']['id'];
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
		if ( $collection_id ) {
			$card_group->collection_id = $collection_id;
		}
		if ( $e_topic_id ) {
			$card_group->topic_id = $e_topic_id;
		}
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
		$card->hash          = $hash;
		$card->c_number      = 'c1';
		$card->card_group_id = $card_group->id;
		$card->save();
		Manager::commit();

		if ( $e_set_bg_as_default ) {
			update_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, $bg_image_id );
		}
		// Create card group

		// Common::send_error( [
		// 'ajax_admin_create_new_basic_card',
		// 'post'                 => $post,
		// '$e_card'              => $e_card,
		// 'toSql'                => $card_group->toSql(),
		// '$reverse'             => $reverse,
		// '$e_card_group'        => $e_card_group,
		// '$question'            => $question,
		// '$e_set_bg_as_default' => $e_set_bg_as_default,
		// '$bg_image_id'         => $bg_image_id,
		// '$answer'              => $answer,
		// '$deck'                => $deck,
		// '$cg_name'             => $cg_name,
		// '$e_tags'              => $e_tags,
		// '$schedule_at'         => $schedule_at,
		// ] );

		$edit_page = Initializer::get_admin_url( Settings::SLUG_BASIC_CARD )
		             . '&card-group=' . $card_group->id;

		Common::send_success( 'Created successfully.', $edit_page );

	}

	public function ajax_admin_load_basic_card( $post ): void {
		$all           = $post[ Common::VAR_2 ];
		$card_group_id = (int) sanitize_text_field( $all['card_group_id'] );

		$card_group = CardGroup::with( 'tags', 'cards', 'deck', 'collection', 'topic' )->find( $card_group_id );
		if ( empty( $card_group ) ) {
			Common::send_error( 'Invalid card group' );
		}
		foreach ( $card_group->cards as $card ) {
			$card_type = $card->card_group->card_type;
			if ( in_array( $card_type, array( 'table', 'image' ) ) ) {
				if ( ! is_array( $card->answer ) ) {
					$card->answer = json_decode( $card->answer );
				}
				if ( ! is_array( $card->question ) ) {
					$card->question = json_decode( $card->question );
				}
				if ( ! is_array( $card_group->whole_question ) ) {
					$card_group->whole_question = json_decode( $card_group->whole_question );
				}
			}
		}

		Common::send_success(
			'Loaded successfully.',
			array(
				'card_group' => $card_group,
			)
		);

	}
	// </editor-fold desc="Basic Cards">

	// <editor-fold desc="Tags">
	public function ajax_admin_create_tag( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_create_tag',
		// 'post' => $post,
		// ] );
		Initializer::verify_post( $post, true, true );
		$all  = $post[ Common::VAR_2 ];
		$name = sanitize_text_field( $all['name'] );

		$create = Tag::firstOrCreate( array( 'name' => $name ) );

		// Common::send_error( [
		// 'ajax_admin_create_tag',
		// 'post'  => $post,
		// '$name' => $name,
		// ] );

		Common::send_success( 'Created successfully.', $create );

	}

	public function ajax_admin_update_tags( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_create_tag',
		// 'post' => $post,
		// ] );
		Initializer::verify_post( $post, true, true );
		$all  = $post[ Common::VAR_2 ];
		$tags = $all['tags'];

		foreach ( $tags as $one ) {
			$name = sanitize_text_field( $one['name'] );
			$id   = (int) sanitize_text_field( $one['id'] );

			$tag = Tag::find( $id );
			if ( $tag instanceof Tag ) {
				$tag->name = $name;
				$tag->save();
			}
		}

		// $create = Tag::firstOrCreate( [ 'name' => $name ] );

		// Common::send_error( [
		// 'ajax_admin_create_tag',
		// 'post'  => $post,
		// '$name' => $name,
		// ] );

		Common::send_success( 'Updated successfully.' );

	}

	public function ajax_admin_search_tags( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_load_tags',
		// 'post' => $post,
		// ] );

		$params         = $post[ Common::VAR_2 ]['params'];
		$per_page       = (int) sanitize_text_field( $params['per_page'] );
		$page           = (int) sanitize_text_field( $params['page'] );
		$search_keyword = sanitize_text_field( $params['search_keyword'] );
		$status         = sanitize_text_field( $params['status'] );
		// Common::send_error( [
		// 'ajax_admin_load_tags',
		// 'post'            => $post,
		// '$params'         => $params,
		// '$per_page'       => $per_page,
		// '$page'           => $page,
		// '$search_keyword' => $search_keyword,
		// '$status'         => $status,
		// ] );

		$items  = Tag::get_tags(
			array(
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			)
		);
		$totals = Tag::get_totals();

		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// 'post'            => $post,
		// '$params'         => $params,
		// '$per_page'       => $per_page,
		// '$page'           => $page,
		// '$search_keyword' => $search_keyword,
		// '$deck_groups'    => $deck_groups,
		// '$status'         => $status,
		// ] );

		Common::send_success(
			'Tag loaded.',
			array(
				'details' => $items,
				'totals'  => $totals,
			),
			array(// 'post' => $post,
			)
		);

	}

	public function ajax_admin_load_tags( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_load_tags',
		// 'post' => $post,
		// ] );

		$params         = $post[ Common::VAR_2 ]['params'];
		$per_page       = (int) sanitize_text_field( $params['per_page'] );
		$page           = (int) sanitize_text_field( $params['page'] );
		$search_keyword = sanitize_text_field( $params['search_keyword'] );
		$status         = sanitize_text_field( $params['status'] );
		// Common::send_error( [
		// 'ajax_admin_load_tags',
		// 'post'            => $post,
		// '$params'         => $params,
		// '$per_page'       => $per_page,
		// '$page'           => $page,
		// '$search_keyword' => $search_keyword,
		// '$status'         => $status,
		// ] );

		$items  = Tag::get_tags(
			array(
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			)
		);
		$totals = Tag::get_totals();

		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// 'post'            => $post,
		// '$params'         => $params,
		// '$per_page'       => $per_page,
		// '$page'           => $page,
		// '$search_keyword' => $search_keyword,
		// '$deck_groups'    => $deck_groups,
		// '$status'         => $status,
		// ] );

		Common::send_success(
			'Tag loaded.',
			array(
				'details' => $items,
				'totals'  => $totals,
			),
			array(// 'post' => $post,
			)
		);

	}

	public function ajax_admin_trash_tags( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_trash_tags',
		// 'post' => $post,
		// ] );

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'items' => array(),
			)
		);
		foreach ( $args['items'] as $one ) {
			$id = (int) sanitize_text_field( $one['id'] );
			Tag::query()->where( 'id', '=', $id )->delete();
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'  => $post,
			// '$all'  => $all,
			// '$id'   => $id,
			// '$args' => $args,
			// '$one' => $one,
			// ] );
		}

		Common::send_success( 'Trashed successfully.' );

	}

	public function ajax_admin_delete_tags( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_delete_tags',
		// 'post' => $post,
		// ] );

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'items' => array(),
			)
		);
		foreach ( $args['items'] as $one ) {
			$id = (int) sanitize_text_field( $one['id'] );
			Tag::query()->where( 'id', '=', $id )->forceDelete();
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'  => $post,
			// '$all'  => $all,
			// '$name' => $name,
			// '$id'   => $id,
			// '$args' => $args,
			// ] );
		}

		Common::send_success( 'Deleted.' );

	}

	// </editor-fold desc="Tags">

	// <editor-fold desc="Deck">

	public function ajax_admin_trash_decks( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_trash_deck_group',
		// 'post' => $post,
		// ] );

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'decks' => array(),
			)
		);
		foreach ( $args['decks'] as $item ) {
			$id = (int) sanitize_text_field( $item['id'] );
			Deck::find( $id )->delete();
			// Deck::query()->where( 'id', '=', $id )->delete();
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'  => $post,
			// '$all'  => $all,
			// '$id'   => $id,
			// '$args' => $args,
			// ] );
		}

		Common::send_success( 'Trashed successfully.' );

	}

	public function ajax_admin_delete_decks( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_trash_deck_group',
		// 'post' => $post,
		// ] );

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'decks' => array(),
			)
		);

		foreach ( $args['decks'] as $item ) {
			Manager::beginTransaction();
			$id                    = (int) sanitize_text_field( $item['id'] );
			$uncategorized_deck_id = get_uncategorized_deck_id();
			CardGroup::withTrashed()
			         ->where( 'deck_id', '=', $id )
			         ->update(
				         array(
					         'deck_id' => $uncategorized_deck_id,
				         )
			         );
			$deck = Deck::withTrashed()->find( $id );
			$deck->tags()->detach();
			$deck->forceDelete();
			Manager::commit();
			// Deck::query()->where( 'id', '=', $id )->delete();
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'  => $post,
			// '$all'  => $all,
			// '$id'   => $id,
			// '$args' => $args,
			// ] );
		}

		Common::send_success( 'Deleted successfully.' );

	}

	public function ajax_admin_load_decks( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// 'post' => $post,
		// ] );

		$params         = $post[ Common::VAR_2 ]['params'];
		$per_page       = (int) sanitize_text_field( $params['per_page'] );
		$page           = (int) sanitize_text_field( $params['page'] );
		$search_keyword = sanitize_text_field( $params['search_keyword'] );
		$status         = sanitize_text_field( $params['status'] );
		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// 'post'            => $post,
		// '$params'         => $params,
		// '$per_page'       => $per_page,
		// '$page'           => $page,
		// '$search_keyword' => $search_keyword,
		// '$status'         => $status,
		// ] );

		$decks  = Deck::get_decks(
			array(
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			)
		);
		$totals = Deck::get_totals();

		Common::send_success(
			'Decks loaded.',
			array(
				'details' => $decks,
				'totals'  => $totals,
			),
			array(
				'post' => $post,
			)
		);

	}

	public function ajax_admin_search_decks( $post ): void {

		$params          = $post[ Common::VAR_2 ]['params'];
		$per_page        = (int) sanitize_text_field( $params['per_page'] );
		$page            = (int) sanitize_text_field( $params['page'] );
		$search_keyword  = sanitize_text_field( $params['search_keyword'] );
		$status          = sanitize_text_field( $params['status'] );
		$e_deck_group_id = is_array( $params['deck_group'] ) ? (int) sanitize_text_field( $params['deck_group']['id'] ) : null;

		$items = Deck::get_deck_simple(
			array(
				'search'        => $search_keyword,
				'page'          => $page,
				'per_page'      => $per_page,
				'only_trashed'  => ( 'trash' === $status ) ? true : false,
				'deck_group_id' => $e_deck_group_id,
			)
		);

		Common::send_success( 'Decks  found.', $items );
	}

	public function ajax_admin_create_new_deck( $post ): void {

		$all        = $post[ Common::VAR_2 ];
		$name       = sanitize_text_field( $all['name'] );
		$deck_group = $all['deck_group'];
		$tags       = $all['tags'];

		if ( empty( $deck_group ) ) {
			Common::send_error( 'Please select a deck group' );
		}

		$deck_group_id = (int) sanitize_text_field( $deck_group['id'] );
		try {
			Manager::beginTransaction();
			$deck_group = DeckGroup::find( $deck_group_id );
			$deck       = new Deck();
			$deck->name = $name;
			$deck->deck_group()->associate( $deck_group );
			$deck->save();

			$deck->tags()->detach();
			foreach ( $tags as $one ) {
				$tag = Tag::find( $one['id'] );
				$deck->tags()->save( $tag );
				// Common::send_error( [
				// 'ajax_admin_create_new_deck_group',
				// 'post'           => $post,
				// '$deck_group_id' => $deck_group_id,
				// '$tags'          => $tags,
				// '$name'          => $name,
				// '$tag'           => $tag,
				// '$deck_group'      => $deck_group,
				// ] );
			}
			Manager::commit();
		} catch ( PDOException $e ) {
			Common::send_error( 'Item already exists' );
		}
		// Common::send_error( [
		// 'ajax_admin_create_new_deck_group',
		// 'post'           => $post,
		// '$deck_group_id' => $deck_group_id,
		// '$tags'          => $tags,
		// '$name'          => $name,
		// ] );

		Common::send_success( 'Deck created.' );

	}

	public function ajax_admin_update_decks( $post ): void {

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
			$deck->update(
				array(
					'name'          => $name,
					'deck_group_id' => $deck_group_id,
				)
			);
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'           => $post,
			// '$deck_group_id' => $deck_group_id,
			// '$tags'          => $tags,
			// '$name'          => $name,
			// '$e_deck_group'  => $e_deck_group,
			// ] );
			$deck->tags()->detach();
			foreach ( $tags as $one ) {
				$tag_id = (int) sanitize_text_field( $one['id'] );
				$tag    = Tag::find( $tag_id );
				$deck->tags()->save( $tag );
				// Common::send_error( [
				// 'ajax_admin_create_new_deck_group',
				// 'post'           => $post,
				// '$deck_group_id' => $deck_group_id,
				// '$tags'          => $tags,
				// '$name'          => $name,
				// '$tag'           => $tag,
				// '$deck_group'      => $deck_group,
				// ] );
			}
		}

		Common::send_success( 'Saved.' );

	}

	// </editor-fold desc="Deck">

	// <editor-fold desc="Topic">
	public function ajax_admin_create_new_topic( $post ): void {

		$all  = $post[ Common::VAR_2 ];
		$name = sanitize_text_field( $all['name'] );
		$deck = $all['deck'];
		$tags = $all['tags'];

		if ( empty( $deck ) ) {
			Common::send_error( 'Please select a deck group' );
		}

		$deck_id = (int) sanitize_text_field( $deck['id'] );
		try {
			Manager::beginTransaction();
			$deck        = Deck::find( $deck_id );
			$topic       = new Topic();
			$topic->name = $name;
			$topic->deck()->associate( $deck );
			$topic->save();

			$topic->tags()->detach();
			foreach ( $tags as $one ) {
				$tag = Tag::find( $one['id'] );
				$topic->tags()->save( $tag );
			}
			Manager::commit();
		} catch ( PDOException $e ) {
			Common::send_error( 'Item already exists' );
		}

		Common::send_success( 'Topic created.' );
	}

	public function ajax_admin_trash_topics( $post ): void {

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'topics' => array(),
			)
		);
		foreach ( $args['topics'] as $item ) {
			$id = (int) sanitize_text_field( $item['id'] );
			Topic::find( $id )->delete();
		}

		Common::send_success( 'Trashed successfully.' );

	}

	public function ajax_admin_delete_topics( $post ): void {

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'topics' => array(),
			)
		);

		foreach ( $args['topics'] as $item ) {
			Manager::beginTransaction();
			$id = (int) sanitize_text_field( $item['id'] );

			$deck = Topic::withTrashed()->find( $id );
			$deck->tags()->detach();
			$deck->forceDelete();
			Manager::commit();
		}

		Common::send_success( 'Deleted successfully.' );

	}

	public function ajax_admin_load_topics( $post ): void {
		$params         = $post[ Common::VAR_2 ]['params'];
		$per_page       = (int) sanitize_text_field( $params['per_page'] );
		$page           = (int) sanitize_text_field( $params['page'] );
		$search_keyword = sanitize_text_field( $params['search_keyword'] );
		$status         = sanitize_text_field( $params['status'] );

		$topics = Topic::get_topics(
			array(
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			)
		);
		$totals = Topic::get_totals();

		Common::send_success(
			'Topics loaded.',
			array(
				'details' => $topics,
				'totals'  => $totals,
			),
			array(
				'post' => $post,
			)
		);
	}

	public function ajax_admin_search_topics( $post ): void {
		$params         = $post[ Common::VAR_2 ]['params'];
		$per_page       = (int) sanitize_text_field( $params['per_page'] );
		$page           = (int) sanitize_text_field( $params['page'] );
		$search_keyword = sanitize_text_field( $params['search_keyword'] );
		$status         = sanitize_text_field( $params['status'] );
		$e_deck_id      = is_array( $params['deck'] ) ? (int) sanitize_text_field( $params['deck']['id'] ) : null;

		$items = Topic::get_topic_simple(
			array(
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
				'deck_id'      => $e_deck_id,
			)
		);

		Common::send_success( 'Topics found.', $items );
	}

	public function ajax_admin_update_topics( $post ): void {

		$all = $post[ Common::VAR_2 ];

		$topics = $all['topics'];
		foreach ( $topics as $one_topic ) {
			$name     = sanitize_text_field( $one_topic['name'] );
			$e_deck   = $one_topic['deck'];
			$tags     = $one_topic['tags'];
			$topic_id = (int) sanitize_text_field( $one_topic['id'] );
			$deck_id  = (int) sanitize_text_field( $e_deck['id'] );

			if ( empty( $e_deck ) ) {
				Common::send_error( 'Please select a deck group' );
			}

			$topic = Topic::find( $topic_id );
			$topic->update(
				array(
					'name'    => $name,
					'deck_id' => $deck_id,
				)
			);
			$topic->tags()->detach();
			foreach ( $tags as $one ) {
				$tag_id = (int) sanitize_text_field( $one['id'] );
				$tag    = Tag::find( $tag_id );
				$topic->tags()->save( $tag );
			}
		}

		Common::send_success( 'Saved.' );

	}

	// </editor-fold desc="Topic">

	// <editor-fold desc="Deck Groups">

	public function ajax_admin_search_deck_group( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// 'post' => $post,
		// ] );

		$params         = $post[ Common::VAR_2 ]['params'];
		$per_page       = (int) sanitize_text_field( $params['per_page'] );
		$page           = (int) sanitize_text_field( $params['page'] );
		$search_keyword = sanitize_text_field( $params['search_keyword'] );
		$status         = sanitize_text_field( $params['status'] );
		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// 'post'            => $post,
		// '$params'         => $params,
		// '$per_page'       => $per_page,
		// '$page'           => $page,
		// '$search_keyword' => $search_keyword,
		// '$status'         => $status,
		// ] );

		$deck_groups = DeckGroup::get_deck_groups_simple(
			array(
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			)
		);

		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// 'post'            => $post,
		// '$params'         => $params,
		// '$per_page'       => $per_page,
		// '$page'           => $page,
		// '$search_keyword' => $search_keyword,
		// '$deck_groups'    => $deck_groups,
		// '$status'         => $status,
		// ] );

		Common::send_success( 'Deck group found.', $deck_groups );

	}

	public function ajax_admin_load_deck_group( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// 'post' => $post,
		// ] );

		$params         = $post[ Common::VAR_2 ]['params'];
		$per_page       = (int) sanitize_text_field( $params['per_page'] );
		$page           = (int) sanitize_text_field( $params['page'] );
		$search_keyword = sanitize_text_field( $params['search_keyword'] );
		$status         = sanitize_text_field( $params['status'] );
		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// 'post'            => $post,
		// '$params'         => $params,
		// '$per_page'       => $per_page,
		// '$page'           => $page,
		// '$search_keyword' => $search_keyword,
		// '$status'         => $status,
		// ] );

		$deck_groups = DeckGroup::get_deck_groups(
			array(
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			)
		);
		$totals      = DeckGroup::get_totals();

		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// 'post'            => $post,
		// '$params'         => $params,
		// '$per_page'       => $per_page,
		// '$page'           => $page,
		// '$search_keyword' => $search_keyword,
		// '$deck_groups'    => $deck_groups,
		// '$status'         => $status,
		// ] );

		Common::send_success(
			'Deck group loaded.',
			array(
				'details' => $deck_groups,
				'totals'  => $totals,
			),
			array(
				'post' => $post,
			)
		);

	}

	public function ajax_admin_trash_deck_group( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_trash_deck_group',
		// 'post' => $post,
		// ] );

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'deck_groups' => array(),
			)
		);
		foreach ( $args['deck_groups'] as $group ) {
			$id = (int) sanitize_text_field( $group['id'] );
			DeckGroup::query()->where( 'id', '=', $id )->delete();
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'  => $post,
			// '$all'  => $all,
			// '$id'   => $id,
			// '$args' => $args,
			// ] );
		}

		Common::send_success( 'Trashed successfully.' );

	}

	public function ajax_admin_delete_deck_group( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_delete_deck_group',
		// 'post' => $post,
		// ] );

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'deck_groups' => array(),
			)
		);
		Manager::beginTransaction();
		foreach ( $args['deck_groups'] as $group ) {
			$id = (int) sanitize_text_field( $group['id'] );

			// Assign uncategorized deck group to existing decks under this deck group
			$uncategorized_deck_group_id = get_uncategorized_deck_group_id();
			if ( $uncategorized_deck_group_id ) {
				Deck::where( 'deck_group_id', '=', $id )
				    ->update(
					    array(
						    'deck_group_id' => $uncategorized_deck_group_id,
					    )
				    );
			}
			// Delete the deck group
			$deck_group = DeckGroup::withTrashed()->find( $id );
			$deck_group->tags()->detach();
			$deck_group->forceDelete();

			// DeckGroup::query()->where( 'id', '=', $id )->forceDelete();
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'  => $post,
			// '$all'  => $all,
			// '$name' => $name,
			// '$id'   => $id,
			// '$args' => $args,
			// ] );
		}
		Manager::commit();

		Common::send_success( 'Deleted.' );

	}

	public function ajax_admin_update_deck_group( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_update_deck_group',
		// 'post' => $post,
		// ] );

		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'deck_groups' => array(),
			)
		);
		foreach ( $args['deck_groups'] as $group ) {
			$name       = sanitize_text_field( $group['name'] );
			$id         = (int) sanitize_text_field( $group['id'] );
			$update_id  = DeckGroup::query()->where( 'id', '=', $id )->update(
				array(
					'name' => $name,
				)
			);
			$deck_group = DeckGroup::find( $id );
			$deck_group->tags()->detach();
			foreach ( $group['tags'] as $one ) {
				$tag_id = (int) sanitize_text_field( $one['id'] );
				$tag    = Tag::find( $tag_id );
				$deck_group->tags()->save( $tag );
			}

			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'        => $post,
			// '$all'        => $all,
			// '$name'       => $name,
			// '$id'         => $id,
			// '$args'       => $args,
			// '$group'      => $group,
			// '$deck_group' => $deck_group,
			// '$update_id'  => $update_id,
			// ] );
		}

		Common::send_success( 'Saved.' );

	}

	public function ajax_admin_create_new_deck_group( $post ): void {
		// Common::send_error( [
		// 'ajax_admin_create_new_deck_group',
		// 'post' => $post,
		// ] );

		$all             = $post[ Common::VAR_2 ];
		$deck_group_name = sanitize_text_field( $all['deck_group_name'] );
		$tags            = $all['tags'];

		$create     = DeckGroup::firstOrCreate( array( 'name' => $deck_group_name ) );
		$deck_group = DeckGroup::find( $create->id );
		$deck_group->tags()->detach();
		foreach ( $tags as $one ) {
			$tag = Tag::find( $one['id'] );
			// $tags = $deck_group->tags();
			$deck_group->tags()->save( $tag );
			// $deck_group->tags()->attach($tag);
			// Common::send_error( [
			// 'ajax_admin_create_new_deck_group',
			// 'post'             => $post,
			// 'toSql'             => $deck_group->tags()->toSql(),
			// '$deck_group_name' => $deck_group_name,
			// '$tags'            => $tags,
			// '$tag'            => $tag,
			// '$deck_group'      => $deck_group,
			// ] );
		}

		// Common::send_error( [
		// 'ajax_admin_create_new_deck_group',
		// 'post'             => $post,
		// '$deck_group_name' => $deck_group_name,
		// '$tags'            => $tags,
		// '$deck_group'      => $deck_group,
		// ] );

		Common::send_success( 'Deck group created.' );

	}

	// </editor-fold  desc="Deck Groups" >

	// <editor-fold desc="Others">
	public function ajax_admin_load_image_attachment( $post ): void {

		// Common::send_error( [
		// 'ajax_admin_load_image_attachment',
		// 'post' => $post,
		// ] );

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

		// Common::send_error( [
		// 'ajax_admin_create_new_basic_card',
		// 'post'        => $post,
		// '$id'         => $id,
		// '$attachment_url' => $attachment_url,
		// ] );

		Common::send_success( 'Image found', $attachment_url );

	}
	// </editor-fold desc="Others">

	// <editor-fold desc="Collections">

	public function ajax_admin_trash_collections( $post ): void {
		$all  = $post[ Common::VAR_2 ];
		$args = wp_parse_args(
			$all,
			array(
				'collections' => array(),
			)
		);
		foreach ( $args['collections'] as $item ) {
			$id = (int) sanitize_text_field( $item['id'] );
			Collections::find( $id )->delete();
		}

		Common::send_success( 'Trashed successfully.' );

	}

	/**
	 * Delete the collections and remove the collection id from the card groups.
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public function ajax_admin_delete_collections( $post ): void {

		$all         = $post[ Common::VAR_2 ];
		$collections = $all['collections'];

		foreach ( $collections as $one_collection ) {
			$card_groups = CardGroup::where( 'collection_id', '=', $one_collection['id'] )->get();
			foreach ( $card_groups as $card_group ) {
				$card_group->collection_id = null;
				$card_group->save();
			}
			Collections::withTrashed()->find( $one_collection['id'] )->forceDelete();
		}

		Common::send_success( 'Delete successfully.' );
	}

	public function ajax_admin_load_collections( $post ): void {

		$params         = $post[ Common::VAR_2 ]['params'];
		$per_page       = (int) sanitize_text_field( $params['per_page'] );
		$page           = (int) sanitize_text_field( $params['page'] );
		$search_keyword = sanitize_text_field( $params['search_keyword'] );
		$status         = sanitize_text_field( $params['status'] );

		$collections = Collections::get_collections(
			array(
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			)
		);
		$totals      = Collections::get_totals();

		Common::send_success(
			'Collections loaded.',
			array(
				'details' => $collections,
				'totals'  => $totals,
			),
			array(
				'post' => $post,
			)
		);

	}

	public function ajax_admin_search_collections( $post ): void {
		$params         = $post[ Common::VAR_2 ]['params'];
		$per_page       = (int) sanitize_text_field( $params['per_page'] );
		$page           = (int) sanitize_text_field( $params['page'] );
		$search_keyword = sanitize_text_field( $params['search_keyword'] );
		$status         = sanitize_text_field( $params['status'] );
		$items          = Collections::get_collections_simple(
			array(
				'search'       => $search_keyword,
				'page'         => $page,
				'per_page'     => $per_page,
				'only_trashed' => ( 'trash' === $status ) ? true : false,
			)
		);

		Common::send_success( 'Collections found.', $items );

	}

	public function ajax_admin_create_new_collection( $post ): void {

		$all  = $post[ Common::VAR_2 ];
		$name = sanitize_text_field( $all['name'] );

		try {
			Manager::beginTransaction();
			$collection       = new Collections();
			$collection->name = $name;
			$collection->save();

			Manager::commit();
		} catch ( PDOException $e ) {
			Common::send_error( 'Item already exists' );
		}

		Common::send_success( 'Collection created.' );

	}

	public function ajax_admin_update_collections( $post ): void {

		$all = $post[ Common::VAR_2 ];

		$collections = $all['collections'];
		foreach ( $collections as $one_collection ) {
			$name          = sanitize_text_field( $one_collection['name'] );
			$collection_id = (int) sanitize_text_field( $one_collection['id'] );

			$collection = Collections
				::withTrashed()
				->find( $collection_id );
			$collection->update(
				array(
					'name' => $name,
				)
			);
			// Untrash the collection.
			$collection->restore();
		}

		Common::send_success( 'Saved.' );

	}


	/**
	 * Publish the cards in these collections by just removing the collection id.
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public function ajax_admin_publish_collections( $post ): void {
		$all         = $post[ Common::VAR_2 ];
		$collections = $all['collections'];

		foreach ( $collections as $one_collection ) {
			$card_groups = CardGroup::where( 'collection_id', '=', $one_collection['id'] )->get();
			foreach ( $card_groups as $card_group ) {
				$card_group->collection_id = null;
				$card_group->save();
			}
		}

		Common::send_success( 'Published successfully.' );
	}

	// </editor-fold desc="Collections">

	// <editor-fold desc="User Cards">
	public function ajax_admin_assign_topics( $post ): void {
		// 'selected_cards' | 'selected_group' | 'selected_deck' | 'selected_topic'
		$params            = $post[ Common::VAR_2 ]['params'];
		$what_to_do        = $params['what_to_do'];
		$e_group           = $params['group'];
		$e_deck            = $params['deck'];
		$e_topic           = $params['topic'];
		$e_topic_to_assign = $params['topic_to_assign'];

		if ( ! is_array( $e_topic_to_assign ) ) {
			Common::send_error( 'Please select a topic to assign.' );
		}

		$topic_id_to_assign = (int) sanitize_text_field( $e_topic_to_assign['id'] );

		$message     = 'Cards assigned to topic.';
		$card_groups = [];
		if ( 'selected_cards' === $what_to_do ) {
//			$card_groups = $all['selected_cards'];
		} elseif ( 'selected_group' === $what_to_do ) {
			if ( ! is_array( $e_group ) ) {
				Common::send_error( 'Please select a group.' );
			}
			$deck_group_id = (int) sanitize_text_field( $e_group['id'] );

			// Get card groups under decks that are under this deck group.
			$card_groups = CardGroup
				::whereHas(
					'deck',
					function ( $query ) use ( $deck_group_id ) {
						$query->where( 'deck_group_id', '=', $deck_group_id );
					}
				)
				->get();
			if ( $card_groups ) {
				// Assign the card groups to $topic to assign.
				foreach ( $card_groups as $card_group ) {
					$card_group->topic_id = $topic_id_to_assign;
					$card_group->save();
				}
			}
			$message = $card_groups->count() . ' card groups assigned to topic.';
		} elseif ( 'selected_deck' === $what_to_do ) {

		} elseif ( 'selected_topic' === $what_to_do ) {

		}


		Common::send_success( $message );
	}


	// </editor-fold desc="User Cards">

}
