<?php
/**
 * Front end ajax helper file
 */

namespace StudyPlanner\Helpers;

if (!defined('ABSPATH')) {
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
use Model\StudyLog;
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

    public static function get_instance(): self {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = new self();
        self::$instance->init_ajax();

        return self::$instance;
    }

    private function init_ajax() {
        add_action('front_sp_ajax_front_get_deck_groups', array($this, 'ajax_front_get_deck_groups'));
        add_action('front_sp_ajax_front_create_study', array($this, 'ajax_front_create_study'));
        add_action('front_sp_ajax_front_get_today_questions_in_study', array($this, 'ajax_front_get_today_questions_in_study'));
        add_action('front_sp_ajax_admin_get_timezones', array($this, 'ajax_admin_get_timezones'));
        add_action('front_sp_ajax_admin_update_user_timezone', array($this, 'ajax_admin_update_user_timezone'));
        add_action('front_sp_ajax_front_mark_answer', array($this, 'ajax_front_mark_answer'));
        add_action('front_sp_ajax_front_mark_answer_on_hold', array($this, 'ajax_front_mark_answer_on_hold'));
        add_action('front_sp_ajax_front_load_stats_forecast', array($this, 'ajax_front_load_stats_forecast'));
        add_action('front_sp_ajax_front_load_stats_review_time', array($this, 'ajax_front_load_stats_review_time'));
        add_action('front_sp_ajax_front_get_single_deck_group', array($this, 'ajax_front_get_single_deck_group'));
        add_action('front_sp_ajax_front_record_study_log', array($this, 'ajax_front_record_study_log'));
        add_action('front_sp_ajax_front_load_stats_chart_added', array($this, 'ajax_front_load_stats_chart_added'));
        add_action('front_sp_ajax_front_load_stats_chart_interval', array($this, 'ajax_front_load_stats_chart_interval'));
        add_action('front_sp_ajax_front_load_stats_chart_answer_buttons', array($this, 'ajax_front_load_stats_chart_answer_buttons'));
        add_action('front_sp_ajax_front_load_stats_hourly_breakdown', array($this, 'ajax_front_load_stats_hourly_breakdown'));
        add_action('front_sp_ajax_admin_load_user_profile', array($this, 'ajax_admin_load_user_profile'));
        add_action('front_sp_ajax_front_load_stats_progress_chart', array($this, 'ajax_front_load_stats_progress_chart'));
        add_action('front_sp_ajax_front_load_stats_card_types', array($this, 'ajax_front_load_stats_card_types'));
    }

    /*** <editor-fold desc="Chart Stats"> **/

    public function ajax_front_load_stats_card_types($post): void {
        Initializer::verify_post($post, true);
//                Common::send_error([
//                    'ajax_front_load_stats_card_types',
//                    'post' => $post,
//                ]);

        $all  = $post[Common::VAR_2];
        $span = sanitize_text_field($all['date']);
        //        $span    = 'one_month';
        $user_id = get_current_user_id();

        $all = Study::get_user_stats_card_types($user_id, $span);

        Common::send_success('Stats Card types here', $all);


    }

    public function ajax_front_load_stats_progress_chart($post): void {
        Initializer::verify_post($post, true);
        //        Common::send_error([
        //            'ajax_front_load_stats_progress_chart',
        //            'post' => $post,
        //        ]);

        $all  = $post[Common::VAR_2];
        $year = sanitize_text_field($all['year']);
        //        $span    = 'one_month';
        $user_id = get_current_user_id();

        $all = Study::get_user_stats_progress_chart($user_id, $year);

        Common::send_success('Progress chart here', $all);


    }

    public function ajax_admin_load_user_profile($post): void {
        Initializer::verify_post($post, true);
        //        Common::send_error([
        //            'ajax_admin_load_user_profile',
        //            'post' => $post,
        //        ]);

        $all        = $post[Common::VAR_2];
        $user       = get_user_by('ID', get_current_user_id());
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


        Common::send_success('Profile loaded', $profile);


    }

    public function ajax_front_record_study_log($post): void {
        Initializer::verify_post($post, true);
        //        Common::send_error([
        //            'ajax_front_record_study_log',
        //            'post' => $post,
        //        ]);

        $all      = $post[Common::VAR_2];
        $study_id = (int) sanitize_text_field($all['study_id']);
        $card_id  = (int) sanitize_text_field($all['card_id']);
        $action   = sanitize_text_field($all['action']);
        if (!in_array($action, ['start', 'stop'])) {
            Common::send_error('Invalid action');
        }
        $study = Study::find($study_id);
        if (empty($study)) {
            Common::send_error('Invalid study');
        }
        $card = Card::find($card_id);
        if (empty($card)) {
            Common::send_error('Invalid card.');
        }

        $last_log = StudyLog
            ::where('card_id', '=', $card_id)
            ->where('study_id', '=', $study_id)
            ->limit(1)
            ->orderByDesc('id')->get()->first();

        if (!empty($last_log)) {
            if ('start' === $action) {
                if ('start' === $last_log->action) {
                    $last_log->forceDelete();
                }
            }
            if ('stop' === $action) {
                if ('stop' === $last_log->action) {
                    Common::send_error("Ignore. Cant record 2 stops straight.");
                }
            }
        }

        $new_study_log = StudyLog::create([
            'study_id' => $study_id,
            'card_id'  => $card_id,
            'action'   => $action,
        ]);

        //        Common::send_error([
        //            'ajax_front_load_stats_forecast',
        //            'post'           => $post,
        //            '$new_study_log' => $new_study_log,
        //            '$last_log'      => $last_log,
        //            '$study'         => $study,
        //            '$card'          => $card,
        //            '$action'        => $action,
        //        ]);


        Common::send_success('Log recorded');


    }

    public function ajax_front_load_stats_forecast($post): void {
        Initializer::verify_post($post, true);
        //			Common::send_error( [
        //				'ajax_front_load_stats_forecast',
        //				'post' => $post,
        //			] );

        $all     = $post[Common::VAR_2];
        $span    = sanitize_text_field($all['span']);
        $length  = 30;
        $user_id = get_current_user_id();

        $forecast = Study::get_user_card_forecast($user_id, $span);
        //        Common::send_error([
        //            'ajax_front_load_stats_forecast',
        //            'post'  => $post,
        //            '$span' => $span,
        //        ]);

        Common::send_success('Forecast here', $forecast);

    }

    public function ajax_front_load_stats_chart_added($post): void {
        Initializer::verify_post($post, true);
        //        Common::send_error([
        //            'ajax_front_load_stats_chart_added',
        //            'post' => $post,
        //        ]);

        $all  = $post[Common::VAR_2];
        $span = sanitize_text_field($all['span']);
        //        $span    = 'one_month';
        $user_id = get_current_user_id();

        $all = Study::get_user_stats_charts_added($user_id, $span);
        //        Common::send_error([
        //            'ajax_front_load_stats_review_time',
        //            'post'  => $post,
        //            '$span' => $span,
        //        ]);

        Common::send_success('Charts added here', $all);


    }

    public function ajax_front_load_stats_hourly_breakdown($post): void {
        Initializer::verify_post($post, true);
        //        Common::send_error([
        //            'front_sp_ajax_front_load_stats_hourly_breakdown',
        //            'post' => $post,
        //        ]);

        $all  = $post[Common::VAR_2];
        $date = sanitize_text_field($all['date']);
        //        $span    = 'one_month';
        $user_id = get_current_user_id();

        $all = Study::get_user_stats_charts_hourly_breakdown($user_id, $date);
        //        Common::send_error([
        //            'ajax_front_load_stats_review_time',
        //            'post'  => $post,
        //            '$date' => $date,
        //        ]);

        Common::send_success('Charts hourly breackdown here', $all);


    }

    public function ajax_front_load_stats_chart_answer_buttons($post): void {
        Initializer::verify_post($post, true);
        //        Common::send_error([
        //            'ajax_front_load_stats_chart_answer_buttons',
        //            'post' => $post,
        //        ]);

        $all  = $post[Common::VAR_2];
        $span = sanitize_text_field($all['span']);
        //        $span    = 'one_month';
        $user_id = get_current_user_id();

        $all = Study::get_user_stats_charts_answer_buttons($user_id, $span);
        //        Common::send_error([
        //            'ajax_front_load_stats_review_time',
        //            'post'  => $post,
        //            '$span' => $span,
        //        ]);

        Common::send_success('Charts answer buttons here', $all);


    }

    public function ajax_front_load_stats_chart_interval($post): void {
        Initializer::verify_post($post, true);
        //        Common::send_error([
        //            'ajax_front_load_stats_chart_interval',
        //            'post' => $post,
        //        ]);

        $all  = $post[Common::VAR_2];
        $span = sanitize_text_field($all['span']);
        //        $span    = 'one_month';
        $user_id = get_current_user_id();

        $all = Study::get_user_stats_charts_intervals($user_id, $span);
        //        Common::send_error([
        //            'ajax_front_load_stats_review_time',
        //            'post'  => $post,
        //            '$span' => $span,
        //        ]);

        Common::send_success('Charts intervals here', $all);


    }

    public function ajax_front_load_stats_review_time($post): void {
        Initializer::verify_post($post, true);
        //        Common::send_error([
        //            'ajax_front_load_stats_review_time',
        //            'post' => $post,
        //        ]);

        $all  = $post[Common::VAR_2];
        $span = sanitize_text_field($all['span']);
        //        $span    = 'one_month';
        $user_id = get_current_user_id();

        $review = Study::get_user_card_review_count_and_time($user_id, $span);
        //        Common::send_error([
        //            'ajax_front_load_stats_review_time',
        //            'post'  => $post,
        //            '$span' => $span,
        //        ]);

        Common::send_success('Review here', $review);


    }

    /*** </editor-fold desc="Chart Stats"> **/

    // <editor-fold desc="Gap Cards">

    public function ajax_front_mark_answer_on_hold($post): void {
        Initializer::verify_post($post);
        //			Common::send_error( [
        //				__METHOD__,
        //				'post' => $post,
        //			] );

        $all        = $post[Common::VAR_2];
        $study_id   = (int) sanitize_text_field($all['study_id']);
        $card_id    = (int) sanitize_text_field($all['card_id']);
        $answer     = $all['answer'];
        $all_grades = get_all_card_grades();

        $study = Study::find($study_id);
        if (empty($study)) {
            Common::send_error('Invalid study plan');
        }
        $card = Card::find($card_id);
        if (empty($card)) {
            Common::send_error('Invalid card.');
        }

        $answered_as_new     = false;
        $answered_as_revised = false;

        $_answered_before = Answered::where('card_id', '=', $card_id)->first();
        if (empty($_answered_before)) {
            $answered_as_new = true;
        } else {
            $answered_as_revised = true;
        }

        $study_log = StudyLog
            ::where('study_id', '=', $study_id)
            ->where('card_id', '=', $card_id)
            ->where('action', '=', 'start')
            ->limit(1)
            ->orderByDesc('id')
            ->get()->first();

        Manager::beginTransaction();

        $_tomorro_datetime = new DateTime(Common::getDateTime(1));
        $next_due_date     = $_tomorro_datetime->setTime(0, 0, 0)->format('Y-m-d H:i:s');

        $answer = Answered::create([
            'study_id'            => $study_id,
            'card_id'             => $card_id,
            'answer'              => '',
            'grade'               => 'hold',
            'next_due_at'         => $next_due_date,
            'answered_as_new'     => $answered_as_new,
            'answered_as_revised' => $answered_as_revised,
            'started_at'          => $study_log->created_at,
        ]);
        $study_log->forceDelete();
        Manager::commit();
        Common::send_success('On hold marked');
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

    public function ajax_front_mark_answer($post): void {
        Initializer::verify_post($post);
        //        			Common::send_error( [
        //        				__METHOD__,
        //        				'post' => $post,
        //        			] );

        $all        = $post[Common::VAR_2];
        $study_id   = (int) sanitize_text_field($all['study_id']);
        $card_id    = (int) sanitize_text_field($all['card_id']);
        $answer     = $all['answer'];
        $grade      = sanitize_text_field($all['grade']);
        $all_grades = get_all_card_grades();

        $study = Study::with('deck.card_group')->find($study_id);
        if (empty($study)) {
            Common::send_error('Invalid study plan');
        }
        $card = Card::with('card_group')->find($card_id);
        if (empty($card)) {
            Common::send_error('Invalid card.');
        }

        if (!in_array($grade, $all_grades)) {
            Common::send_error('Invalid grade.');
        }
        $deck = $study->deck;
        if (empty($deck)) {
            Common::send_error('Invalid deck. Probably delete');
        }
        $card_group = $card->card_group;
        if (empty($card_group)) {
            Common::send_error('Invalid card group. Probably delete');
        }

        $answered_as_new     = false;
        $answered_as_revised = false;

        //        $_answered_before = Answered
        //            ::where('card_id', '=', $card_id)
        //            ->
        //            ->first();
        $_answered_before = Answered
            ::where('card_id', '=', $card_id)
            ->where('study_id', '=', $study_id)
            ->first();
        if (empty($_answered_before)) {
            $answered_as_new = true;
        } else {
            $answered_as_revised = true;
        }

        $study_log = StudyLog
            ::where('study_id', '=', $study_id)
            ->where('card_id', '=', $card_id)
            ->where('action', '=', 'start')
            ->limit(1)
            ->orderByDesc('id')
            ->get()->first();

        //        Common::send_error([
        //            'ajax_front_get_question',
        //            'post'                 => $post,
        //            '$study_log'           => $study_log,
        //            '$study_id'            => $study_id,
        //            '$study'               => $study,
        //            '$grade'               => $grade,
        //            '$card'                => $card,
        //            '$all_grades'          => $all_grades,
        //            '$answer'              => $answer,
        //            '$card_group'          => $card_group,
        //            '$answered_as_revised' => $answered_as_revised,
        //        ]);

        Manager::beginTransaction();

        $answer = Answered::create([
            'study_id'            => $study_id,
            'card_id'             => $card_id,
            'answer'              => $answer,
            'grade'               => $grade,
            'answered_as_new'     => $answered_as_new,
            'answered_as_revised' => $answered_as_revised,
            'started_at'          => $study_log->created_at,
            //				'next_due_at' => Common::getDateTime( - 7 ),
        ]);
        $study_log->forceDelete();
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
            $next_due = new Card_Due_Date_Service([
                'card_id'  => $card_id,
                'study_id' => $study_id,
            ]);

            $next_due_date         = $next_due->get_next_due_date();
            $answer->next_due_at   = $next_due_date['next_due_date_morning'];
            $answer->next_interval = $next_due_date['next_interval'];
            $answer->ease_factor   = $next_due_date['ease_factor'];
            $answer->save();

            Manager::commit();

            //				Common::send_error( [
            //					'ajax_front_get_question',
            //					'post'           => $post,
            //					'$study_id'      => $study_id,
            //					'$study'         => $study,
            //					'$grade'         => $grade,
            //					'$card'          => $card,
            //					'$study_log'          => $study_log,
            //					'$all_grades'    => $all_grades,
            //					'$answer'        => $answer,
            //					'$next_due_date' => $next_due_date,
            //				] );
            Common::send_success('Answered.', [
                'debug_display' => $next_due_date['debug_display'],
                'next_interval' => $next_due_date['next_interval'],
            ]);
        } catch (\Exception $e) {
            Common::send_error('Error: '.$e->getMessage());
        }


    }

    public function ajax_front_get_today_questions_in_study($post): void {
        //			Common::send_error( [
        //				'ajax_front_get_question',
        //				'post' => $post,
        //			] );

        $all      = $post[Common::VAR_2]['study'];
        $study_id = (int) sanitize_text_field($all['id']);

        $study = Study::with('tags', 'deck')->find($study_id);
        if (empty($study)) {
            //				dd( $study, $study_id, $post );
            Common::send_error('Invalid study plan');
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

        $all_cards = $study::get_user_cards_to_study($study->id, $user_id);


        //			$all_cards = $user_cards_new['cards'] + $user_cards_revise['cards'];


        //			Common::send_error( [
        //				'ajax_front_create_study',
        //				'post'                   => $post,
        //				'Manager::getQueryLog()' => Manager::getQueryLog(),
        //				'$study_id'              => $study_id,
        //				'$all_cards'             => $all_cards,
        //				'$user_cards_new'        => $user_cards_new,
        //				'$user_cards_revise'     => $user_cards_revise,
        //				'$user_cards_on_hold'    => $user_cards_on_hold,
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


        Common::send_success('Cards loaded.', [
            'user_cards' => [
                'cards' => $all_cards,
            ],
        ]);

    }

    public function ajax_front_create_study($post): void {
        //			Common::send_error( [
        //				'ajax_front_create_study',
        //				'post' => $post,
        //			] );

        $all               = $post[Common::VAR_2]['study'];
        $deck_id           = (int) sanitize_text_field($all['deck']['id']);
        $study_id          = (int) sanitize_text_field($all['id']);
        $tags              = $all['tags'];
        $no_of_new         = (int) sanitize_text_field($all['no_of_new']);
        $no_on_hold        = (int) sanitize_text_field($all['no_on_hold']);
        $no_to_revise      = (int) sanitize_text_field($all['no_to_revise']);
        $revise_all        = (bool) sanitize_text_field($all['revise_all']);
        $study_all_new     = (bool) sanitize_text_field($all['study_all_new']);
        $study_all_on_hold = (bool) sanitize_text_field($all['study_all_on_hold']);
        $all_tags          = (bool) sanitize_text_field($all['all_tags']);

        $deck = Deck::find($deck_id);
        if (empty($deck)) {
            Common::send_error('Invalid deck', [
                'post' => $post,
            ]);
        }
        $study = null;
        if (!empty($study_id)) {
            $study = Study::find($study_id);

            if (empty($study)) {
                Common::send_error('Invalid study plan');
            }
        }

        $user_id = get_current_user_id();

        Manager::beginTransaction();


        //        Common::send_error([
        //            'ajax_front_create_study',
        //            'post' => $post,
        //            '$study_id' => $study_id,
        //            '$study' => $study,
        //            '$deck_id' => $deck_id,
        //            '$tags' => $tags,
        //            '$no_of_new' => $no_of_new,
        //            '$no_on_hold' => $no_on_hold,
        //            '$no_to_revise' => $no_to_revise,
        //            '$revise_all' => $revise_all,
        //            '$study_all_new' => $study_all_new,
        //            '$study_all_on_hold' => $study_all_on_hold,
        //        ]);

        $creating_new = false;
        if (empty($study)) {
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
        //			Common::send_error( [
        //				'ajax_front_create_study',
        //				'post'               => $post,
        //				'$study_id'          => $study_id,
        //				'$study'             => $study,
        //			]);
        if ($creating_new) {
            $study->save();
        } else {
            $study->update();
        }

        $study->tags()->detach();
        foreach ($tags as $one) {
            $tag = Tag::find($one['id']);
            $study->tags()->save($tag);
        }

        Manager::commit();
        $new_study = Study::get_user_study_by_id($study->id);
        //			$new_study = Study::with( 'tags', 'deck' )->find( $study->id );;


        //        Common::send_error([
        //            'ajax_front_create_study',
        //            'post' => $post,
        //            '$study_id' => $study_id,
        //            '$new_study' => $new_study,
        //            '$study' => $study,
        //            '$deck_id' => $deck_id,
        //            '$tags' => $tags,
        //            '$no_of_new' => $no_of_new,
        //            '$no_on_hold' => $no_on_hold,
        //            '$no_to_revise' => $no_to_revise,
        //            '$revise_all' => $revise_all,
        //            '$study_all_new' => $study_all_new,
        //            '$study_all_on_hold' => $study_all_on_hold,
        //        ]);

        Common::send_success('Saved.', $new_study);

    }

    public function ajax_front_get_single_deck_group($post): void {

        //			Common::send_error( [
        //				'ajax_front_get_single_deck_group',
        //				'post' => $post,
        //			] );

        $all     = $post[Common::VAR_2];
        $deck_id = $all['deck_id'];
        $deck    = Deck::with('deck_group')->find($deck_id);
        if (empty($deck)) {
            Common::send_error('Invalid deck');
        }
        $deck_group = $deck->deck_group;
        if (empty($deck_group)) {
            Common::send_error('Invalid deck group');
        }

        $deck_group = DeckGroup::get_deck_groups_front_end_one($deck_group->id);

        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post'        => $post,
        //				'$deck_id'    => $deck_id,
        //				'$deck'       => $deck,
        //				'$deck_group' => $deck_group,
        //			] );

        Common::send_success('Deck group loaded.', $deck_group);

    }

    public function ajax_front_get_deck_groups($post): void {
        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'post' => $post,
        //			] );
        $user_id        = get_current_user_id();
        $params         = $post[Common::VAR_2]['params'];
        $per_page       = (int) sanitize_text_field($params['per_page']);
        $page           = (int) sanitize_text_field($params['page']);
        $search_keyword = sanitize_text_field($params['search_keyword']);
        $status         = sanitize_text_field($params['status']);

        $deck_groups = DeckGroup::get_deck_groups_front_end([
            'search'       => '',//$search_keyword,
            'page'         => 1,//$page,
            'per_page'     => 1000,//$per_page,
            'only_trashed' => false,//( 'trash' === $status ) ? true : false,
        ]);
        $studies     = Study::get_user_studies([
            'user_id'      => $user_id,
            'search'       => '',//$search_keyword,
            'page'         => 1,//$page,
            'per_page'     => 1000,//$per_page,
            'only_trashed' => false,//( 'trash' === $status ) ? true : false,
        ]);

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


        Common::send_success('Deck group loaded.', [
            'details' => $deck_groups,
            'studies' => $studies,
        ]);

    }

    // </editor-fold desc="Gap Cards">

    // <editor-fold desc="Others">
    public function ajax_admin_update_user_timezone($post): void {
        Initializer::verify_post($post, true);

        //			Common::send_error( [
        //				__METHOD__,
        //				'post' => $post,
        //			] );
        $all       = $post[Common::VAR_2];
        $time_zone = sanitize_text_field($all['timezone']);
        $user_id   = get_current_user_id();
        update_user_meta($user_id, Settings::UM_USER_TIMEZONE, $time_zone);
        //			Common::send_error( [
        //				__METHOD__,
        //				'post' => $post,
        //				'$time_zone' => $time_zone,
        //			] );
        Common::send_success('Timezone saved.');

    }

    public function ajax_admin_get_timezones($post): void {
        Initializer::verify_post($post, true);
        $user_id       = get_current_user_id();
        $user_timezone = get_user_meta($user_id, Settings::UM_USER_TIMEZONE, true);
        Common::send_success('Timezones loaded.', [
            'timezones'     => Common::get_time_zones(),
            'user_timezone' => $user_timezone,
        ]);

    }
    // </editor-fold desc="Others">


}