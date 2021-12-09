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
class ChartReviewHelper
{

    public static function get_chart_forecast($user_id)
    {

    }

    public static function get_forecast_all_answers_distinct_for_matured_and_young($args)
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

        $user = User::with([
            'studies.answers' => function ($query) use ($args) {
                $query->select('*',
                    // If > 21, is matured Else young
                    Manager::raw('DATEDIFF(DATE(next_due_at),DATE(created_at)) as day_diff'),
                    // Difference in days from today and due date
                    Manager::raw('DATEDIFF(DATE(next_due_at),DATE("'.$args['start_date'].'")) as day_diff_today'),
                    // Time (seconds) it took to study this card (answer)
                    Manager::raw('TIMESTAMPDIFF(SECOND,TIMESTAMP(started_at),TIMESTAMP(created_at)) as time_diff_second_spent')
                );
                $query->groupBy('card_id');
                if ($args['no_date_limit']) {
                    $query->where('next_due_at', '>=', $args['start_date']);
                } else {
                    $query->whereBetween('next_due_at', [$args['start_date'], $args['end_date']]);
                }
                $query->orderByDesc('id');
                //                Common::send_error([
                //                    __METHOD__,
                //                    '$query sql'             => $query->toSql(),
                //                    '$query getBindings'     => $query->getBindings(),
                //                    //            '$uuu get'               => $user->get(),
                //                    '$query'                 => $args,
                //                    'Manager::getQueryLog()' => Manager::getQueryLog(),
                //                ]);
            },
            'studies.answers.study',
            'studies.answers.card'
        ])
            ->where('ID', '=', $args['user_id']);
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql'               => $user->toSql(),
        ////            '$uuu get'               => $user->get(),
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

