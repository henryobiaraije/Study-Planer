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
class ChartIntervalHelper
{

    public static function get_all_answers_with_next_due_intervals($args)
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
                        Manager::raw('COUNT(*) as day_diff_count'),
                        Manager::raw('DATEDIFF(DATE(next_due_at),DATE(created_at)) as day_diff'),
                    // Difference in days from today and due date
                    //                        Manager::raw('DATEDIFF(DATE(next_due_at),DATE("'.$args['start_date'].'")) as day_diff_today'),
                    );
                    if ($args['no_date_limit']) {
                        $query->where('next_due_at', '>=', $args['start_date']);
                    } else {
                        $query->whereBetween('next_due_at', [$args['start_date'], $args['end_date']]);
                    }
                    $query->groupBy('day_diff');
                    //                    Common::send_error([
                    //                        __METHOD__,
                    //                        '$query sql'             => $query->toSql(),
                    //                        '$uuu get'               => $query->get(),
                    //                        '$query'                 => $query,
                    //                        'Manager::getQueryLog()' => Manager::getQueryLog(),
                    //                    ]);
                },
            ])
            ->where('ID', '=', $args['user_id']);
        //        Common::send_error([
        //            __METHOD__,
        //            '$user sql'              => $user->toSql(),
        //            '$user get'              => $user->get(),
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