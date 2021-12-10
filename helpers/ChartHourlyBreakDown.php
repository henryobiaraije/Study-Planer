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
class ChartHourlyBreakDown {

    public static function get_all_answers_hourly_break_down($args) {
        //todo maybe rewrite later and use sql totally
        $default  = [
            'user_id' => 0,
            'date'    => '',
        ];
        $args     = wp_parse_args($args, $default);
        $midnight = new DateTime($args['date']);
        $midnight->setTime(23, 59, 59);
        $midnight = $midnight->format('Y-m-d H:i:s');
        $user     = User
            ::with([
                'studies.answers' => function ($query) use ($args, $midnight) {
                    $query->select('*',
                        // If > 21, is matured Else young
                        Manager::raw('DATEDIFF(DATE(next_due_at),DATE(created_at)) as day_diff'),
                    );
                    $query->whereBetween('created_at', [$args['date'], $midnight]);
                    Common::send_error([
                        __METHOD__,
                        '$query getBindings'     => $query->getBindings(),
                        '$query sql'             => $query->toSql(),
                        '$query get'             => $query->get(),
                        '$args'                  => $args,
                        '$midnight'              => $midnight,
                        'Manager::getQueryLog()' => Manager::getQueryLog(),
                    ]);
                },
            ])
            //            ->whereHas('studies.answers', function ($query) use ($args) {
            //
            //            })
            ->where('ID', '=', $args['user_id']);
        Common::send_error([
            __METHOD__,
            '$uuu sql'               => $user->toSql(),
            '$uuu get'               => $user->get(),
            '$args'                  => $args,
            'Manager::getQueryLog()' => Manager::getQueryLog(),
        ]);

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