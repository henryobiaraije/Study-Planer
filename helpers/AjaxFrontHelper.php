<?php
/**
 * Front end ajax helper file
 */

namespace StudyPlannerPro\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DateTime;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Model\Answered;
use Model\AnswerLog;
use Model\Card;
use Model\CardGroup;
use Model\Deck;
use Model\DeckGroup;
use Model\Study;
use Model\StudyLog;
use Model\User;
use PDOException;
use PHPMailer\PHPMailer\Exception;
use StudyPlannerPro\Initializer;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Libs\Settings;
use StudyPlannerPro\Models\Tag;
use StudyPlannerPro\Models\UserCard;
use StudyPlannerPro\Services\Card_Due_Date_Service;
use function StudyPlannerPro\get_all_card_grades;
use function StudyPlannerPro\get_card_group_background_image;
use function StudyPlannerPro\sp_get_user_debug_form;
use function StudyPlannerPro\sp_save_user_debug_form;
use function StudyPlannerPro\sp_save_user_ignored_card_groups;

/**
 * Class AjaxFrontHelper
 *
 * @package StudyPlannerPro\Helpers
 */
class AjaxFrontHelper {
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

	private function init_ajax(): void {
		// <editor-fold desc="Others">
		add_action( 'front_sp_pro_ajax_front_get_deck_groups', array( $this, 'ajax_front_get_deck_groups' ) );
		add_action( 'front_sp_pro_ajax_front_create_study', array( $this, 'ajax_front_create_study' ) );
		add_action( 'front_sp_pro_ajax_front_get_today_questions_in_study', array(
			$this,
			'ajax_front_get_today_questions_in_study'
		) );
		add_action( 'front_sp_pro_ajax_admin_get_timezones', array( $this, 'ajax_admin_get_timezones' ) );
		add_action( 'front_sp_pro_ajax_admin_update_user_timezone', array( $this, 'ajax_admin_update_user_timezone' ) );
		add_action( 'front_sp_pro_ajax_front_mark_answer', array( $this, 'ajax_front_mark_answer' ) );
		add_action( 'front_sp_pro_ajax_front_mark_answer_on_hold', array( $this, 'ajax_front_mark_answer_on_hold' ) );
		add_action( 'front_sp_pro_ajax_front_load_stats_forecast', array( $this, 'ajax_front_load_stats_forecast' ) );
		add_action( 'front_sp_pro_ajax_front_load_stats_review_time', array(
			$this,
			'ajax_front_load_stats_review_time'
		) );
		add_action( 'front_sp_pro_ajax_front_get_single_deck_group', array(
			$this,
			'ajax_front_get_single_deck_group'
		) );
		add_action( 'front_sp_pro_ajax_front_record_study_log', array( $this, 'ajax_front_record_study_log' ) );
		add_action( 'front_sp_pro_ajax_front_load_stats_chart_added', array(
			$this,
			'ajax_front_load_stats_chart_added'
		) );
		add_action( 'front_sp_pro_ajax_front_load_stats_chart_interval', array(
			$this,
			'ajax_front_load_stats_chart_interval'
		) );
		add_action( 'front_sp_pro_ajax_front_load_stats_chart_answer_buttons', array(
			$this,
			'ajax_front_load_stats_chart_answer_buttons'
		) );
		add_action( 'front_sp_pro_ajax_front_load_stats_hourly_breakdown', array(
			$this,
			'ajax_front_load_stats_hourly_breakdown'
		) );
		add_action( 'front_sp_pro_ajax_admin_load_user_profile', array( $this, 'ajax_admin_load_user_profile' ) );
		add_action( 'front_sp_pro_ajax_front_load_stats_progress_chart', array(
			$this,
			'ajax_front_load_stats_progress_chart'
		) );
		add_action( 'front_sp_pro_ajax_front_load_stats_card_types', array(
			$this,
			'ajax_front_load_stats_card_types'
		) );
		// </editor-fold desc="Others">

		// <editor-fold desc="User Cards ">
		add_action( 'admin_sp_pro_ajax_front_add_user_cards', array( $this, 'ajax_front_add_user_cards' ) );
		add_action( 'admin_sp_pro_ajax_front_remove_card', array( $this, 'ajax_front_remove_card' ) );
		add_action( 'admin_sp_pro_ajax_front_load_user_cards', array( $this, 'ajax_front_load_user_cards' ) );
		add_action( 'admin_sp_pro_ajax_front_save_user_debug_form', array( $this, 'ajax_front_save_user_debug_form' ) );
		add_action( 'admin_sp_pro_ajax_front_load_user_debug_form', array( $this, 'ajax_front_load_user_debug_form' ) );
		add_action( 'admin_sp_pro_ajax_front_ignore_cards', array( $this, 'ajax_front_ignore_cards' ) );
		// </editor-fold desc="User Cards ">
		//        add_action('front_sp_pro_ajax_front_accept_changes', array($this, 'ajax_front_accept_changes'));
	}