    public static function get_all_answers_newly_learned($args)
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
                        // Time (seconds) it took to study this card (answer)
                        Manager::raw('TIMESTAMPDIFF(SECOND,TIMESTAMP(started_at),TIMESTAMP(created_at)) as time_diff_second_spent')
                    );
                    if ($args['no_date_limit']) {
                        $query->where('next_due_at', '>=', $args['start_date']);
                    } else {
                        $query->whereBetween('next_due_at', [$args['start_date'], $args['end_date']]);
                    }
                    $query->where('answered_as_new', '=', true);
                    $query->groupBy('card_id');
                    $query->orderByDesc('id');
                    //                Common::send_error([
                    //                    __METHOD__,
                    //                    '$query sql'             => $query->toSql(),
                    //                    '$query getBindings'     => $query->getBindings(),
                    //                    //            '$uuu get'               => $user->get(),
                    //                    '$query'                 => $args,
                    //                    'Manager::getQueryLog()' => Manager::getQueryLog(),
                    //                ]);
                },
                'studies.answers.study',
                'studies.answers.card'
            ])
            //            ->whereHas('studies.answers', function ($query) {
            //                $query
            //                    ->where('answered_as_new', '=', true);
            //            })
            ->where('ID', '=', $args['user_id']);
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql'               => $user->toSql(),
        ////            '$uuu get'               => $user->get(),
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

    public static function get_forecast_all_answers_within_a_date($args)
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
                        // Difference in days from today and due date
                        Manager::raw('DATEDIFF(DATE(created_at),DATE("'.$args['start_date'].'")) as day_diff_today'),
                       );
                    if ($args['no_date_limit']) {
                        $query->where('created_at', '>=', $args['start_date']);
                    } else {
                        $query->whereBetween('created_at', [$args['start_date'], $args['end_date']]);
                    }
                    $query->orderByDesc('id');
                    //                Common::send_error([
                    //                    __METHOD__,
                    //                    '$query sql'             => $query->toSql(),
                    //                    '$query getBindings'     => $query->getBindings(),
                    //                    //            '$uuu get'               => $user->get(),
                    //                    '$query'                 => $args,
                    //                    'Manager::getQueryLog()' => Manager::getQueryLog(),
                    //                ]);
                },
                'studies.answers.study',
                'studies.answers.card'
            ])
            ->where('ID', '=', $args['user_id']);
        //        Common::send_error([
        //            __METHOD__,
        //            '$uuu sql'               => $user->toSql(),
        ////            '$uuu get'               => $user->get(),
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

    /**
     * Just get
     * @return array[]
     */
    public static function get_forecast_cards_new($user_id)
    {
        $all = [];

        $user         = User
            ::with([
                'studies.tags',
                'studies.deck',
            ])
            ->where('ID', '=', $user_id);
        $user_studies = $user->get()->first()->studies;

        foreach ($user_studies as $study) {
            $study_id         = $study->id;
            $all_tags         = $study->all_tags;
            $deck             = $study->deck;
            $tags             = $study->tags;
            $tag_ids          = $tags->pluck('id');
            $query_card_group = CardGroup
                ::with([
                    'cards' => function ($query) use ($user_id) {
                        $query
                            ->whereNotIn('id', function ($q) use ($user_id) {
                                $q
                                    ->select('card_id')
                                    ->from(SP_TABLE_ANSWERED.' as aaa')
                                    ->leftJoin(SP_TABLE_STUDY.' as sss', 'sss.id', '=', 'aaa.study_id')
                                    ->leftJoin(SP_TABLE_USERS.' as uuu', 'uuu.id', '=', 'sss.user_id')
                                    ->where('uuu.id', '=', $user_id)
                                    ->distinct()//todo improve, limit by study_id or user_id
                                ;
                                //                        Common::send_error([
                                //                            __METHOD__,
                                //                            '$q sql' => $q->toSql(),
                                //                            '$q get' => $q->get(),
                                //                            '$q' => $q,
                                //                        ]);
                            });
                    }, 'deck', 'tags'
                ])
                ->whereHas('cards', function ($query) use ($deck, $user_id) {
                    $query
                        ->whereNotIn('id', function ($q) use ($user_id) {
                            $q
                                ->select('aaa.card_id')
                                ->from(SP_TABLE_ANSWERED.' as aaa')
                                ->leftJoin(SP_TABLE_STUDY.' as sss', 'sss.id', '=', 'aaa.study_id')
                                ->leftJoin(SP_TABLE_USERS.' as uuu', 'uuu.id', '=', 'sss.user_id')
                                ->where('uuu.id', '=', $user_id)
                                ->distinct()//todo improve, limit by study_id or user_id
                            ;
                            //                            Common::send_error([
                            //                                __METHOD__,
                            //                                '$q sql' => $q->toSql(),
                            //                                '$q get' => $q->get(),
                            //                                '$q'     => $q,
                            //                            ]);
                        });
                    //                    Common::send_error([
                    //                        __METHOD__,
                    //                        '$query sql' => $query->toSql(),
                    ////                        '$query get' => $query->get(),
                    //                        '$query'     => $query,
                    //                    ]);
                })
                ->whereHas('deck', function ($query) use ($deck) {
                    $query->where('deck_id', '=', $deck->id);
                });
            if (!$all_tags) {
                $query_card_group = $query_card_group->whereHas('tags', function ($query) use ($tag_ids) {
                    $query->whereIn(SP_TABLE_TAGS.'.id', $tag_ids);
                    //                    Common::send_error([
                    //                        __METHOD__,
                    //                        '$query sql' => $query->toSql(),
                    //                        '$query sql getBindings' => $query->getBindings(),
                    //                        '$query get' => $query->get(),
                    //                    ]);
                });
            }
            $card_groups = $query_card_group->get();
            foreach ($card_groups as $card_group) {
                foreach ($card_group->cards as $card) {
                    $card->study = $study;
                }
            }
            $all[] = [
                'study'       => $study,
                'card_groups' => $card_groups,
            ];
            //            Common::send_error([
            //                __METHOD__,
            //                '$user_id'                => $user_id,
            //                '$tag_ids'                => $tag_ids,
            //                '$study'                  => $study,
            //                '$query_card_group toSql' => $query_card_group->toSql(),
            //                '$query_card_group get'   => $query_card_group->get(),
            //                '$tags'                   => $tags,
            //                'user_studies'            => $user_studies,
            //                'Manager::getQueryLog()'  => Manager::getQueryLog(),
            //            ]);
        }


        //        Common::send_error([
        //            __METHOD__,
        //            '$user_id' => $user_id,
        //            'user_studies' => $user_studies,
        //            '$all' => $all,
        //            'Manager::getQueryLog()' => Manager::getQueryLog(),
        //        ]);
        return [
            'all' => $all,
        ];
    }
}