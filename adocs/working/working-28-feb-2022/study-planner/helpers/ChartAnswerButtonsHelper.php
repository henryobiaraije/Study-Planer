<?php
/**
 * File to handle chart ChartReviewHelper data sourcing
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
use Model\User;
use PDOException;
use PHPMailer\PHPMailer\Exception;
use StudyPlanner\Initializer;
use StudyPlanner\Libs\Common;
use StudyPlanner\Libs\Settings;
use StudyPlanner\Models\Tag;
use StudyPlanner\Services\Card_Due_Date_Service;
use function StudyPlanner\get_all_card_grades;

/**
 * Class ChartAnswerButtonsHelper
 *
 * @package StudyPlanner\Helpers
 */
class ChartAnswerButtonsHelper
{

    public static function get_all_answers_button_clicks($args)
    {
        //todo maybe rewrite later and use sql totally
        $default = [
            'user_id'         => 0,
            'start_date'      => null,
            'end_date'        => null,
            'no_date_limit'   => false,
            'card_ids_not_in' => [],
            'card_ids_in'     => [],
        ];
        $args    = wp_parse_args($args, $default);

        $user = User
            ::with([
                'studies.answers' => function ($query) use ($args) {
                    $query->select('*',
                        // If > 21, is matured Else young
                        Manager::raw('DATEDIFF(DATE(next_due_at),DATE(created_at)) as day_diff'),
                        // Difference in days from today and due date
                        Manager::raw('DATEDIFF(DATE(next_due_at),DATE("'.$args['start_date'].'")) as day_diff_today'),
                    );
                    if ($args['no_date_limit']) {
                        $query->where('created_at', '>=', $args['start_date']);
                    } else {
                        $query->whereBetween('created_at', [$args['start_date'], $args['end_date']]);
                    }
                },
            ])
            //            ->whereHas('studies.answers', function ($query) use ($args) {
            //
            //            })
            ->where('ID', '=', $args['user_id']);
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql'               => $user->toSql(),
        //            '$uuu get'               => $user->get(),
        //            '$args'                  => $args,
        //            'Manager::getQueryLog()' => Manager::getQueryLog(),
        //        ]);

        $answers = $user->get()->first()->studies->pluck('answers')->flatten();
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql' => $user->toSql(),
        //            '$uuu get' => $user->get(),
        //            '$args' => $args,
        //            '$answers' => $answers,
        //            'Manager::getQueryLog()' => Manager::getQueryLog(),
        //        ]);


        return [
            'answers' => $answers,
        ];
    }

