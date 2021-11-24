<?php
	/**
	 * Front end ajax helper file
	 */

	namespace StudyPlanner\Helpers;

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	use DateTime;
	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Eloquent\Model;
	use Model\Answered;
	use Model\Card;
	use Model\CardGroup;
	use Model\CardGroups;
	use Model\Deck;
	use Model\DeckGroup;
	use Model\Study;
	use PDOException;
	use PHPMailer\PHPMailer\Exception;
	use StudyPlanner\Initializer;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Libs\Settings;
	use StudyPlanner\Models\Tag;
	use StudyPlanner\Services\Card_Due_Date_Service;
	use function StudyPlanner\get_all_card_grades;

	/**
	 * Class AjaxFrontHelper
	 *
	 * @package StudyPlanner\Helpers
	 */
	class AjaxFrontHelper {
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
			add_action( 'admin_sp_ajax_front_get_deck_groups', array( $this, 'ajax_front_get_deck_groups' ) );
			add_action( 'admin_sp_ajax_front_create_study', array( $this, 'ajax_front_create_study' ) );
			add_action( 'admin_sp_ajax_front_get_today_questions_in_study', array( $this, 'ajax_front_get_today_questions_in_study' ) );
			add_action( 'admin_sp_ajax_admin_get_timezones', array( $this, 'ajax_admin_get_timezones' ) );
			add_action( 'admin_sp_ajax_admin_update_user_timezone', array( $this, 'ajax_admin_update_user_timezone' ) );
			add_action( 'admin_sp_ajax_front_mark_answer', array( $this, 'ajax_front_mark_answer' ) );
			add_action( 'admin_sp_ajax_front_mark_answer_on_hold', array( $this, 'ajax_front_mark_answer_on_hold' ) );
		}


		// <editor-fold desc="Gap Cards">


		public function ajax_front_mark_answer_on_hold( $post ) : void {
			Initializer::verify_post( $post );
//			Common::send_error( [
//				__METHOD__,
//				'post' => $post,
//			] );

			$all        = $post[ Common::VAR_2 ];
			$study_id   = (int) sanitize_text_field( $all['study_id'] );
			$card_id    = (int) sanitize_text_field( $all['card_id'] );
			$answer     = $all['answer'];
			$grade      = sanitize_text_field( $all['grade'] );
			$all_grades = get_all_card_grades();

			$study = Study::find( $study_id );
			if ( empty( $study ) ) {
				Common::send_error( 'Invalid study plan' );
			}
			$card = Card::find( $card_id );
			if ( empty( $card ) ) {
				Common::send_error( 'Invalid card.' );
			}

			if ( ! in_array( $grade, $all_grades ) ) {
				Common::send_error( 'Invalid grade.' );
			}

			Manager::beginTransaction();

			$_tomorro_datetime = new DateTime( Common::getDateTime(1) );
			$next_due_date = $_tomorro_datetime->setTime( 0, 0, 0 )->format( 'Y-m-d H:i:s' );

			$answer = Answered::create( [
				'study_id'    => $study_id,
				'card_id'     => $card_id,
				'answer'      => $answer,
				'grade'       => $grade,
				'next_due_at' => $next_due_date,
			] );
			Manager::commit();
//			$answer = Answered::create( [
//				'study_id'    => $study_id,
//				'card_id'     => $card_id,
//				'answer'      => $answer,
//				'grade'       => $grade,
//				'next_due_at' => Common::getDateTime( - 4 ),
//			] );
//			$answer = Answered::create( [
//				'study_id'    => $study_id,
//				'card_id'     => $card_id,
//				'answer'      => $answer,
//				'grade'       => $grade,
//				'next_due_at' => Common::getDateTime(-1),
//			] );


		}

		public function ajax_front_mark_answer( $post ) : void {
			Initializer::verify_post( $post );
//			Common::send_error( [
//				__METHOD__,
//				'post' => $post,
//			] );

			$all        = $post[ Common::VAR_2 ];
			$study_id   = (int) sanitize_text_field( $all['study_id'] );
			$card_id    = (int) sanitize_text_field( $all['card_id'] );
			$answer     = $all['answer'];
			$grade      = sanitize_text_field( $all['grade'] );
			$all_grades = get_all_card_grades();

			$study = Study::find( $study_id );
			if ( empty( $study ) ) {
				Common::send_error( 'Invalid study plan' );
			}
			$card = Card::find( $card_id );
			if ( empty( $card ) ) {
				Common::send_error( 'Invalid card.' );
			}

			if ( ! in_array( $grade, $all_grades ) ) {
				Common::send_error( 'Invalid grade.' );
			}

			Manager::beginTransaction();

			$answer = Answered::create( [
				'study_id'    => $study_id,
				'card_id'     => $card_id,
				'answer'      => $answer,
				'grade'       => $grade,
//				'next_due_at' => Common::getDateTime( - 7 ),
			] );
//			$answer = Answered::create( [
//				'study_id'    => $study_id,
//				'card_id'     => $card_id,
//				'answer'      => $answer,
//				'grade'       => $grade,
//				'next_due_at' => Common::getDateTime( - 4 ),
//			] );
//			$answer = Answered::create( [
//				'study_id'    => $study_id,
//				'card_id'     => $card_id,
//				'answer'      => $answer,
//				'grade'       => $grade,
//				'next_due_at' => Common::getDateTime(-1),
//			] );


			try {
				$next_due = new Card_Due_Date_Service( [
					'card_id'  => $card_id,
					'study_id' => $study_id,
				] );

				$next_due_date         = $next_due->get_next_due_date();
				$answer->next_due_at = $next_due_date['next_due_date_morning'];
				$answer->next_interval = $next_due_date['next_interval'];
				$answer->save();
				Manager::commit();

//				Common::send_error( [
//					'ajax_front_get_question',
//					'post'           => $post,
//					'$study_id'      => $study_id,
//					'$study'         => $study,
//					'$grade'         => $grade,
//					'$card'          => $card,
//					'$all_grades'    => $all_grades,
//					'$answer'        => $answer,
//					'$next_due_date' => $next_due_date,
//				] );
				Common::send_success( 'Answered.', [
					'debug_display' => $next_due_date['debug_display'],
					'next_interval' => $next_due_date['next_interval'],
				] );
			} catch ( \Exception $e ) {
				Common::send_error( 'Error: ' . $e->getMessage() );
			}






		}

		public function ajax_front_get_today_questions_in_study( $post ) : void {
//			Common::send_error( [
//				'ajax_front_get_question',
//				'post' => $post,
//			] );

			$all      = $post[ Common::VAR_2 ]['study'];
			$study_id = (int) sanitize_text_field( $all['id'] );

			$study = Study::with( 'tags', 'deck' )->find( $study_id );
			if ( empty( $study ) ) {
				Common::send_error( 'Invalid study plan' );
			}

//			Common::send_error( [
//				'ajax_front_get_question',
//				'post'      => $post,
//				'$study_id' => $study_id,
//				'$study'    => $study,
//			] );

			$tags              = $study->tags;
			$no_of_new         = $study->no_of_new;
			$no_on_hold        = $study->no_on_hold;
			$no_to_revise      = $study->no_to_revise;
			$revise_all        = $study->revise_all;
			$study_all_new     = $study->study_all_new;
			$study_all_on_hold = $study->study_all_on_hold;
			$user_id           = get_current_user_id();

			$user_cards = Study::get_user_cards_new( $study->id, $user_id );


//			Common::send_error( [
//				'ajax_front_create_study',
//				'post'                   => $post,
//				'$user_cards'            => $user_cards->get(),
//				'Manager::getQueryLog()' => Manager::getQueryLog(),
//				'$study_id'              => $study_id,
//				'$study'                 => $study,
//				'$tags'                  => $tags,
//				'$no_of_new'             => $no_of_new,
//				'$no_on_hold'            => $no_on_hold,
//				'$no_to_revise'          => $no_to_revise,
//				'$revise_all'            => $revise_all,
//				'$study_all_new'         => $study_all_new,
//				'$study_all_on_hold'     => $study_all_on_hold,
//				'$user_id'               => $user_id,
//			] );


			Common::send_success( 'Cards loaded.', [
				'user_cards' => $user_cards,
			] );

		}

		public function ajax_front_create_study( $post ) : void {
//			Common::send_error( [
//				'ajax_front_create_study',
//				'post' => $post,
//			] );

			$all               = $post[ Common::VAR_2 ]['study'];
			$deck_id           = (int) sanitize_text_field( $all['deck']['id'] );
			$study_id          = (int) sanitize_text_field( $all['id'] );
			$tags              = $all['tags'];
			$no_of_new         = (int) sanitize_text_field( $all['no_of_new'] );
			$no_on_hold        = (int) sanitize_text_field( $all['no_on_hold'] );
			$no_to_revise      = (int) sanitize_text_field( $all['no_to_revise'] );
			$revise_all        = (bool) sanitize_text_field( $all['revise_all'] );
			$study_all_new     = (bool) sanitize_text_field( $all['study_all_new'] );
			$study_all_on_hold = (bool) sanitize_text_field( $all['study_all_on_hold'] );
			$all_tags          = (bool) sanitize_text_field( $all['all_tags'] );

			$deck = Deck::find( $deck_id );
			if ( empty( $deck ) ) {
				Common::send_error( 'Invalid deck', [
					'post' => $post,
				] );
			}
			$study = null;
			if ( ! empty( $study_id ) ) {
				$study = Study::find( $study_id );
				if ( empty( $study ) ) {
					Common::send_error( 'Invalid study plan' );
				}
			}

			$user_id = get_current_user_id();

			Manager::beginTransaction();


//			Common::send_error( [
//				'ajax_front_create_study',
//				'post'               => $post,
//				'$study_id'          => $study_id,
//				'$study'             => $study,
//				'$deck_id'           => $deck_id,
//				'$tags'              => $tags,
//				'$no_of_new'         => $no_of_new,
//				'$no_on_hold'        => $no_on_hold,
//				'$no_to_revise'      => $no_to_revise,
//				'$revise_all'        => $revise_all,
//				'$study_all_new'     => $study_all_new,
//				'$study_all_on_hold' => $study_all_on_hold,
//			] );

			if ( empty( $study ) ) {
				$study = new Study();
			}
			$study->no_to_revise      = $no_to_revise;
			$study->no_of_new         = $no_of_new;
			$study->no_on_hold        = $no_on_hold;
			$study->revise_all        = $revise_all;
			$study->study_all_new     = $study_all_new;
			$study->study_all_on_hold = $study_all_on_hold;
			$study->user_id           = $user_id;
			$study->deck_id           = $deck_id;
			$study->all_tags          = $all_tags;
//			Common::send_error( [
//				'ajax_front_create_study',
//				'post'               => $post,
//				'$study_id'          => $study_id,
//				'$study'             => $study,
//			]);
			$study->update();

			$study->tags()->detach();
			foreach ( $tags as $one ) {
				$tag = Tag::find( $one['id'] );
				$study->tags()->save( $tag );
			}

			Manager::commit();
			$new_study = Study::get_user_study_by_id( $study->id );
//			$new_study = Study::with( 'tags', 'deck' )->find( $study->id );;


//			Common::send_error( [
//				'ajax_front_create_study',
//				'post'               => $post,
//				'$study_id'          => $study_id,
//				'$new_study'         => $new_study,
//				'$study'             => $study,
//				'$deck_id'           => $deck_id,
//				'$tags'              => $tags,
//				'$no_of_new'         => $no_of_new,
//				'$no_on_hold'        => $no_on_hold,
//				'$no_to_revise'      => $no_to_revise,
//				'$revise_all'        => $revise_all,
//				'$study_all_new'     => $study_all_new,
//				'$study_all_on_hold' => $study_all_on_hold,
//			] );

			Common::send_success( 'Saved.', $new_study );

		}

		public function ajax_front_get_deck_groups( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'post' => $post,
//			] );

			$params         = $post[ Common::VAR_2 ]['params'];
			$per_page       = (int) sanitize_text_field( $params['per_page'] );
			$page           = (int) sanitize_text_field( $params['page'] );
			$search_keyword = sanitize_text_field( $params['search_keyword'] );
			$status         = sanitize_text_field( $params['status'] );

			$deck_groups = DeckGroup::get_deck_groups_front_end( [
				'search'       => '',//$search_keyword,
				'page'         => 1,//$page,
				'per_page'     => 1000,//$per_page,
				'only_trashed' => false,//( 'trash' === $status ) ? true : false,
			] );
			$studies     = Study::get_user_studies( [
				'search'       => '',//$search_keyword,
				'page'         => 1,//$page,
				'per_page'     => 1000,//$per_page,
				'only_trashed' => false,//( 'trash' === $status ) ? true : false,
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


			Common::send_success( 'Deck group loaded.', [
				'details' => $deck_groups,
				'studies' => $studies,
			] );

		}

		// </editor-fold desc="Gap Cards">

		// <editor-fold desc="Others">
		public function ajax_admin_update_user_timezone( $post ) : void {
			Initializer::verify_post( $post, true );

//			Common::send_error( [
//				__METHOD__,
//				'post' => $post,
//			] );
			$all       = $post[ Common::VAR_2 ];
			$time_zone = sanitize_text_field( $all['timezone'] );
			$user_id   = get_current_user_id();
			update_user_meta( $user_id, Settings::UM_USER_TIMEZONE, $time_zone );
//			Common::send_error( [
//				__METHOD__,
//				'post' => $post,
//				'$time_zone' => $time_zone,
//			] );
			Common::send_success( 'Timezone saved.' );

		}

		public function ajax_admin_get_timezones( $post ) : void {
			Initializer::verify_post( $post, true );
			$user_id       = get_current_user_id();
			$user_timezone = get_user_meta( $user_id, Settings::UM_USER_TIMEZONE, true );
			Common::send_success( 'Timezones loaded.', [
				'timezones'     => Common::get_time_zones(),
				'user_timezone' => $user_timezone,
			] );

		}
		// </editor-fold desc="Others">


	}