	public function ajax_admin_load_user_profile( $post ): void {
		Initializer::verify_post( $post, true );
		//        Common::send_error([
		//            'ajax_admin_load_user_profile',
		//            'post' => $post,
		//        ]);

		$all        = $post[ Common::VAR_2 ];
		$user       = get_user_by( 'ID', get_current_user_id() );
		$user_name  = $user->user_login;
		$user_email = $user->user_email;
		$profile    = [
			'user_email' => $user_email,
			'user_name'  => $user_name,
		];

		//        Common::send_error([
		//            'ajax_admin_load_user_profile',
		//            'post'        => $post,
		//            '$user_name'  => $user_name,
		//            '$user_email' => $user_email,
		//        ]);


		Common::send_success( 'Profile loaded', $profile );


	}

	public function ajax_front_add_user_cards( $post ): void {
		Initializer::verify_post( $post, true );

		$user_id = get_current_user_id();

		$params        = $post[ Common::VAR_2 ]['params'];
		$e_card_groups = $params['cards'];

		if ( empty( $e_card_groups ) ) {
			Common::send_error( 'Please select cards.' );
		}

		$user_debug_form = sp_get_user_debug_form();

		foreach ( $e_card_groups as $a_card_group ) {
			$card_group_id = (int) $a_card_group['id'];
			$card_group    = CardGroup::find( $card_group_id );
			if ( empty( $card_group ) ) {
				Common::send_error( 'Invalid card.' );
			}

			$user_card = UserCard
				::where( 'user_id', '=', $user_id )
				->where( 'card_group_id', '=', $card_group_id )
				->get()->first();
			if ( empty( $user_card ) ) {
				$user_card = new UserCard();
			}

			$user_card->user_id       = $user_id;
			$user_card->card_group_id = $card_group_id;
			$user_card->save();

			$user_card->update( [
				'created_at' => $user_debug_form['current_study_date'],
				'updated_at' => $user_debug_form['current_study_date'],
			] );
		}

		Common::send_success( 'Cards Added successfully', [] );
	}

	// </editor-fold desc="General">

	// <editor-fold desc="Stats">

	public function ajax_front_load_stats_card_types( $post ): void {
		Initializer::verify_post( $post, true );
		//                Common::send_error([
		//                    'ajax_front_load_stats_card_types',
		//                    'post' => $post,
		//                ]);

		$all  = $post[ Common::VAR_2 ];
		$span = sanitize_text_field( $all['date'] );
		//        $span    = 'one_month';
		$user_id = get_current_user_id();

		$all = Study::get_user_stats_card_types( $user_id, $span );

		Common::send_success( 'Stats Card types here', $all );


	}