    public static function get_all_answers_answered_just_once($user_id)
    {
        $default = [
            'user_id'         => 0,
            'start_date'      => null,
            'end_date'        => null,
            'no_date_limit'   => false,
            'card_ids_not_in' => [],
            'card_ids_in'     => [],
        ];
        //        $args    = wp_parse_args($args, $default);
        //        $_answered_before = Answered
        //            //            ::whereHas('study', function ($query) use ($user_id) {
        //            ////                $query->where('user_id', '=', $user_id)
        //            ////                    ->orderBy('id', 'asc');
        //            //            })
        //            ::where('card_id', '=', 1213)
        //            ->orderBy('id', 'asc')
        //            ->first();
        //
        //
        //        Common::send_error([
        //            __METHOD__,
        //            '$_answered_before' => $_answered_before,
        ////            '$_answered_before sql'         => $_answered_before->toSql(),
        ////            '$_answered_before getBindings' => $_answered_before->getBindings(),
        ////            '$_answered_before get'         => $_answered_before->get(),
        //            'Manager::getQueryLog()'        => Manager::getQueryLog(),
        //        ]);
        $user = User
            ::with([
                'studies'         => function ($query) use ($user_id) {
                    $query->whereHas('answers', function ($query) use ($user_id) {
                        $query
                            ->select(
                                '*',
                                Manager::raw('COUNT(*) as cards_count'),
                                // If > 21, is matured Else young
                                Manager::raw('DATEDIFF(DATE(next_due_at),DATE(created_at)) as day_diff'),
                            // Difference in days from today and due date
                            //                        Manager::raw('DATEDIFF(DATE(next_due_at),DATE("'.$args['start_date'].'")) as day_diff_today'),
                            // Time (seconds) it took to study this card (answer)
                            //                        Manager::raw('TIMESTAMPDIFF(SECOND,TIMESTAMP(started_at),TIMESTAMP(created_at)) as time_diff_second_spent')
                            )
                            ->groupBy('card_id')
                            ->having('cards_count', '=', 1)
                            ->orderBy('id', 'asc');
                        //                    if ($args['no_date_limit']) {
                        //                        $query->where('next_due_at', '>=', $args['start_date']);
                        //                    } else {
                        //                        $query->whereBetween('next_due_at', [$args['start_date'], $args['end_date']]);
                        //                    }
                        //                    $query->where('answered_as_new', '=', true);
                        //                    Common::send_error([
                        //                        __METHOD__,
                        //                        '$query sql'             => $query->toSql(),
                        //                        '$query getBindings'     => $query->getBindings(),
                        //                        '$query get'             => $query->get(),
                        //                        //                        '$query'                 => $args,
                        //                        'Manager::getQueryLog()' => Manager::getQueryLog(),
                        //                    ]);
                    });
                },
                'studies.answers' => function ($query) use ($user_id) {
                    $query
                        ->select(
                            '*',
                            Manager::raw('COUNT(*) as cards_count'),
                            // If > 21, is matured Else young
                            Manager::raw('DATEDIFF(DATE(next_due_at),DATE(created_at)) as day_diff'),
                        // Difference in days from today and due date
                        //                        Manager::raw('DATEDIFF(DATE(next_due_at),DATE("'.$args['start_date'].'")) as day_diff_today'),
                        // Time (seconds) it took to study this card (answer)
                        //                        Manager::raw('TIMESTAMPDIFF(SECOND,TIMESTAMP(started_at),TIMESTAMP(created_at)) as time_diff_second_spent')
                        )
                        ->groupBy('card_id')
                        ->having('cards_count', '=', 1)
                        ->orderBy('id', 'asc');
                    //                    if ($args['no_date_limit']) {
                    //                        $query->where('next_due_at', '>=', $args['start_date']);
                    //                    } else {
                    //                        $query->whereBetween('next_due_at', [$args['start_date'], $args['end_date']]);
                    //                    }
                    //                    $query->where('answered_as_new', '=', true);

                    //                    Common::send_error([
                    //                        __METHOD__,
                    //                        '$query sql'             => $query->toSql(),
                    //                        '$query getBindings'     => $query->getBindings(),
                    //                        '$query get'             => $query->get(),
                    //                        //                        '$query'                 => $args,
                    //                        'Manager::getQueryLog()' => Manager::getQueryLog(),
                    //                    ]);
                },
                //                'studies.answers.study',
                //                'studies.answers.card'
            ])

            //            ->whereHas('studies.answers', function ($query) {
            //                $query
            //                    ->where('answered_as_new', '=', true);
            //            })
            ->where('ID', '=', $user_id);
//        Common::send_error([
//            __METHOD__,
//            '$uuu sql'               => $user->toSql(),
//            '$uuu get'               => $user->get(),
//            //            '$args'                  => $args,
//            'Manager::getQueryLog()' => Manager::getQueryLog(),
//        ]);
        $answers = $user->get()->first()->studies->pluck('answers')->flatten();
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql' => $user->toSql(),
        //            '$uuu get' => $user->get(),
        //            '$args' => $args,
        //            '$answers' => $answers,
        //            'Manager::getQueryLog()' => Manager::getQueryLog(),
        //        ]);


        return [
            'answers' => $answers,
        ];
    }

    public static function get_all_answers_button_clicks2($args)
    {
        $default = [
            'user_id'         => 0,
            'start_date'      => null,
            'end_date'        => null,
            'no_date_limit'   => false,
            'card_ids_not_in' => [],
            'card_ids_in'     => [],
        ];
        $args    = wp_parse_args($args, $default);

        $user = User
            ::with([
                'studies.answers' => function ($query) use ($args) {
                    $query->select('*',
                        // If > 21, is matured Else young
                        Manager::raw('DATEDIFF(DATE(next_due_at),DATE(created_at)) as day_diff'),
                        // Difference in days from today and due date
                        Manager::raw('DATEDIFF(DATE(next_due_at),DATE("'.$args['start_date'].'")) as day_diff_today'),
                    );
                    $query->where('answered_as_new', '=', true);
                    if ($args['no_date_limit']) {
                        $query->where('next_due_at', '>=', $args['start_date']);
                    } else {
                        $query->whereBetween('next_due_at', [$args['start_date'], $args['end_date']]);
                    }
                },
            ])
            ->whereHas('studies.answers', function ($query) use ($args) {
                $query->where('answered_as_new', '=', true);
            })
            ->where('ID', '=', $args['user_id']);
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql'               => $user->toSql(),
        //            //            '$uuu get'               => $user->get(),
        //            '$args'                  => $args,
        //            'Manager::getQueryLog()' => Manager::getQueryLog(),
        //        ]);
        $answers = $user->get()->first()->studies->pluck('answers')->flatten();
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql' => $user->toSql(),
        //            '$uuu get' => $user->get(),
        //            '$args' => $args,
        //            '$answers' => $answers,
        //            'Manager::getQueryLog()' => Manager::getQueryLog(),
        //        ]);


        return [
            'answers' => $answers,
        ];
    }

}