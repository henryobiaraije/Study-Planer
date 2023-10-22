<?php
/**
 * File to handle chart ChartReviewHelper data sourcing
 */

namespace StudyPlannerPro\Helpers;

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
use StudyPlannerPro\Initializer;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Libs\Settings;
use StudyPlannerPro\Models\Tag;
use StudyPlannerPro\Services\Card_Due_Date_Service;
use function StudyPlannerPro\get_all_card_grades;

/**
 * Class ChartReviewHelper
 *
 * @package StudyPlannerPro\Helpers
 */
class ChartCardTypes {

    public static function get_all_card_types_matured($args) {
        $default = [
            'user_id'           => 0,
            'start_date'        => null,
            'end_date'          => null,
            'no_date_limit'     => false,
            'matured_card_days' => 27,
        ];
        $args    = wp_parse_args($args, $default);

        $user = User
            ::with([
                'studies.answers' => function ($query) use ($args) {
                    $query
                        ->select('*',
                            // If > 21, is matured Else young
                            Manager::raw('DATEDIFF(DATE(next_due_at),DATE(created_at)) as day_diff'),
                        )
                        ->having('day_diff', '>=', $args['matured_card_days']);
                    //                    $query->where('answered_as_new', '=', true);
                    //                    if ($args['no_date_limit']) {
                    //                        $query->where('created_at', '>=', $args['start_date']);
                    //                    } else {
                    //                        $query->whereBetween('created_at', [$args['start_date'], $args['end_date']]);
                    //                    }

                    //                    Common::send_error([
                    //                        __METHOD__,
                    //                        '$query sql'             => $query->toSql(),
                    //                        '$query get'             => $query->get(),
                    //                        '$query getBindings'             => $query->getBindings(),
                    //                        '$args'                  => $args,
                    //                        'Manager::getQueryLog()' => Manager::getQueryLog(),
                    //                    ]);
                },
            ])
            ->where('ID', '=', $args['user_id']);
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql'               => $user->toSql(),
        //            '$uuu get'               => $user->get(),
        //            'empty get'              => empty($user->get()->all()),
        //            '$args'                  => $args,
        //            'Manager::getQueryLog()' => Manager::getQueryLog(),
        //        ]);
        if (empty($user->get()->all())) {
            $answers = [];
        } else {
            $answers = $user->get()->first()->studies->pluck('answers')->flatten();
        }

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

    public static function get_all_card_types_young($args) {
        $default = [
            'user_id'           => 0,
            'start_date'        => null,
            'end_date'          => null,
            'no_date_limit'     => false,
            'matured_card_days' => 27,
        ];
        $args    = wp_parse_args($args, $default);

        $user = User
            ::with([
                'studies.answers' => function ($query) use ($args) {
                    $query
                        ->select('*',
                            // If > 21, is matured Else young
                            Manager::raw('DATEDIFF(DATE(next_due_at),DATE(created_at)) as day_diff'),
                        )
                        ->having('day_diff', '<', $args['matured_card_days']);
                    //                    $query->where('answered_as_new', '=', true);
                    //                    if ($args['no_date_limit']) {
                    //                        $query->where('created_at', '>=', $args['start_date']);
                    //                    } else {
                    //                        $query->whereBetween('created_at', [$args['start_date'], $args['end_date']]);
                    //                    }

                    //                    Common::send_error([
                    //                        __METHOD__,
                    //                        '$query sql'             => $query->toSql(),
                    //                        '$query get'             => $query->get(),
                    //                        '$query getBindings'             => $query->getBindings(),
                    //                        '$args'                  => $args,
                    //                        'Manager::getQueryLog()' => Manager::getQueryLog(),
                    //                    ]);
                },
            ])
            ->where('ID', '=', $args['user_id']);
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql'               => $user->toSql(),
        //            '$uuu get'               => $user->get(),
        //            'empty get'              => empty($user->get()->all()),
        //            '$args'                  => $args,
        //            'Manager::getQueryLog()' => Manager::getQueryLog(),
        //        ]);
        if (empty($user->get()->all())) {
            $answers = [];
        } else {
            $answers = $user->get()->first()->studies->pluck('answers')->flatten();
        }

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