	public function ajax_front_load_stats_progress_chart( $post ): void {
		Initializer::verify_post( $post, true );

		$all     = $post[ Common::VAR_2 ];
		$year    = sanitize_text_field( $all['year'] );
		$user_id = get_current_user_id();

		$all = Study::get_user_stats_progress_chart( $user_id, $year );

		Common::send_success( 'Progress chart here', $all );


	}

	public function ajax_front_load_stats_forecast( $post ): void {
		Initializer::verify_post( $post, true );

		$all     = $post[ Common::VAR_2 ];
		$span    = sanitize_text_field( $all['span'] );
		$length  = 30;
		$user_id = get_current_user_id();

		$forecast = Study::get_user_card_forecast( $user_id, $span );

		Common::send_success( 'Forecast here', $forecast );

	}

	public function ajax_front_load_stats_chart_added( $post ): void {
		Initializer::verify_post( $post, true );

		$all     = $post[ Common::VAR_2 ];
		$span    = sanitize_text_field( $all['span'] );
		$user_id = get_current_user_id();

		$all = Study::get_user_stats_charts_added( $user_id, $span );

		Common::send_success( 'Charts added here', $all );
	}

	public function ajax_front_load_stats_hourly_breakdown( $post ): void {
		Initializer::verify_post( $post, true );
		//        Common::send_error([
		//            'front_sp_pro_ajax_front_load_stats_hourly_breakdown',
		//            'post' => $post,
		//        ]);

		$all  = $post[ Common::VAR_2 ];
		$date = sanitize_text_field( $all['date'] );
		//        $span    = 'one_month';
		$user_id = get_current_user_id();

		$all = Study::get_user_stats_charts_hourly_breakdown( $user_id, $date );
		//        Common::send_error([
		//            'ajax_front_load_stats_review_time',
		//            'post'  => $post,
		//            '$date' => $date,
		//        ]);

		Common::send_success( 'Charts hourly breackdown here', $all );


	}

	public function ajax_front_load_stats_chart_answer_buttons( $post ): void {
		Initializer::verify_post( $post, true );
		//        Common::send_error([
		//            'ajax_front_load_stats_chart_answer_buttons',
		//            'post' => $post,
		//        ]);

		$all  = $post[ Common::VAR_2 ];
		$span = sanitize_text_field( $all['span'] );
		//        $span    = 'one_month';
		$user_id = get_current_user_id();

		$all = Study::get_user_stats_charts_answer_buttons( $user_id, $span );
		//        Common::send_error([
		//            'ajax_front_load_stats_review_time',
		//            'post'  => $post,
		//            '$span' => $span,
		//        ]);

		Common::send_success( 'Charts answer buttons here', $all );


	}

	public function ajax_front_load_stats_chart_interval( $post ): void {
		Initializer::verify_post( $post, true );
		//        Common::send_error([
		//            'ajax_front_load_stats_chart_interval',
		//            'post' => $post,
		//        ]);

		$all  = $post[ Common::VAR_2 ];
		$span = sanitize_text_field( $all['span'] );
		//        $span    = 'one_month';
		$user_id = get_current_user_id();

		$all = Study::get_user_stats_charts_intervals( $user_id, $span );
		//        Common::send_error([
		//            'ajax_front_load_stats_review_time',
		//            'post'  => $post,
		//            '$span' => $span,
		//        ]);

		Common::send_success( 'Charts intervals here', $all );


	}

	public function ajax_front_load_stats_review_time( $post ): void {
		Initializer::verify_post( $post, true );
		//        Common::send_error([
		//            'ajax_front_load_stats_review_time',
		//            'post' => $post,
		//        ]);

		$all  = $post[ Common::VAR_2 ];
		$span = sanitize_text_field( $all['span'] );
		//        $span    = 'one_month';
		$user_id = get_current_user_id();

		$review = Study::get_user_card_review_count_and_time( $user_id, $span );
		//        Common::send_error([
		//            'ajax_front_load_stats_review_time',
		//            'post'  => $post,
		//            '$span' => $span,
		//        ]);

		Common::send_success( 'Review here', $review );


	}
	// </editor-fold desc="Stats">

