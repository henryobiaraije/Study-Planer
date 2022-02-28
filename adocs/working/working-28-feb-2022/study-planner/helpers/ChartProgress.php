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
 * Class ChartReviewHelper
 *
 * @package StudyPlanner\Helpers
 */
class ChartProgress {

    public static function get_all_answers_count_by_day($args) {
        $default = [
            'user_id' => 0,
            'year'    => 2021,
        ];
        $args    = wp_parse_args($args, $default);

        $user = User
            ::with([
                'studies.answers' => function ($query) use ($args) {
                    $query
                        ->select(
                            '*',
                            //                            Manager::raw('AVG(DAY(created_at)) as day_average'),
                            Manager::raw('DAYNAME(created_at) as day'),
                            Manager::raw('COUNT(*) as day_answer_count')
                        )
                        ->whereYear('created_at', $args['year'])
                        ->groupBy(Manager::raw('DAY(a.created_at)'),
                            Manager::raw('MONTH(a.created_at)'),
                            Manager::raw('YEAR(a.created_at)'));
                    //                    Common::send_error([
                    //                        __METHOD__,
                    //                        '$query sql'             => $query->toSql(),
                    //                        '$query get'             => $query->get(),
                    //                        '$query'                 => $query,
                    //                        'Manager::getQueryLog()' => Manager::getQueryLog(),
                    //                    ]);
                },
            ])
            ->where('ID', '=', $args['user_id']);
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql'               => $user->toSql(),
        //            '$uuu get'               => $user->get(),
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

    public static function get_days_learnt_count($args) {
        $default = [
            'user_id' => 0,
            'year'    => 2021,
        ];
        $args    = wp_parse_args($args, $default);

        $table = Manager::table(SP_TABLE_ANSWERED.' as a')
            ->join(SP_TABLE_STUDY.' as s', 's.id', '=', 'a.study_id')
            ->join(SP_TABLE_USERS.' as u', 'u.id', '=', 's.user_id')
            ->groupBy(Manager::raw('DAY(a.created_at)'),
                Manager::raw('MONTH(a.created_at)'),
                Manager::raw('YEAR(a.created_at)'))
            ->whereYear('a.created_at', $args['year'])
            ->where('s.user_id','=',$args['user_id'])
            ->select(Manager::raw('DAYNAME(a.created_at) as day'))
            ->select(Manager::raw('COUNT(*) as day_answer_count'))
            ->select(
                Manager::raw('DATE(a.created_at) as date'),
                Manager::raw('COUNT(DAY(a.created_at)) as count'),
                Manager::raw('DAYNAME(a.created_at) as day'),
                Manager::raw('COUNT(*) as day_answer_count')
            )
            ->orderBy('a.id', 'asc');

        $get                    = $table->get();
        $total                  = 0;
        $average                = 0;
        $answers                = [];
        $percentage_days_learnt = 0;
        if (!empty($get->all())) {
            $total                  = $get->sum('day_answer_count');
            $average                = $get->avg('day_answer_count');
            $answers                = $get;
            $percentage_days_learnt = (count($get->all()) / 365) * 100;
        }


        return [
            'answers'                => $answers,
            'total'                  => $total,
            'average'                => $average,
            'percentage_days_learnt' => $percentage_days_learnt,
        ];
    }

    public static function get_days_learnt_streak($args) {
        $default = [
            'user_id' => 0,
            'year'    => 2021,
        ];
        $args    = wp_parse_args($args, $default);

        $table = Manager::table(SP_TABLE_ANSWERED.' as a')
            ->join(SP_TABLE_STUDY.' as s', 's.id', '=', 'a.study_id')
            ->join(SP_TABLE_USERS.' as u', 'u.id', '=', 's.user_id')
            ->where('s.user_id','=',$args['user_id'])
            ->groupBy(
                Manager::raw('DAY(a.created_at)'),
                Manager::raw('MONTH(a.created_at)'),
                Manager::raw('YEAR(a.created_at)')
            )
            //            ->whereYear('a.created_at', $args['year'])
            ->select(
                'a.id',
                Manager::raw('DATE(a.created_at) as the_date'),
            )
            ->orderBy('a.id', 'asc');
//                Common::send_error([
//                    __METHOD__,
//                    '$uuu sql'               => $table->toSql(),
//                    '$uuu get'               => $table->get(),
//                    '$args'                  => $args,
//                    '$table'                 => $table,
//                    'Manager::getQueryLog()' => Manager::getQueryLog(),
//                ]);

        $answers = $table->get();

        return [
            'answers' => $answers,
        ];
    }

}