	// <editor-fold desc="Dashboard Actions">

	/**
	 * Used to track how long it took the user to answer a question.
	 * Once the question is opened, the "start" study log is recorded.
	 * Then when the answer is submitted, the study log's datetime is used as the $start_time is deleted.
	 * Then the study_log's datetime - answer's datetime is used to calculate the time it took to answer.
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public function ajax_front_record_study_log( $post ): void {
		Initializer::verify_post( $post, true );

		$all      = $post[ Common::VAR_2 ];
		$study_id = (int) sanitize_text_field( $all['study_id'] );
		$card_id  = (int) sanitize_text_field( $all['card_id'] );
		$action   = sanitize_text_field( $all['action'] );
		if ( ! in_array( $action, [ 'start', 'stop' ] ) ) {
			Common::send_error( 'Invalid action' );
		}
		$study = Study::find( $study_id );
		if ( empty( $study ) ) {
			Common::send_error( 'Invalid study' );
		}
		$card = Card::find( $card_id );
		if ( empty( $card ) ) {
			Common::send_error( 'Invalid card.' );
		}

		$last_log = StudyLog
			::where( 'card_id', '=', $card_id )
			->where( 'study_id', '=', $study_id )
			->limit( 1 )
			->orderByDesc( 'id' )->get()->first();

		if ( ! empty( $last_log ) ) {
			if ( 'start' === $action ) {
				if ( 'start' === $last_log->action ) {
					$last_log->forceDelete();
				}
			}
			if ( 'stop' === $action ) {
				if ( 'stop' === $last_log->action ) {
					Common::send_error( "Ignore. Cant record 2 stops straight." );
				}
			}
		}

		$created_at = sp_get_user_debug_form()['current_study_date'];

		$new_study_log = StudyLog::create( array(
			'study_id'   => $study_id,
			'card_id'    => $card_id,
			'action'     => $action,
			'created_at' => $created_at,
		) );

		Common::send_success( 'Log recorded' );
	}

	public function ajax_front_mark_answer_on_hold( $post ): void {
		Initializer::verify_post( $post );

		$all      = $post[ Common::VAR_2 ];
		$study_id = (int) sanitize_text_field( $all['study_id'] );
		$card_id  = (int) sanitize_text_field( $all['card_id'] );
		//        $answer     = $all['answer'];
		//        $all_grades = get_all_card_grades();

		$study = Study::find( $study_id );
		if ( empty( $study ) ) {
			Common::send_error( 'Invalid study plan' );
		}
		$card = Card::find( $card_id );
		if ( empty( $card ) ) {
			Common::send_error( 'Invalid card.' );
		}

		$answered_as_new     = false;
		$answered_as_revised = false;

		$_answered_before = Answered::where( 'card_id', '=', $card_id )->first();
		if ( empty( $_answered_before ) ) {
			$answered_as_new = true;
		} else {
			$answered_as_revised = true;
		}

		$study_log = StudyLog
			::where( 'study_id', '=', $study_id )
			->where( 'card_id', '=', $card_id )
			->where( 'action', '=', 'start' )
			->limit( 1 )
			->orderByDesc( 'id' )
			->get()->first();

		Manager::beginTransaction();

		$_tomorro_datetime = new DateTime( Common::getDateTime( 1 ) );
		$next_due_date     = $_tomorro_datetime->setTime( 0, 0, 0 )->format( 'Y-m-d H:i:s' );

		$debug_user = sp_get_user_debug_form();

		$answer = Answered::create( [
			'study_id'            => $study_id,
			'card_id'             => $card_id,
			'answer'              => '',
			'grade'               => 'hold',
			'next_due_at'         => $next_due_date,
			'answered_as_new'     => $answered_as_new,
			'answered_as_revised' => $answered_as_revised,
			'started_at'          => $study_log->created_at,
			'created_at'          => $debug_user['current_study_date'],
			'updated_at'          => $debug_user['current_study_date'],
		] );
		$study_log->forceDelete();
		Manager::commit();
		Common::send_success( 'On hold marked' );
	}

	public function ajax_front_mark_answer( $post ): void {
		Initializer::verify_post( $post );

		$all                    = $post[ Common::VAR_2 ];
		$study_id               = (int) sanitize_text_field( $all['study_id'] );
		$card_id                = (int) sanitize_text_field( $all['card_id'] );
		$e_answer               = $all['answer'];
		$e_question             = $all['question'];
		$card_whole             = $all['card_whole'];
		$accept_changes_comment = sanitize_text_field( $card_whole['accept_changes_comment'] );
		$new_card               = $card_whole['answer_log']['card'];
		$grade                  = sanitize_text_field( $all['grade'] );
		$all_grades             = get_all_card_grades();

		$study = Study::with( 'deck.card_group' )->find( $study_id );
		if ( empty( $study ) ) {
			Common::send_error( 'Invalid study plan' );
		}
		$card = Card::with( 'card_group' )->find( $card_id );
		if ( empty( $card ) ) {
			Common::send_error( 'Invalid card.' );
		}

		if ( ! in_array( $grade, $all_grades ) ) {
			Common::send_error( 'Invalid grade.' );
		}

		$card_group = $card->card_group;
		if ( empty( $card_group ) ) {
			Common::send_error( 'Invalid card group. Probably delete' );
		}

		$answered_as_new     = false;
		$answered_as_revised = false;

		// Check if answered before.
		$_answered_before = Answered
			::where( 'card_id', '=', $card_id )
			->where( 'study_id', '=', $study_id )
			->first();
		if ( empty( $_answered_before ) ) {
			$answered_as_new = true;
		} else {
			$answered_as_revised = true;
		}

		$study_log = StudyLog
			::where( 'study_id', '=', $study_id )
			->where( 'card_id', '=', $card_id )
			->where( 'action', '=', 'start' )
			->limit( 1 )
			->orderByDesc( 'id' )
			->get()->first();

		Manager::beginTransaction();

		$debug_form = sp_get_user_debug_form();
		$answer     = Answered::create( [
			'study_id'            => $study_id,
			'card_id'             => $card_id,
			'grade'               => $grade,
			'answered_as_new'     => $answered_as_new,
			'answered_as_revised' => $answered_as_revised,
			'started_at'          => $study_log->created_at,
			'created_at'          => $debug_form['current_study_date'],
			'updated_at'          => $debug_form['current_study_date'],
		] );
		$study_log->forceDelete();
		$old_log = AnswerLog
			::where( 'study_id', '=', $study_id )
			->where( 'card_id', '=', $card_id )
			->get()->first();
		if ( empty( $old_log ) ) {
			$old_log = AnswerLog::create( [
				'study_id'   => $study_id,
				'card_id'    => $card_id,
				'created_at' => sp_get_user_debug_form()['current_study_date'],
			] );
		}
		$old_log->update( [
			'last_card_updated_at'    => $new_card['updated_at'],
			'accepted_change_comment' => $accept_changes_comment,
			'question'                => $e_question,
			'answer'                  => $e_answer,
		] );

		try {
			$next_due = new Card_Due_Date_Service( [
				'card_id'  => $card_id,
				'study_id' => $study_id,
			] );

			$next_due_date         = $next_due->get_next_due_date();
			$answer->next_due_at   = $next_due_date['next_due_date_morning'];
			$answer->next_interval = $next_due_date['next_interval'];
			$answer->ease_factor   = $next_due_date['ease_factor'];
			$answer->updated_at    = $debug_form['current_study_date'];
			//            $answer->card_last_updated_at = $card->updated_at;
			$answer->save();

			Manager::commit();

			// Update answer again to update the updated_at field with the user's custom date.
			$answer->update( [
				'updated_at' => $debug_form['current_study_date'],
			] );

			Common::send_success( 'Answered.', [
				'debug_display' => $next_due_date['debug_display'],
				'next_interval' => $next_due_date['next_interval'],
			] );
		} catch ( \Exception $e ) {
			Common::send_error( 'Error: ' . $e->getMessage() );
		}
	}

	/**
	 * Returns today's question in study.
	 *
	 * @param $post
	 */
	public function ajax_front_get_today_questions_in_study( $post ): void {

		$all      = $post[ Common::VAR_2 ]['study'];
		$study_id = (int) sanitize_text_field( $all['id'] );

		$study = Study::with( 'tags', 'deck' )->find( $study_id );
		// Make sure the study is valid.
		if ( empty( $study ) ) {
			Common::send_error( 'Invalid study plan' );
		}

		$user_id   = get_current_user_id();
		$all_cards = $study::get_user_cards_to_study( $study->id, $user_id );

		// Loop through the cards to form a displayable array.
		foreach ( $all_cards as $card ) {
			$card_type = $card->card_group->card_type;
			if ( in_array( $card_type, [ 'table', 'image' ] ) ) {
				if ( ! is_array( $card->answer ) ) {
					$card->answer = json_decode( $card->answer );
				}
				if ( ! is_array( $card->question ) ) {
					$card->question = json_decode( $card->question );
				}
				if ( ! is_array( $card->card_group->whole_question ) ) {
					$card->card_group->whole_question = json_decode( $card->card_group->whole_question );
				}
			}

			$has_updated            = false;
			$is_image_or_table      = in_array( $card->card_group->card_type, [ 'image', 'table' ] );
			$old_answer             = null;
			$old_question           = null;
			$last_updated_at        = null;
			$accept_changes_comment = '';

			$last_answer_log = AnswerLog
				::with( 'card' )
				->where( 'study_id', '=', $study_id )
				->where( 'card_id', '=', $card->id )
				->get()->first();
			//            Common::send_error([
			//                'ajax_front_create_study',
			//                'post'               => $post,
			//                '$last_answer_log' => $last_answer_log,
			//            ]);
			if ( ! empty( $last_answer_log ) ) {
				$answer_card              = $last_answer_log->card;
				$log_answer               = $last_answer_log->answer;
				$log_question             = $last_answer_log->question;
				$log_last_card_updated_at = Common::format_datetime( $last_answer_log->last_card_updated_at );
				$accept_changes_comment   = $last_answer_log->accepted_change_comment;
				$card_updated_at          = Common::format_datetime( $answer_card->updated_at );
				$old_answer               = $log_answer;
				$old_question             = $log_question;
				if ( $is_image_or_table ) {
					$old_answer   = is_object( $old_answer ) ? $old_answer : json_decode( $old_answer );
					$old_answer   = is_object( $old_answer ) ? $old_answer : json_decode( $old_answer );
					$old_question = is_object( $old_question ) ? $old_question : json_decode( $old_question );
					$old_question = is_object( $old_question ) ? $old_question : json_decode( $old_question );
					//                    Common::send_error([
					//                        'ajax_front_create_study',
					//                        'post'               => $post,
					//                        '$answer_old_answer' => $answer_old_answer,
					//                        '$decode'            => $decode,
					//                    ]);
				}

				if ( ( $card_updated_at !== $log_last_card_updated_at ) && ( $log_answer !== $answer_card->answer ) ) {
					$has_updated = true;
				}
				//                Common::send_error([
				//                    'ajax_front_create_study',
				//                    'post'                      => $post,
				//                    '$has_updated'              => $has_updated,
				//                    '$old_question'             => $old_question,
				//                    '$old_answer'               => $old_answer,
				//                    '$card_updated_at'          => $card_updated_at,
				//                    '$log_last_card_updated_at' => $log_last_card_updated_at,
				//                    '$accept_changes_comment'   => $accept_changes_comment,
				//                ]);
			}
			//            Common::send_error([
			//                'ajax_front_create_study',
			//                'post'               => $post,
			//                '$last_answer_log' => $last_answer_log,
			//            ]);
			$card->answer_log             = $last_answer_log;
			$card->old_answer             = $old_answer;
			$card->old_question           = $old_question;
			$card->has_updated            = $has_updated;
			$card->accept_changes_comment = $accept_changes_comment;
			$card->accept_change_button   = '';

			$card->card_group->card_group_edit_url = '';
			$card->card_group->bg_image_url        = get_card_group_background_image( $card->card_group->bg_image_id );
			//            Common::send_error([
			//                'ajax_front_create_study',
			//                'post'                   => $post,
			//                '$card'                  => $card,
			//                '$is_image_or_table'     => $is_image_or_table,
			//                'Manager::getQueryLog()' => Manager::getQueryLog(),
			//                '$study_id'              => $study_id,
			//            ]);
		}

		Common::send_success( 'Cards loaded.', [
			'user_cards' => [
				'cards' => $all_cards,
			],
		] );

	}

	/**
	 * Ajax method to handle creation of study.
	 *
	 * @param $post
	 */
	public function ajax_front_create_study( $post ): void {
		//			Common::send_error( [
		//				'ajax_front_create_study',
		//				'post' => $post,
		//			] );
		Initializer::verify_post( $post, true );
		$user_id = get_current_user_id();

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
		$tags_excluded     = $all['tags_excluded'];

		$deck = Deck::find( $deck_id );
		if ( empty( $deck ) ) {
			Common::send_error( 'Invalid deck', [
				'post' => $post,
			] );
		}
		$study = Study
			::where( 'deck_id', '=', $deck_id )
			->where( 'user_id', '=', $user_id )
			->get()->first();


		Manager::beginTransaction();

		$creating_new = false;
		// Create a new study if the study is empty.
		if ( empty( $study ) ) {
			$study        = new Study();
			$creating_new = true;
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

		// Save or update.
		if ( $creating_new ) {
			$study->save();
		} else {
			$study->update();
		}

		// Remove old tags from study.
		$study->tags()->detach();
		// Add the selected tags to the study.
		foreach ( $tags as $one ) {
			$tag = Tag::find( $one['id'] );
			$study->tags()->save( $tag );
			$excluded_key = array_search( $one['id'], array_column( $tags_excluded, 'id' ) );
			// Make sure none of the excluded cards is added as included cards.
			if ( $excluded_key !== false ) {
				unset( $tags_excluded[ $excluded_key ] );
			}
		}

		// Remove all old excluded tags.
		$study->tagsExcluded()->detach();
		// Add all selected excluded tags.
		foreach ( $tags_excluded as $one ) {
			$tag = Tag::find( $one['id'] );
			$study->tagsExcluded()->save( $tag );
		}

		// Commit changes.
		Manager::commit();

		// Study to return.
		$new_study = Study::get_user_study_by_id( $study->id );
		Common::send_success( 'Saved.', $new_study );

	}

	public function ajax_front_get_single_deck_group( $post ): void {

		//			Common::send_error( [
		//				'ajax_front_get_single_deck_group',
		//				'post' => $post,
		//			] );

		$all     = $post[ Common::VAR_2 ];
		$deck_id = $all['deck_id'];
		$deck    = Deck::with( 'deck_group' )->find( $deck_id );
		if ( empty( $deck ) ) {
			Common::send_error( 'Invalid deck' );
		}
		$deck_group = $deck->deck_group;
		if ( empty( $deck_group ) ) {
			Common::send_error( 'Invalid deck group' );
		}

		$deck_group = DeckGroup::get_deck_groups_front_end_one( $deck_group->id );

		//			Common::send_error( [
		//				'ajax_admin_load_deck_group',
		//				'post'        => $post,
		//				'$deck_id'    => $deck_id,
		//				'$deck'       => $deck,
		//				'$deck_group' => $deck_group,
		//			] );

		Common::send_success( 'Deck group loaded.', $deck_group );

	}

	public function ajax_front_get_deck_groups( $post ): void {
		//			Common::send_error( [
		//				'ajax_admin_load_deck_group',
		//				'post' => $post,
		//			] );
		$user_id        = get_current_user_id();
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
			'user_id'      => $user_id,
			'search'       => '',//$search_keyword,
			'page'         => 1,//$page,
			'per_page'     => 1000,//$per_page,
			'only_trashed' => false,//( 'trash' === $status ) ? true : false,
		] );

		//        Common::send_error([
		//            'ajax_admin_load_deck_group',
		//            'post' => $post,
		//            '$user_id' => $user_id,
		//            '$params' => $params,
		//            '$studies' => $studies,
		//            '$per_page' => $per_page,
		//            '$page' => $page,
		//            '$search_keyword' => $search_keyword,
		//            '$deck_groups' => $deck_groups,
		//            '$status' => $status,
		//        ]);


		Common::send_success( 'Deck group loaded.', [
			'details' => $deck_groups,
			'studies' => $studies,
		] );

	}

	// </editor-fold desc="Gap Cards">

	// <editor-fold desc="Others">
	public function ajax_admin_update_user_timezone( $post ): void {
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

	public function ajax_admin_get_timezones( $post ): void {
		Initializer::verify_post( $post, true );
		$user_id       = get_current_user_id();
		$user_timezone = get_user_meta( $user_id, Settings::UM_USER_TIMEZONE, true );
		Common::send_success( 'Timezones loaded.', [
			'timezones'     => Common::get_time_zones(),
			'user_timezone' => $user_timezone,
		] );

	}
	// </editor-fold desc="Others">

	// <editor-fold desc="User Card">
	public function ajax_front_remove_card( $post ): void {
		// 'selected_cards' | 'selected_group' | 'selected_deck' | 'selected_topic'
		$params = $post[ Common::VAR_2 ]['params'];

		$card_group_ids = $params['card_group_ids'];

		UserCard::where( 'user_id', '=', get_current_user_id() )
		        ->whereIn( 'card_group_id', $card_group_ids )
		        ->forceDelete();


		Common::send_success( 'Removed successfully' );
	}

	public function ajax_front_load_user_cards( $post ): void {
		// 'selected_cards' | 'selected_group' | 'selected_deck' | 'selected_topic'
		$params = $post[ Common::VAR_2 ]['params'];
		Initializer::verify_post( $post, true );

		$user_id           = get_current_user_id();
		$user_card_details = UserCard
			::get_user_cards_to_study( $user_id );

		Common::send_success( 'User cards loaded successfully', $user_card_details );
	}

	public function ajax_front_save_user_debug_form( $post ): void {
		$params = $post[ Common::VAR_2 ]['params'];
		Initializer::verify_post( $post, true );
		$current_study_date = $params['current_study_date'];
		$user_id            = get_current_user_id();

		sp_save_user_debug_form( $user_id, $current_study_date );

		Common::send_success( 'Saved' );
	}

	public function ajax_front_load_user_debug_form( $post ): void {
		Initializer::verify_post( $post, true );
		$user_id = get_current_user_id();

		$user_debug_form = sp_get_user_debug_form( $user_id );

		Common::send_success( 'loaded user debug info', $user_debug_form );
	}

	public function ajax_front_ignore_cards( $post ): void {
		Initializer::verify_post( $post, true );
		$user_id        = get_current_user_id();
		$params         = $post[ Common::VAR_2 ]['params'];
		$card_group_ids = $params['card_group_ids'];

		sp_save_user_ignored_card_groups( $user_id, $card_group_ids );

		Common::send_success( 'Cards Removed' );
	}

	// </editor-fold desc="User Card">


}