<?php

namespace Model;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use DateInterval;
use DateTime;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\Str;
use PDOException;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use StudyPlanner\Helpers\ChartForecastHelper;
use StudyPlanner\Helpers\ChartReviewHelper;
use StudyPlanner\Libs\Common;
use StudyPlanner\Libs\Settings;
use StudyPlanner\Models\Tag;
use function StudyPlanner\get_user_timezone_date_midnight_today;
use function StudyPlanner\get_user_timezone_minutes_to_add;

class Study extends Model
{
    protected $table = SP_TABLE_STUDY;

    use SoftDeletes;
    use HasRelationships;

    protected $fillable = [
        'user_id',
        'study_all_on_hold',
        'no_to_revise',
        'no_of_new',
        'no_on_hold',
        'revise_all',
        'study_all_new',
        'study_all_on_hold',
    ];

    protected $casts = [
        'revise_all'        => 'boolean',
        'study_all_new'     => 'boolean',
        'study_all_on_hold' => 'boolean',
        'all_tags'          => 'boolean',
    ];


    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', SP_TABLE_TAGGABLES);
    }

    public function deck()
    {
        return $this->belongsTo(Deck::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function cards()
    {
        return $this->hasManyDeep(Card::class, [
            Deck::class,
            CardGroup::class,
        ]);
//			] )->where( 'user_id', '=', $user_id );
    }

    public function answers()
    {
        return $this->hasMany(Answered::class);
    }

    public function cardsGroups()
    {
        return $this->hasManyThrough(CardGroup::class, Deck::class);
    }

    public static function get_user_studies($args): array
    {
        $user_id = get_current_user_id();
        $default = [
            'search'       => '',
            'page'         => 1,
            'per_page'     => 5,
            'with_trashed' => false,
            'only_trashed' => false,
            'user_id'      => 0
        ];
        $args    = wp_parse_args($args, $default);
        $studies = null;
        if ($args['with_trashed']) {
            $studies = Study::withoutTrashed();
        } elseif ($args['only_trashed']) {
            $studies = Study::onlyTrashed();
        } else {
            $studies = Study::where('id', '>', 0);
        }
        $studies->with([
            'tags',
            'deck',
            'user'
        ])
            ->where('user_id', '=', $args['user_id']);
        $total   = $studies->count();
        $offset  = ($args['page'] - 1);
        $studies = $studies->offset($offset)
            ->limit($args['per_page'])
            ->orderByDesc('id');

        $studies = $studies->get();
//        Common::send_error([
//            __METHOD__,
//            '$studies' => $studies,
//            'Manager::getQueryLog()' => Manager::getQueryLog(),
//        ]);

        return [
            'total'   => $total,
            'studies' => $studies->all(),
        ];
    }

    public static function get_user_study_by_id($study_id)
    {
        return Study::with('tags', 'deck')->find($study_id);
    }

    public static function get_user_card_forecast($user_id, $span)
    {
//        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable                    = [
            'heading'       => [],
            'cumulative'    => [],
            'y'             => [],
            'm'             => [],
            'y_debug'       => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'm_debug'       => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'total_reviews' => 0,
            'average'       => 0,
            'due_tomorrow'  => 0,
        ];
        $matured_day_no               = Settings::MATURE_CARD_DAYS;
        $end_date                     = null;
        $user_timezone_today_midnight = get_user_timezone_date_midnight_today($user_id);
        $start_date                   = $user_timezone_today_midnight;
        $_date                        = new DateTime($start_date);
        if ('one_month' === $span) {
            $_date->add(new DateInterval('P30D'));
        } elseif ('three_month' === $span) {
            $_date->add(new DateInterval('P3M'));
        } elseif ('one_year' === $span) {
            $_date->add(new DateInterval('P1Y'));
        } elseif ('all' === $span) {
            $newest_answer_query = Answered
                ::orderByDesc('next_due_at')
                ->limit(1);
            if (empty($end_date)) {
                $_date->add(new DateInterval('P3D'));
                $end_date = $_date->format('Y-m-d H:i:s');
            } else {
                $end_date = $newest_answer_query->get()->first()->next_due_at;
            }
//            Common::send_error([
//                __METHOD__,
//                '$newest_answer_query sql' => $newest_answer_query->toSql(),
//                '$_date ' => $_date,
//                '$newest_answer_query sql getBindings' => $newest_answer_query->getBindings(),
//                '$newest_answer_query get' => $newest_answer_query->get(),
//            ]);
        }
        if ('all' !== $span) {
            $end_date = $_date->format('Y-m-d H:i:s');
        }
        $_start_date = new DateTime($start_date);
        $_end_date   = new DateTime($end_date);

        $no_of_days = (int) $_end_date->diff($_start_date)->format("%a"); //3
        $days       = [];
        for ($_a = 0; $_a < $no_of_days; $_a++) {
            $graphable['heading'][] = $_a.'d';
            $days[]                 = [
                'y' => [
                    'count'     => 0,
                    'answers'   => [],
                    'new_cards' => [],
                ],
                'm' => [
                    'count'   => 0,
                    'answers' => [],
                ],
            ];
        }


        $forecast_all_answers_distinct = ChartForecastHelper::get_forecast_all_answers_distinct([
            'user_id'       => $user_id,
            "start_date"    => $start_date,
            'end_date'      => $end_date,
            'no_date_limit' => ($end_date === null),
            //            'card_ids_not_in' => $matured_cards['card_ids'],
        ])['answers'];
        $forecast_new_cards_to_study   = ChartForecastHelper::get_forecast_cards_new([
            'user_id' => $user_id,
        ])['all'];

        // Form young and matured cards from answered cards
        foreach ($forecast_all_answers_distinct as $answer) {
            $study             = $answer->study;
            $no_on_hold        = $study->no_on_hold;
            $no_to_revise      = $study->no_to_revise;
            $revise_all        = $study->revise_all;
            $study_all_on_hold = $study->study_all_on_hold;
            $day_dif           = $answer->day_diff;
            $day_diff_today    = $answer->day_diff_today;
            if ($day_dif >= $matured_day_no) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $days[$day_diff_today]['m']['count']++;
                $days[$day_diff_today]['m']['answers'][] = $answer;
            } else {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $days[$day_diff_today]['y']['count']++;
                $days[$day_diff_today]['y']['answers'][] = $answer;
            }

//            Common::send_error([
//                '$no_to_revise'                  => $no_to_revise,
//                '$answer'                        => $answer,
//                '$no_on_hold'                    => $no_on_hold,
//                '$revise_all'                    => $revise_all,
//                '$study_all_on_hold'             => $study_all_on_hold,
//                '$day_dif'                       => $day_dif,
//                '$start_date'                    => $start_date,
//                '$end_date'                      => $end_date,
//                '$span'                          => $span,
//                '$no_of_days'                    => $no_of_days,
//                '$days'                          => $days,
//                '$forecast_new_cards_to_study'   => $forecast_new_cards_to_study,
//                '$forecast_all_answers_distinct' => $forecast_all_answers_distinct,
//                'Manager::getQueryLog()'         => Manager::getQueryLog(),
//            ]);
        }

        // Form young cards from new cards and spread them by no_of_new per study
        $hold_studies = [];
        $card_groups  = collect($forecast_new_cards_to_study)
            ->pluck('card_groups');
        foreach ($card_groups as $_card_group) {
            $all_new_cards = $_card_group->pluck('cards')->flatten();
//            Common::send_error([
//                '$hold_studies'  => $hold_studies,
//                '$all_new_cards' => $all_new_cards,
//            ]);
            $_new_day_index = 0;
            foreach ($all_new_cards as $key => $new_card) {
                $study         = $new_card->study;
                $study_id      = $study->id;
                $no_of_new     = $study->no_of_new;
                $study_all_new = $study->study_all_new;
                if (!array_key_exists($study_id, $hold_studies)) {
                    $hold_studies[$study_id] = [];
                }
                if (!array_key_exists($_new_day_index, $hold_studies[$study_id])) {
                    $hold_studies[$study_id][$_new_day_index] = [
                        'count' => 0,
                    ];
                }

                if ($study_all_new) {
                    if (!array_key_exists(0, $days)) {
                        $graphable['heading'][] = '0d';
                        $days[0]                = [
                            'y' => [
                                'count'     => 0,
                                'answers'   => [],
                                'new_cards' => [],
                            ],
                            'm' => [
                                'count'   => 0,
                                'answers' => [],
                            ],
                        ];
                    }
                    $days[0]['y']['count']++;
                    $days[0]['y']['new_cards'][] = $new_card;
                } else {
//                if (1 === $key) {
//                    Common::send_error([
//                        __METHOD__,
//                        '$key'            => $key,
//                        '$hold_studies'   => $hold_studies,
//                        '$_new_day_index' => $_new_day_index,
//                        '$study_id'       => $study_id,
//                        '$no_of_new'      => $no_of_new,
//                        'count count'     => $hold_studies[$study_id][$_new_day_index]['count'],
//                    ]);
//                }
                    if ($hold_studies[$study_id][$_new_day_index]['count'] >= $no_of_new) {
                        $_new_day_index++;
                    }
                    if (!array_key_exists($_new_day_index, $hold_studies[$study_id])) {
                        $hold_studies[$study_id][$_new_day_index] = [
                            'count' => 0,
                        ];
                    }
                    $hold_studies[$study_id][$_new_day_index]['count']++;

                    if (!array_key_exists($_new_day_index, $days)) {
                        $graphable['heading'][] = $_new_day_index.'d';
                        $days[$_new_day_index]  = [
                            'y' => [
                                'count'     => 0,
                                'answers'   => [],
                                'new_cards' => [],
                            ],
                            'm' => [
                                'count'   => 0,
                                'answers' => [],
                            ],
                        ];
                    }
                    $days[$_new_day_index]['y']['count']++;
                    $days[$_new_day_index]['y']['new_cards'][] = $new_card;
                }
//            if (3 === $key) {
//                Common::send_error([
//                    __METHOD__,
//                    '$key'            => $key,
//                    '$hold_studies'   => $hold_studies,
//                    '$_new_day_index' => $_new_day_index,
//                    '$study_id'       => $study_id,
//                    '$no_of_new'      => $no_of_new,
//                ]);
//            }
            }
        }


        $cumulative_count = 0;
        foreach ($days as $key => $day) {
//            if (0 === $key) {
//                $graphable['y'][]                    = 0;
//                $graphable['m'][]                    = 0;
//                $cumulative_count                    += 0;
//                $graphable['total_reviews']          += 0;
//                $graphable['cumulative'][]           = $cumulative_count;
//                $graphable['y_debug']['answers'][]   = $day['m']['answers'];
//                $graphable['y_debug']['new_cards'][] = $day['y']['new_cards'];
//                $graphable['m_debug']['answers'][]   = $day['m']['answers'];
//                continue;
//            }
            $graphable['y'][]                    = $day['y']['count'];
            $graphable['m'][]                    = $day['m']['count'];
            $cumulative_count                    += ($day['m']['count'] + $day['y']['count']);
            $graphable['total_reviews']          += ($day['m']['count'] + $day['y']['count']);
            $graphable['cumulative'][]           = $cumulative_count;
            $graphable['y_debug']['answers'][]   = $day['m']['answers'];
            $graphable['y_debug']['new_cards'][] = $day['y']['new_cards'];
            $graphable['m_debug']['answers'][]   = $day['m']['answers'];
            if (1 === $key) {
                $graphable['due_tomorrow'] = ($day['m']['count'] + $day['y']['count']);
            }
        }
        $graphable['average'] = $graphable['total_reviews'] / $no_of_days;
        $graphable['average'] = number_format($graphable['average'], 2);

//        Common::send_error([
//            '$hold_studies'                  => $hold_studies,
//            '$study_all_new'                 => $study_all_new,
//            '$card_groups'                   => $card_groups,
//            '$_new_day_index'                => $_new_day_index,
//            '$all_new_cards'                 => $all_new_cards,
//            '$start_date'                    => $start_date,
//            '$end_date'                      => $end_date,
//            '$span'                          => $span,
//            '$graphable'                     => $graphable,
//            '$no_of_days'                    => $no_of_days,
//            '$days'                          => $days,
//            '$forecast_new_cards_to_study'   => $forecast_new_cards_to_study,
//            '$forecast_all_answers_distinct' => $forecast_all_answers_distinct,
//            'Manager::getQueryLog()'         => Manager::getQueryLog(),
//        ]);

        return [
            'graphable' => $graphable
        ];


    }

    public static function get_user_card_forecast2($user_id, $span)
    {
//        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable                    = [
            'heading'       => [],
            'cumulative'    => [],
            'y'             => [],
            'm'             => [],
            'y_debug'       => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'm_debug'       => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'total_reviews' => 0,
            'average'       => 0,
            'due_tomorrow'  => 0,
        ];
        $matured_day_no               = Settings::MATURE_CARD_DAYS;
        $end_date                     = null;
        $user_timezone_today_midnight = get_user_timezone_date_midnight_today($user_id);
        $start_date                   = $user_timezone_today_midnight;
        $_date                        = new DateTime($start_date);
        if ('one_month' === $span) {
            $_date->add(new DateInterval('P30D'));
        } elseif ('three_month' === $span) {
            $_date->add(new DateInterval('P3M'));
        } elseif ('one_year' === $span) {
            $_date->add(new DateInterval('P1Y'));
        } elseif ('all' === $span) {
            $newest_answer_query = Answered
                ::orderByDesc('next_due_at')
                ->limit(1);
            $end_date            = $newest_answer_query->get()->first()->next_due_at;
//            Common::send_error([
//                __METHOD__,
//                '$newest_answer_query sql' => $newest_answer_query->toSql(),
//                '$_date ' => $_date,
//                '$newest_answer_query sql getBindings' => $newest_answer_query->getBindings(),
//                '$newest_answer_query get' => $newest_answer_query->get(),
//            ]);
        }
        if ('all' !== $span) {
            $end_date = $_date->format('Y-m-d H:i:s');
        }
        $_start_date = new DateTime($start_date);
        $_end_date   = new DateTime($end_date);

        $no_of_days = (int) $_end_date->diff($_start_date)->format("%a"); //3
        $days       = [];
        for ($_a = 0; $_a < $no_of_days; $_a++) {
            $graphable['heading'][] = $_a.'d';
            $days[]                 = [
                'y' => [
                    'count'     => 0,
                    'answers'   => [],
                    'new_cards' => [],
                    //                    'studies' => [
                    //                        '_eg' => [
                    //                            'hold_count'   => 0,
                    //                            'revise_count' => 0,
                    //                        ],
                    //                    ],
                ],
                'm' => [
                    'count'   => 0,
                    'answers' => [],
                    //                    'studies' => [
                    //                        '_eg' => [
                    //                            'hold_count'   => 0,
                    //                            'revise_count' => 0,
                    //                        ],
                    //                    ],
                ],
            ];
        }

        $forecast_all_answers_distinct = ChartForecastHelper::get_forecast_all_answers_distinct([
            'user_id'       => $user_id,
            "start_date"    => $start_date,
            'end_date'      => $end_date,
            'no_date_limit' => ($end_date === null),
            //            'card_ids_not_in' => $matured_cards['card_ids'],
        ])['answers'];
        $forecast_new_cards_to_study   = ChartForecastHelper::get_forecast_cards_new([
            'user_id' => $user_id,
        ])['all'];

        foreach ($forecast_all_answers_distinct as $answer) {
            $study             = $answer->study;
            $no_on_hold        = $study->no_on_hold;
            $no_to_revise      = $study->no_to_revise;
            $revise_all        = $study->revise_all;
            $study_all_on_hold = $study->study_all_on_hold;
            $day_dif           = $answer->day_diff;
            $day_diff_today    = $answer->day_diff_today;
            if ($day_dif >= $matured_day_no) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $days[$day_diff_today]['m']['count']++;
                $days[$day_diff_today]['m']['answers'][] = $answer;
            } else {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $days[$day_diff_today]['y']['count']++;
                $days[$day_diff_today]['y']['answers'][] = $answer;
            }

//            Common::send_error([
//                '$no_to_revise'                  => $no_to_revise,
//                '$answer'                        => $answer,
//                '$no_on_hold'                    => $no_on_hold,
//                '$revise_all'                    => $revise_all,
//                '$study_all_on_hold'             => $study_all_on_hold,
//                '$day_dif'                       => $day_dif,
//                '$start_date'                    => $start_date,
//                '$end_date'                      => $end_date,
//                '$span'                          => $span,
//                '$no_of_days'                    => $no_of_days,
//                '$days'                          => $days,
//                '$forecast_new_cards_to_study'   => $forecast_new_cards_to_study,
//                '$forecast_all_answers_distinct' => $forecast_all_answers_distinct,
//                'Manager::getQueryLog()'         => Manager::getQueryLog(),
//            ]);
        }

        $hold_studies   = [];
        $_new_day_index = 0;

        $card_groups = collect($forecast_new_cards_to_study)
            ->pluck('card_groups');

        foreach ($card_groups as $_card_group) {

        }

        $all_new_cards = collect($forecast_new_cards_to_study)
            ->pluck('card_groups')->flatten()
            ->pluck('cards')->flatten();
        foreach ($all_new_cards as $key => $new_card) {
            $study         = $new_card->study;
            $study_id      = $study->id;
            $no_of_new     = $study->no_of_new;
            $study_all_new = $study->study_all_new;
            if (!array_key_exists($study_id, $hold_studies)) {
                $hold_studies[$study_id] = [];
            }

            if ($study_all_new) {
                $days[0]['y']['count']++;
                $days[0]['y']['new_cards'][] = $new_card;
            } else {
//                if (1 === $key) {
//                    Common::send_error([
//                        __METHOD__,
//                        '$key'            => $key,
//                        '$hold_studies'   => $hold_studies,
//                        '$_new_day_index' => $_new_day_index,
//                        '$study_id'       => $study_id,
//                        '$no_of_new'      => $no_of_new,
//                        'count count'     => $hold_studies[$study_id][$_new_day_index]['count'],
//                    ]);
//                }
                if ($hold_studies[$study_id][$_new_day_index]['count'] >= $no_of_new) {
                    $_new_day_index++;
                }
                if (!array_key_exists($_new_day_index, $hold_studies[$study_id])) {
                    $hold_studies[$study_id][$_new_day_index] = [
                        'count' => 0,
                    ];
                }
                $hold_studies[$study_id][$_new_day_index]['count']++;
                $days[$_new_day_index]['y']['count']++;
                $days[$_new_day_index]['y']['new_cards'][] = $new_card;
            }
//            if (3 === $key) {
//                Common::send_error([
//                    __METHOD__,
//                    '$key'            => $key,
//                    '$hold_studies'   => $hold_studies,
//                    '$_new_day_index' => $_new_day_index,
//                    '$study_id'       => $study_id,
//                    '$no_of_new'      => $no_of_new,
//                ]);
//            }
        }

        $cumulative_count = 0;
        foreach ($days as $key => $day) {
            if (0 === $key) {
                $graphable['y'][]                    = 0;
                $graphable['m'][]                    = 0;
                $cumulative_count                    += 0;
                $graphable['total_reviews']          += 0;
                $graphable['cumulative'][]           = $cumulative_count;
                $graphable['y_debug']['answers'][]   = $day['m']['answers'];
                $graphable['y_debug']['new_cards'][] = $day['y']['new_cards'];
                $graphable['m_debug']['answers'][]   = $day['m']['answers'];
                continue;
            }
            $graphable['y'][]                    = $day['y']['count'];
            $graphable['m'][]                    = $day['m']['count'];
            $cumulative_count                    += ($day['m']['count'] + $day['y']['count']);
            $graphable['total_reviews']          += ($day['m']['count'] + $day['y']['count']);
            $graphable['cumulative'][]           = $cumulative_count;
            $graphable['y_debug']['answers'][]   = $day['m']['answers'];
            $graphable['y_debug']['new_cards'][] = $day['y']['new_cards'];
            $graphable['m_debug']['answers'][]   = $day['m']['answers'];
            if (1 === $key) {
                $graphable['due_tomorrow'] = ($day['m']['count'] + $day['y']['count']);
            }
        }
        $graphable['average'] = $graphable['total_reviews'] / $no_of_days;
        $graphable['average'] = number_format($graphable['average'], 2);

        Common::send_error([
            '$hold_studies'                  => $hold_studies,
            '$study_all_new'                 => $study_all_new,
            '$card_groups'                   => $card_groups,
            '$_new_day_index'                => $_new_day_index,
            '$all_new_cards'                 => $all_new_cards,
            '$start_date'                    => $start_date,
            '$end_date'                      => $end_date,
            '$span'                          => $span,
            '$graphable'                     => $graphable,
            '$no_of_days'                    => $no_of_days,
            '$days'                          => $days,
            '$forecast_new_cards_to_study'   => $forecast_new_cards_to_study,
            '$forecast_all_answers_distinct' => $forecast_all_answers_distinct,
            'Manager::getQueryLog()'         => Manager::getQueryLog(),
        ]);

//        Common::send_error([
//            '$start_date'                    => $start_date,
//            '$end_date'                      => $end_date,
//            '$span'                          => $span,
//            '$no_of_days'                    => $no_of_days,
//            '$start_date'                    => $start_date,
//            '$days'                          => $days,
//            '$forecast_new_cards_to_study'   => $forecast_new_cards_to_study,
//            '$forecast_all_answers_distinct' => $forecast_all_answers_distinct,
//            'Manager::getQueryLog()'         => Manager::getQueryLog(),
//        ]);
        Common::send_success('Forecast here', [
            'graphable' => $graphable
        ]);

    }

    public static function get_user_card_review_count_and_time($user_id, $span)
    {
//        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable                    = [
            'heading'                  => [],
            'cumulative'               => [],
            'y'                        => [],
            'm'                        => [],
            'newly_learned'            => [],
            'relearned'                => [],
            'y_debug'                  => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'm_debug'                  => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'newly_learned_debug'      => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'relearned_debug'          => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'm_cumulative'             => 0,
            'y_cumulative'             => 0,
            'newly_learned_cumulative' => 0,
            're_learned_cumulative'    => 0,
            'total_reviews'            => 0,
            'average'                  => 0,
            'due_tomorrow'             => 0,
        ];
        $matured_day_no               = Settings::MATURE_CARD_DAYS;
        $end_date                     = null;
        $user_timezone_today_midnight = get_user_timezone_date_midnight_today($user_id);
//        $start_date                   = $user_timezone_today_midnight;
        $end_date = $user_timezone_today_midnight;
        $_date    = new DateTime($user_timezone_today_midnight);
        if ('one_month' === $span) {
            $_date->sub(new DateInterval('P30D'));
        } elseif ('three_month' === $span) {
            $_date->sub(new DateInterval('P3M'));
        } elseif ('one_year' === $span) {
            $_date->sub(new DateInterval('P1Y'));
        } elseif ('all' === $span) {
            $newest_answer_query = Answered
                ::orderBy('next_due_at')
                ->limit(1);
            $start_date          = $newest_answer_query->get()->first()->next_due_at;
//            Common::send_error([
//                __METHOD__,
//                '$newest_answer_query sql' => $newest_answer_query->toSql(),
//                '$_date ' => $_date,
//                '$newest_answer_query sql getBindings' => $newest_answer_query->getBindings(),
//                '$newest_answer_query get' => $newest_answer_query->get(),
//            ]);
        }
        if ('all' !== $span) {
            $start_date = $_date->format('Y-m-d H:i:s');
        }
        $_start_date = new DateTime($start_date);
        $_end_date   = new DateTime($end_date);

        $no_of_days = (int) $_end_date->diff($_start_date)->format("%a"); //3
        $days       = [];
        $__a_count  = 0 - $no_of_days + 1;
        for ($_a = 0; $_a < $no_of_days; $_a++) {
            $graphable['heading'][] = $__a_count.'d';
            $__a_count++;
            $days[] = [
                'y'             => [
                    'count'        => 0,
                    'seconds_took' => 0,
                    'minutes_took' => 0,
                    'hours_took'   => 0,
                    'cumulative'   => 0,
                    'answers'      => [],
                ],
                'm'             => [
                    'count'        => 0,
                    'seconds_took' => 0,
                    'minutes_took' => 0,
                    'hours_took'   => 0,
                    'cumulative'   => 0,
                    'answers'      => [],
                ],
                'newly_learned' => [
                    'count'        => 0,
                    'seconds_took' => 0,
                    'minutes_took' => 0,
                    'hours_took'   => 0,
                    'cumulative'   => 0,
                    'answers'      => [],
                ],
                're_learned'    => [
                    'count'        => 0,
                    'seconds_took' => 0,
                    'minutes_took' => 0,
                    'hours_took'   => 0,
                    'cumulative'   => 0,
                    'answers'      => [],
                ],
            ];
        }

        $forecast_all_answers_within_a_date = ChartReviewHelper::get_forecast_all_answers_within_a_date([
            'user_id'       => $user_id,
            "start_date"    => $start_date,
            'end_date'      => $end_date,
            'no_date_limit' => ($end_date === null),
            //            'card_ids_not_in' => $matured_cards['card_ids'],
        ])['answers'];

        $_cumulative_m             = 0;
        $_cumulative_y             = 0;
        $_cumulative_newly_learned = 0;
        $_cumulative_re_learned    = 0;
        foreach ($forecast_all_answers_within_a_date as $answer) {
            $study             = $answer->study;
            $no_on_hold        = $study->no_on_hold;
            $no_to_revise      = $study->no_to_revise;
            $revise_all        = $study->revise_all;
            $study_all_on_hold = $study->study_all_on_hold;

            $day_dif        = $answer->day_diff;
            $day_diff_today = $answer->day_diff_today;

            $is_matured       = $day_dif >= $matured_day_no;
            $is_young         = $day_dif < $matured_day_no;
            $is_newly_learned = !empty($answer->answered_as_new);
            $is_relearned     = !empty($answer->answered_as_revised);

            if ($is_matured) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_m += $answer->time_diff_hours_spent;
                $days[$day_diff_today]['m']['count']++;
                $days[$day_diff_today]['m']['seconds_took'] += $answer->time_diff_second_spent;
                $days[$day_diff_today]['m']['minutes_took'] += $answer->time_diff_minute_spent;
                $days[$day_diff_today]['m']['hours_took']   += $answer->time_diff_hours_spent;
                $days[$day_diff_today]['m']['cumulative']   += $_cumulative_m;
                $days[$day_diff_today]['m']['answers'][]    = $answer;
            }
            if ($is_young) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_y += $answer->time_diff_hours_spent;
                $days[$day_diff_today]['y']['count']++;
                $days[$day_diff_today]['y']['seconds_took'] += $answer->time_diff_second_spent;
                $days[$day_diff_today]['y']['minutes_took'] += $answer->time_diff_minute_spent;
                $days[$day_diff_today]['y']['hours_took']   += $answer->time_diff_hours_spent;
                $days[$day_diff_today]['y']['cumulative']   += $_cumulative_y;
                $days[$day_diff_today]['y']['answers'][]    = $answer;
            }
            if ($is_newly_learned) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_newly_learned += $answer->time_diff_hours_spent;
                $days[$day_diff_today]['newly_learned']['count']++;
                $days[$day_diff_today]['newly_learned']['seconds_took'] += $answer->time_diff_second_spent;
                $days[$day_diff_today]['newly_learned']['minutes_took'] += $answer->time_diff_minute_spent;
                $days[$day_diff_today]['newly_learned']['hours_took']   += $answer->time_diff_hours_spent;
                $days[$day_diff_today]['newly_learned']['cumulative']   += $_cumulative_newly_learned;
                $days[$day_diff_today]['newly_learned']['answers'][]    = $answer;
            }
            if ($is_relearned) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_re_learned += $answer->time_diff_hours_spent;
                $days[$day_diff_today]['re_learned']['count']++;
                $days[$day_diff_today]['re_learned']['seconds_took'] += $answer->time_diff_second_spent;
                $days[$day_diff_today]['re_learned']['minutes_took'] += $answer->time_diff_minute_spent;
                $days[$day_diff_today]['re_learned']['hours_took']   += $answer->time_diff_hours_spent;
                $days[$day_diff_today]['re_learned']['cumulative']   += $_cumulative_re_learned;
                $days[$day_diff_today]['re_learned']['answers'][]    = $answer;
            }

//            Common::send_error([
//                '$no_to_revise'                                        => $no_to_revise,
//                '$answer'                                              => $answer,
//                '$no_on_hold'                                          => $no_on_hold,
//                '$revise_all'                                          => $revise_all,
//                '$study_all_on_hold'                                   => $study_all_on_hold,
//                '$day_dif'                                             => $day_dif,
//                '$start_date'                                          => $start_date,
//                '$end_date'                                            => $end_date,
//                '$span'                                                => $span,
//                '$no_of_days'                                          => $no_of_days,
//                '$days'                                                => $days,
//                '$forecast_all_answers_distinct_for_matured_and_young' => $forecast_all_answers_distinct_for_matured_and_young,
//                //                '$forecast_new_cards_to_study'   => $forecast_new_cards_to_study,
//                //                '$forecast_all_answers_distinct' => $forecast_all_answers_distinct,
//                'Manager::getQueryLog()'                               => Manager::getQueryLog(),
//            ]);
        }

        $cumulative_count = 0;
//        rsort($days);
        foreach ($days as $key => $day) {
            $graphable['y'][]                    = $day['y']['count'];
            $graphable['m'][]                    = $day['m']['count'];
            $graphable['newly_learned'][]        = $day['newly_learned']['count'];
            $graphable['relearned'][]            = $day['re_learned']['count'];
            $cumulative_count                    += ($day['m']['count'] + $day['y']['count']);
            $graphable['total_reviews']          += ($day['m']['count'] + $day['y']['count']);
            $graphable['cumulative'][]           = $cumulative_count;
            $graphable['y_debug']['answers'][]   = $day['m']['answers'];
            $graphable['y_debug']['new_cards'][] = $day['y']['new_cards'];
            $graphable['m_debug']['answers'][]   = $day['m']['answers'];
            if (1 === $key) {
                $graphable['due_tomorrow'] = ($day['m']['count'] + $day['y']['count']);
            }
        }
        $graphable['average'] = $graphable['total_reviews'] / $no_of_days;
        $graphable['average'] = number_format($graphable['average'], 2);

//        Common::send_error([
//            '$start_date'                         => $start_date,
//            '$end_date'                           => $end_date,
//            '$span'                               => $span,
//            '$graphable'                          => $graphable,
//            '$no_of_days'                         => $no_of_days,
//            '$days'                               => $days,
//            '$__a_count'                          => $__a_count,
//            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
//            'Manager::getQueryLog()'              => Manager::getQueryLog(),
//        ]);

        return [
            'graphable' => $graphable
        ];

    }

    /**
     * Get cards on hold for forecast
     * @param $args
     * @return array[]
     */

    public static function get_forecast_cards_on_hold($args)
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
//			Common::send_error(['user_id' => $args]);

        $user         = User::with('studies')
            ->where('ID', '=', $args['user_id'])
            ->get()->first();//->studies();//->get();
        $user_studies = $user->studies;

        $all_card_ids = [];
        $debug_info   = [];
        foreach ($user_studies as $key => $study) {
            $study_id          = $study->id;
            $study_all_on_hold = $study->study_all_on_hold;
            $no_on_hold        = $study->no_on_hold;
            $query_answer      = Answered
                ::with('study.deck', 'card')
                ->where('study_id', '=', $study_id)
                ->where('grade', '=', 'hold')
                ->groupBy('card_id')
                ->orderByDesc('id');

            if ($args['no_date_limit']) {
                $query_answer = $query_answer->where('next_due_at', '>=', $args['start_date']);
            } else {
                $query_answer = $query_answer->whereBetween('next_due_at', [$args['start_date'], $args['end_date']]);
            }

            if (!empty($args['card_ids_in'])) {
                $query_answer = $query_answer->whereIn('card_id', $args['card_ids_in']);
            }
            if (!empty($args['card_ids_not_in'])) {
                $query_answer = $query_answer->whereNotIn('card_id', $args['card_ids_not_in']);
            }

            if (!$study_all_on_hold) {
                $query_answer = $query_answer->limit($no_on_hold);
            }

            $get = $query_answer->get();

            foreach ($get as $answer) {
                $all_card_ids[] = $answer->card_id;
                $debug_info[]   = $answer;
            }
//				Common::send_error( [
//					__METHOD__,
//					'query sql' => $query_answer->toSql(),
//					'query get' => $query_answer->get(),
//					'$study'    => $study,
//				] );

        }

        return [
            'card_ids'   => $all_card_ids,
            'debug_info' => $debug_info,
        ];
    }

    public static function get_forecast_cards_to_revise($args)
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

        $user         = User::with('studies')
            ->where('ID', '=', $args['user_id'])
            ->get()->first();//->studies();//->get();
        $user_studies = $user->studies;
//			Common::send_error( [
//				'$args'         => $args,
//				'$user_studies' => $user_studies,
////				'toSql'        => $user_studies->toSql(),
////				'getBinddings' => $user_studies->getBindings(),
////				'get'          => $user_studies->get(),
//			] );
        $all_card_ids = [];
        $debug_info   = [];
        foreach ($user_studies as $key => $study) {
            $study_id     = $study->id;
            $revise_all   = $study->revise_all;
            $no_to_revise = $study->no_to_revise;
            $query_answer = Answered
                ::with('study.deck', 'card')
                ->where('study_id', '=', $study_id)
                ->where('grade', '!=', 'hold')
                ->groupBy('card_id')
                ->orderByDesc('id');
            if ($args['no_date_limit']) {
                $query_answer = $query_answer->where('next_due_at', '>', $args['start_date']);
            } else {
                $query_answer = $query_answer->whereBetween('next_due_at', [$args['start_date'], $args['end_date']]);
            }

            if (!empty($args['card_ids_in'])) {
                $query_answer = $query_answer->whereIn('card_id', $args['card_ids_in']);
            }
            if (!empty($args['card_ids_not_in'])) {
                $query_answer = $query_answer->whereNotIn('card_id', $args['card_ids_not_in']);
            }

            if (!$revise_all) {
                $query_answer = $query_answer->limit($no_to_revise);
            }

            $get = $query_answer->get();

            foreach ($get as $answer) {
                $all_card_ids[] = $answer->card_id;
                $debug_info[]   = $answer;
            }
//				Common::send_error( [
//					__METHOD__,
//					'query sql' => $query_answer->toSql(),
//					'query get' => $query_answer->get(),
//					'$study'    => $study,
//				] );

        }

        return [
            'card_ids'   => $all_card_ids,
            'debug_info' => $debug_info,
        ];
    }


    /**
     * Returns cards whose next due date of the last answer is >= Settings::MATURE_CARD_DAYS
     *
     * @param $user_id
     */
    public static function get_user_matured_card_ids($user_id)
    {
        $mature_card_days = Settings::MATURE_CARD_DAYS;
        $all              = [];
        $all_card_ids     = [];


        $user_query = User::with([
            'studies.answers' => function ($q) use ($mature_card_days) {
                $q
                    ->select(
                        'id',
                        'next_due_at',
                        'created_at',
                        'card_id',
                        'study_id',
                        Manager::raw('DATEDIFF(DATE(next_due_at),DATE(created_at)) next_due_interval'),
                        Manager::raw('DATE(created_at)')
                    )
                    ->groupBy('card_id')
                    ->having('next_due_interval', '>=', $mature_card_days)
                    ->orderBy('id', 'desc');
//					Common::send_error( [
//						__METHOD__,
//						'$q sql'               => $q->toSql(),
//						'$q $get'              => $q->get(),
//						'Manager::getQueryLog()' => Manager::getQueryLog(),
////				'$aaa'                   => $aaa_get,
//					] );
            },
        ])
            ->where('ID', '=', $user_id);

        $user = $user_query->get()->first();

        /*** Prepare basic query ***/
        $user_studies = $user->studies;
//        dd($user_studies);
//			Common::send_error( [
//				__METHOD__,
//				'$user_query sql'               => $user_query->toSql(),
//				'Manager::getQueryLog()' => Manager::getQueryLog(),
//				'$user_studies'                   => $user_studies,
//			] );

        foreach ($user_studies as $study) {
            $study_id = $study->id;
//            Common::send_error([
//                __METHOD__,
//                '$user_query sql' => $user_query->toSql(),
//                '$study_id sql' => $study_id,
//                'Manager::getQueryLog()' => Manager::getQueryLog(),
//                '$user_studies' => $user_studies,
//            ]);
            $answers = $study->answers;
            foreach ($answers as $answer) {
                $all_card_ids[] = $answer->card->id;
                $all[]          = [
                    'card_id' => $answer->card->id,
                    'answer'  => $answer,
                    'study'   => $study,
                    'deck'    => $study->deck,
                ];
            }
//				Common::send_error( [
//					'$matured_answers'       => $matured_answers->toSql(),
//					'$matured_answers get'   => $matured_answers->get(),
////					'$query_mature'          => $query_mature->toSql(),
//					'Manager::getQueryLog()' => Manager::getQueryLog(),
////					'$get'                   => $query_mature->get(),
////					'getBindings'            => $query_mature->getBindings(),
//					'$study_id'              => $study_id,
//				] );

        }

        return [
            'card_ids' => $all_card_ids,
            'all'      => $all,
        ];
    }

    public static function get_user_cards($study_id, $user_id)
    {

        try {
            $user_timezone_minutes_from_now = get_user_timezone_minutes_to_add($user_id);
            $_date_today                    = Common::getDateTime();
            $_datetime                      = new DateTime($_date_today);
            $_datetime->modify("$user_timezone_minutes_from_now minutes");
            $datetime_from_due = $_datetime->format('Y-m-d H:i:s');

//				Common::send_error( [
//					'$_date_today'                    => $_date_today,
//					'$datetime_from_due'              => $datetime_from_due,
//					'$user_timezone_minutes_from_now' => $user_timezone_minutes_from_now,
//				] );

            $study             = Study::with('tags')->find($study_id);
            $deck_id           = $study->deck_id;
            $tags              = [];
            $add_all_tags      = $study->all_tags;
            $study_all_new     = $study->study_all_new;
            $revise_all        = $study->revise_all;
            $study_all_on_hold = $study->study_all_on_hold;
            $no_of_new         = $study->no_of_new;
            $no_on_hold        = $study->no_on_hold;

            if (!$add_all_tags) {
                $tags = $study->tags->pluck('id');
            }


            $cards_query = Manager::table(SP_TABLE_CARDS.' as c')
                ->leftJoin(SP_TABLE_CARD_GROUPS.' as cg', 'cg.id', '=', 'c.card_group_id')
                ->leftJoin(SP_TABLE_DECKS.' as d', 'd.id', '=', 'cg.deck_id')
                ->leftJoin(SP_TABLE_TAGGABLES.' as tg', 'tg.taggable_id', '=', 'cg.id')
                ->leftJoin(SP_TABLE_TAGS.' as t', 't.id', '=', 'tg.tag_id')
                ->where('tg.taggable_type', '=', CardGroup::class)
                ->select(
                    'c.id as card_id',
                    'd.id as deck_id',
                    'cg.card_type as card_type',
                    'cg.id as card_group_id',
                    't.name as tag_name',
                    'tg.taggable_type as taggable_type'
                );

            if (!$add_all_tags) {
                $cards_query = $cards_query->whereIn('t.id', $tags);
            }

            $cards_query = $cards_query->where('d.id', '=', $deck_id)
                ->groupBy('c.id');
//				->where( 'tb.taggable_type', '=', CardGroup::class )
//				dd(
//					$cards_query->toSql(),
//					$cards_query->getBindings(),
//					$date_today, $user_timezone,
//					$timezones[ $user_timezone ],
//					$timezones
//				);
//				dd( $cards_query->toSql() );
            // In this deck
            // In those tags
            //


//				$study = Study::with( [
//					'deck.cards',
//					'deck.cards.card_group',
//					'answers' => function ( $query ) use ( $date_today ) {
////						$query->where( 'next_due_at', '<', $date_today );
//						$query->where( 'id', '>', 14 );
////						dd( $query->toSql() );
//					},
//				] )
//					->where( 'id', '=', $study_id )
//					->where( 'user_id', '=', $user_id );


//				$study = $study->get()->firstOrFail();
//				$cards = $study->deck->cards;

            Common::send_error([
                __METHOD__,
                '$study'                 => $study,
                '$tags'                  => $tags,
                '$add_all_tags'          => $add_all_tags,
                'card_get'               => $cards_query->get(),
                'card_query_sql'         => $cards_query->toSql(),
                //					'$cards'                 => $cards,
                'Manager::getQueryLog()' => Manager::getQueryLog(),
                'study_id'               => $study_id,
            ]);


            return [
                'cards' => $cards,
            ];

        } catch (ItemNotFoundException $e) {
            //todo handle later
            return [
                'cards' => [],
            ];
        }


    }

    public static function get_user_cards_on_hold($study_id, $user_id, $particular_date = null)
    {

        try {
            $user_timezone_today_midnight = get_user_timezone_date_midnight_today($user_id);

            $study        = Study::with('tags')->findOrFail($study_id);
            $deck_id      = $study->deck_id;
            $tags         = [];
            $add_all_tags = $study->all_tags;
            $revise_all   = $study->revise_all;
            $no_to_revise = $study->no_to_revise;

            if (!$add_all_tags) {
                $tags = $study->tags->pluck('id');
            }

            /**
             * Get all cards
             * In "card groups" in the "deck" in the "study"
             * Next due date is <= today midnight + timezone
             * Distinct by card_id
             * Only cards that have been answered before (not in cards revised today , except "agiain")
             * Grade is hold
             */

            /*** Get all cards revised today answered today (To exclude them later if "false === $study->no_to_revise") ***/
            $query_revised_today                 = Answered
                ::where('study_id', '=', $study_id)
                ->where('created_at', '>', $user_timezone_today_midnight)
//					->whereNotIn( 'grade', [ 'again' ] )
                ->where('answered_as_revised', '=', true);
            $card_ids_revised_today              = $query_revised_today->pluck('card_id');
            $count_revised_today                 = $card_ids_revised_today->count();
            $no_of_new_remaining_to_revise_today = $no_to_revise - $count_revised_today;

//				Common::send_error( [
//					'sql'                                  => $query_revised_today->toSql(),
//					'getBindings'                          => $query_revised_today->getBindings(),
//					'$card_ids_revised_today'              => $card_ids_revised_today,
//					'$no_of_new_remaining_to_revise_today' => $no_of_new_remaining_to_revise_today,
//					'$user_timezone_today_midnight'        => $user_timezone_today_midnight,
//				] );

            /*** Prepare basic query ***/
            $cards_query = Manager::table(SP_TABLE_CARDS.' as c')
                ->leftJoin(SP_TABLE_CARD_GROUPS.' as cg', 'cg.id', '=', 'c.card_group_id')
                ->leftJoin(SP_TABLE_DECKS.' as d', 'd.id', '=', 'cg.deck_id')
                ->leftJoin(SP_TABLE_TAGGABLES.' as tg', 'tg.taggable_id', '=', 'cg.id')
                ->leftJoin(SP_TABLE_TAGS.' as t', 't.id', '=', 'tg.tag_id')
                ->where('tg.taggable_type', '=', CardGroup::class)
                ->select(
                    'c.id as card_id'
                );

            /*** Add just a few tags? ***/
            if (!$add_all_tags) {
                $cards_query = $cards_query->whereIn('t.id', $tags);
            }

            /*** Revise a few cards? ***/
            if (!$revise_all) {
                $cards_query = $cards_query->limit($no_of_new_remaining_to_revise_today);
            }

            /*** Return only those answered before (Not in cards revised today) and grade = hold ***/
            $cards_query = $cards_query
                ->whereIn('c.id', function ($q) use (
                    $user_timezone_today_midnight,
                    $card_ids_revised_today,
                    $study_id,
                    $user_id
                ) {
                    $q->select('card_id')->from(SP_TABLE_ANSWERED)
                        ->whereNotIn('card_id', $card_ids_revised_today)
                        ->whereNotIn('card_id', function ($q) use ($user_id) {
                            $q
                                ->select('card_id')
                                ->from(SP_TABLE_ANSWERED.' as aaa')
                                ->leftJoin(SP_TABLE_STUDY.' as sss', 'sss.id', '=', 'aaa.study_id')
                                ->leftJoin(SP_TABLE_USERS.' as uuu', 'uuu.id', '=', 'sss.user_id')
                                ->where('uuu.id', '=', $user_id)
                                ->where('grade', '!=', 'hold')
                                ->distinct() //todo improve, limit by study_id or user_id
                            ;
//                            dd( $q->toSql(), $q->getBindings(), $q->get() );
                        })
                        ->whereIn('grade', ['hold'])
                        ->where('study_id', $study_id)
                        ->where('next_due_at', '<=', $user_timezone_today_midnight)
                        ->distinct();
//						dd( $q->toSql(), $q->getBindings(),$card_ids_revised_today, $q->get() );
                });
//            dd($cards_query->toSql(), $cards_query->getBindings(), $cards_query->get());

            /*** Group by c.id "To prevent duplicate results being returned" **/
            $cards_query = $cards_query->where('d.id', '=', $deck_id)
                ->groupBy('c.id');
//				dd( $cards_query->toSql(), $cards_query->getBindings(),$cards_query->get() );

            $card_ids = $cards_query->pluck('card_id');

            /*** Get the cards ***/
            $all_cards = Card::with('card_group', 'card_group.deck')
                ->whereIn('id', $card_ids);
//            Common::send_error([
//                'all_cards' => $all_cards,
//            ]);
//            dd(
//                $card_ids,
//                $all_cards->toSql(),
//                $all_cards->getBindings(),
//                $all_cards->get(),
//                $cards_query->toSql(),
//                $cards_query->getBindings(),
//                $cards_query->get()
//            );


//            Common::send_error([
//                __METHOD__,
//                '$all_cards toSql'       => $all_cards->toSql(),
//                '$all_cards'             => $all_cards->get(),
//                '$study'                 => $study,
//                '$card_ids'              => $card_ids,
//                '$tags'                  => $tags,
//                '$add_all_tags'          => $add_all_tags,
//                'card_get'               => $cards_query->get(),
//                'card_query_sql'         => $cards_query->toSql(),
//                //					'$cards'                 => $cards,
//                'Manager::getQueryLog()' => Manager::getQueryLog(),
//                'study_id'               => $study_id,
//            ]);


            return [
                'cards' => $all_cards->get(),
            ];

        } catch (ItemNotFoundException $e) {
            //todo handle later
            return [
                'cards' => [],
            ];
        } catch (ModelNotFoundException $e) {
            //todo handle later
            return [
                'cards' => [],
            ];
        }


    }

    public static function get_user_cards_to_revise($study_id, $user_id)
    {

        try {
            $user_timezone_today_midnight = get_user_timezone_date_midnight_today($user_id);

            $study        = Study::with('tags')->findOrFail($study_id);
            $deck_id      = $study->deck_id;
            $tags         = [];
            $add_all_tags = $study->all_tags;
            $revise_all   = $study->revise_all;
            $no_to_revise = $study->no_to_revise;

            if (!$add_all_tags) {
                $tags = $study->tags->pluck('id');
            }

            /**
             * Get all cards
             * In "card groups" in the "deck" in the "study"
             * Next due date is <= today midnight + timezone
             * Distinct by card_id
             * Only cards that have been answered before (not in cards revised today , except "agiain")
             *
             */

            /*** Get all cards revised today answered today (To exclude them later if "false === $study->no_to_revise") ***/
            $query_revised_today                 = Answered::where('study_id', '=', $study_id)
                ->where('created_at', '>', $user_timezone_today_midnight)
                ->whereNotIn('grade', ['again'])
                ->where('study_id', '=', $study_id)
                ->where('answered_as_revised', '=', true);
            $card_ids_revised_today              = $query_revised_today->pluck('card_id');
            $count_revised_today                 = $card_ids_revised_today->count();
            $no_of_new_remaining_to_revise_today = $no_to_revise - $count_revised_today;

//				Common::send_error( [
//					'sql'                                  => $query_revised_today->toSql(),
//					'getBindings'                          => $query_revised_today->getBindings(),
//					'$card_ids_revised_today'              => $card_ids_revised_today,
//					'$no_of_new_remaining_to_revise_today' => $no_of_new_remaining_to_revise_today,
//				] );

            /*** Prepare basic query ***/
            $cards_query = Manager::table(SP_TABLE_CARDS.' as c')
                ->leftJoin(SP_TABLE_CARD_GROUPS.' as cg', 'cg.id', '=', 'c.card_group_id')
                ->leftJoin(SP_TABLE_DECKS.' as d', 'd.id', '=', 'cg.deck_id')
                ->leftJoin(SP_TABLE_TAGGABLES.' as tg', 'tg.taggable_id', '=', 'cg.id')
                ->leftJoin(SP_TABLE_TAGS.' as t', 't.id', '=', 'tg.tag_id')
                ->where('tg.taggable_type', '=', CardGroup::class)
                ->select(
                    'c.id as card_id'
                );

            /*** Add just a few tags? ***/
            if (!$add_all_tags) {
                $cards_query = $cards_query->whereIn('t.id', $tags);
            }

            /*** Revise a few cards? ***/
            if (!$revise_all) {
                $cards_query = $cards_query->limit($no_of_new_remaining_to_revise_today);
            }

            /*** Filter out cards revised today today "Except those with grade as 'again' and 'hold' " ***/
//				$cards_query = $cards_query
//					->whereNotIn( 'c.id', $card_ids_revised_today );

            /*** Filter out cards answered today with grade not "again" ***/
//				$cards_query = $cards_query
//					->whereNotIn( 'c.id', function ( $q ) use ( $user_timezone_today_midnight ) {
//						$q->select( 'card_id' )->from( SP_TABLE_ANSWERED )
//							->where( 'grade', '!=', 'again' );
//					} );

            /*** Return only those answered before (Not in cards revised today) ***/
            $cards_query = $cards_query
                ->whereIn('c.id', function ($q) use (
                    $user_timezone_today_midnight,
                    $card_ids_revised_today,
                    $study_id
                ) {
                    $q->select('card_id')->from(SP_TABLE_ANSWERED)
                        ->whereNotIn('card_id', $card_ids_revised_today)
                        ->whereNotIn('grade', ['hold'])
                        ->where('study_id', $study_id)
                        ->where('next_due_at', '<=', $user_timezone_today_midnight)
                        ->distinct();
//						dd( $q->toSql(), $q->getBindings(),$q->get() );
                });
//				dd( $cards_query->toSql(), $cards_query->getBindings(),$cards_query->get() );

            /*** Group by c.id "To prevent duplicate results being returned" **/
            $cards_query = $cards_query->where('d.id', '=', $deck_id)
                ->groupBy('c.id');
//				dd( $cards_query->toSql(), $cards_query->getBindings(),$cards_query->get() );

            $card_ids = $cards_query->pluck('card_id');

            /*** Get the cards ***/
            $all_cards = Card::with('card_group', 'card_group.deck')
                ->whereIn('id', $card_ids);
//				dd(
//					$card_ids,
//					$all_cards->toSql(),
//					$all_cards->getBindings(),
//					$all_cards->get(),
//					$cards_query->toSql(),
//					$cards_query->getBindings(),
//					$cards_query->get()
//				);


//				Common::send_error( [
//					__METHOD__,
//					'$all_cards toSql'       => $all_cards->toSql(),
//					'$all_cards'             => $all_cards->get(),
//					'$study'                 => $study,
//					'$card_ids'                 => $card_ids,
//					'$tags'                  => $tags,
//					'$add_all_tags'          => $add_all_tags,
//					'card_get'               => $cards_query->get(),
//					'card_query_sql'         => $cards_query->toSql(),
////					'$cards'                 => $cards,
//					'Manager::getQueryLog()' => Manager::getQueryLog(),
//					'study_id'               => $study_id,
//				] );


            return [
                'cards' => $all_cards->get(),
            ];

        } catch (ItemNotFoundException $e) {
            //todo handle later
            return [
                'cards' => [],
            ];
        } catch (ModelNotFoundException $e) {
            //todo handle later
            return [
                'cards' => [],
            ];
        }


    }

    public static function get_user_cards_new($study_id, $user_id)
    {

        try {
            $user_timezone_today_midnight = get_user_timezone_date_midnight_today($user_id);

            $study         = Study::with('tags')->findOrFail($study_id);
            $deck_id       = $study->deck_id;
            $tags          = [];
            $add_all_tags  = $study->all_tags;
            $study_all_new = $study->study_all_new;
            $no_of_new     = $study->no_of_new;

            if (!$add_all_tags) {
                $tags = $study->tags->pluck('id');
            }

            /*** Get all new cards answered today "Only those answered once and today are truly new" ***/
            $query_new_answered_today     = Answered::where('study_id', '=', $study_id)
                ->where('created_at', '>', $user_timezone_today_midnight)
                ->where('grade', '!=', 'again')
                ->where('answered_as_new', '=', true);
            $new_card_ids_answered_today  = $query_new_answered_today->pluck('card_id');
            $count_new_studied_today      = $new_card_ids_answered_today->count();
            $no_of_new_remaining_to_study = $no_of_new - $count_new_studied_today;

//				Common::send_error( [
//					'sql'                           => $query_new_answered_today->toSql(),
//					'getBindings'                   => $query_new_answered_today->getBindings(),
//					'$count_new_studied_today'      => $count_new_studied_today,
//					'$no_of_new_remaining_to_study' => $no_of_new_remaining_to_study,
//					'$new_card_ids_answered_today'  => $new_card_ids_answered_today,
//				] );

            /*** Prepare basic query ***/
            $cards_query = Manager::table(SP_TABLE_CARDS.' as c')
                ->leftJoin(SP_TABLE_CARD_GROUPS.' as cg', 'cg.id', '=', 'c.card_group_id')
                ->leftJoin(SP_TABLE_DECKS.' as d', 'd.id', '=', 'cg.deck_id')
                ->leftJoin(SP_TABLE_TAGGABLES.' as tg', 'tg.taggable_id', '=', 'cg.id')
                ->leftJoin(SP_TABLE_TAGS.' as t', 't.id', '=', 'tg.tag_id')
                ->where('tg.taggable_type', '=', CardGroup::class)
//					->whereNotIn( 'c.id', function ( $q ) use ( $study_id ) {
//						$q->select( 'card_id' )->from( SP_TABLE_ANSWERED . ' as a' )
//							->where( 'study_id', '=', $study_id )
//							->distinct();
//					} )
                ->select(
                    'c.id as card_id'
                );

            /*** Add just a few tags? ***/
            if (!$add_all_tags) {
                $cards_query = $cards_query->whereIn('t.id', $tags);
            }

            /*** Study just a few new cards? ***/
            if (!$study_all_new) {
                $cards_query = $cards_query->limit($no_of_new_remaining_to_study);
            }

            /*** Filter out new cards answered today "Except those with grade as 'again' " ***/
            $cards_query = $cards_query
                ->whereNotIn('c.id', $new_card_ids_answered_today);

            /*** Filter out cards answered today with grade not "again" ***/
            $cards_query = $cards_query
                ->whereNotIn('c.id', function ($q) use (
                    $user_timezone_today_midnight,
                    $user_id
                ) {
//                    $q->select('card_id')->from(SP_TABLE_ANSWERED)
//                        ->where('grade', '!=', 'again');
                    $q
                        ->select('card_id')
                        ->from(SP_TABLE_ANSWERED.' as aaa')
                        ->leftJoin(SP_TABLE_STUDY.' as sss', 'sss.id', '=', 'aaa.study_id')
                        ->leftJoin(SP_TABLE_USERS.' as uuu', 'uuu.ID', '=', 'sss.user_id')
                        ->where('uuu.ID', '=', $user_id)
                        ->where('aaa.created_at', '>', $user_timezone_today_midnight)
                        ->distinct();
                });

            /*** Filter out cards answered before today ***/
            $cards_query->whereNotIn('c.id', function ($q) use ($user_id) {
                $q
                    ->select('card_id')
                    ->from(SP_TABLE_ANSWERED.' as aaa')
                    ->leftJoin(SP_TABLE_STUDY.' as sss', 'sss.id', '=', 'aaa.study_id')
                    ->leftJoin(SP_TABLE_USERS.' as uuu', 'uuu.ID', '=', 'sss.user_id')
                    ->where('uuu.ID', '=', $user_id)
                    ->distinct();
//                        Common::send_error([
//                            __METHOD__,
//                            '$q sql' => $q->toSql(),
//                            '$q get' => $q->get(),
//                            '$q getBindings' => $q->getBindings(),
//                            '$q' => $q,
//                        ]);
            });

            /*** Group by c.id "To prevent duplicate results being returned" **/
            $cards_query = $cards_query->where('d.id', '=', $deck_id)
                ->groupBy('c.id');

            $card_ids = $cards_query->pluck('card_id');

            /*** Get the cards ***/
            $all_cards = Card::with('card_group', 'card_group.deck')
                ->whereIn('id', $card_ids);
//            Common::send_error([
//                '$card_ids'                   => $card_ids,
//                '$user_timezone_today_midnight'                   => $user_timezone_today_midnight,
//                '$all_cards->toSql()'         => $all_cards->toSql(),
//                '$all_cards->getBindings()'   => $all_cards->getBindings(),
//                '$all_cards->get()'           => $all_cards->get(),
//                '$cards_query->toSql()'       => $cards_query->toSql(),
//                '$cards_query->getBindings()' => $cards_query->getBindings(),
//                '$cards_query->get('          => $cards_query->get(),
//            ]);
//            dd(
//                $card_ids,
//                $all_cards->toSql(),
//                $all_cards->getBindings(),
//                $all_cards->get(),
//                $cards_query->toSql(),
//                $cards_query->getBindings(),
//                $cards_query->get()
//            );


            if (36 === $study_id) {
//                Common::send_error([
//                    __METHOD__,
//                    '$all_cards toSql'       => $all_cards->toSql(),
//                    '$all_cards'             => $all_cards->get(),
//                    '$study'                 => $study,
//                    '$tags'                  => $tags,
//                    '$add_all_tags'          => $add_all_tags,
//                    'card_get'               => $cards_query->get(),
//                    'card_query_sql'         => $cards_query->toSql(),
//                    //					'$cards'                 => $cards,
//                    'Manager::getQueryLog()' => Manager::getQueryLog(),
//                    'study_id'               => $study_id,
//                    '$user_id'               => $user_id,
//                ]);
            }


            return [
                'cards' => $all_cards->get(),
            ];

        } catch (ItemNotFoundException $e) {
            //todo handle later
            return [
                'cards' => [],
            ];
        } catch (ModelNotFoundException $e) {
            //todo handle later
            return [
                'cards' => [],
            ];
        }


    }

    public static function get_user_cards_to_study($study_id, $user_id)
    {
        $all_cards = [];

        $user_cards_new     = Study::get_user_cards_new($study_id, $user_id);
        $user_cards_revise  = Study::get_user_cards_to_revise($study_id, $user_id);
        $user_cards_on_hold = Study::get_user_cards_on_hold($study_id, $user_id);

        foreach ($user_cards_new['cards'] as $one) {
            $one->answering_type = 'New Card';
            $all_cards[]         = $one;
        }
        foreach ($user_cards_on_hold['cards'] as $one) {
            $one->answering_type = 'Previously On hold';
            $all_cards[]         = $one;
        }
        foreach ($user_cards_revise['cards'] as $one) {
            $one->answering_type = 'Revising Card';
            $all_cards[]         = $one;
        }
//			foreach ( $all_cards as $card ) {
//				if ( 'table' === $card->card_group->card_type ) {
////					$card->question = json_decode( $card->question );
//					$card->answer   = json_decode( $card->answer );
//				} elseif ( 'image' === $card->card_group->card_type ) {
//					$card->question = json_decode( $card->question );
//					$card->answer   = json_decode( $card->answer );
//				}
//			}

        return $all_cards;
    }

    public static function get_study_due_summary($study_id, $user_id)
    {
        $new_cards       = self::get_user_cards_new($study_id, $user_id)['cards'];
        $cards_to_revise = self::get_user_cards_to_revise($study_id, $user_id)['cards'];
        $cards_on_hold   = self::get_user_cards_on_hold($study_id, $user_id)['cards'];

        return [
            'new'              => count($new_cards),
            'revision'         => count($cards_to_revise),
            'previously_false' => count($cards_on_hold),
            // todo on hold is used instead of previously false. Clarify later from client
            'new_cards'        => $new_cards, //todo remove after testing
        ];
    }

    /******* */
    public static function get_all_new_cards_in_user_studies($user_id)
    {
        $all_card_ids = [];
        $debug_info   = [];
        $user         = User
            ::with([
                'studies.deck.card_groups.cards' => function ($query) use ($user_id) {
                    $query->whereNotIn('id', function ($q) use ($user_id) {
                        $q
                            ->select('card_id')
                            ->from(SP_TABLE_ANSWERED.' as aaa')
                            ->leftJoin(SP_TABLE_STUDY.' as sss', 'sss.id', '=', 'aaa.study_id')
                            ->leftJoin(SP_TABLE_USERS.' as uuu', 'uuu.id', '=', 'sss.user_id')
                            ->where('uuu.id', '=', $user_id)
                            ->distinct() //todo improve, limit by study_id or user_id
                        ;
//                        Common::send_error([
//                            __METHOD__,
//                            '$q sql' => $q->toSql(),
//                            '$q get' => $q->get(),
//                            '$q' => $q,
//                        ]);
                    });
                }
            ])
            ->where('ID', '=', $user_id);
        $user_studies = $user->get()->first()->studies;
        Common::send_error([
            __METHOD__,
            '$study'                 => $user_studies,
            'Manager::getQueryLog()' => Manager::getQueryLog(),
        ]);


        foreach ($user_studies as $key => $study) {
            $all_plucked = $study->deck->card_groups->pluck('cards')->all();
            foreach ($all_plucked as $_pluck) {
                if (!empty($_pluck)) {
                    continue;
                }
                foreach ($_pluck as $card) {
                    $all_card_ids[] = $card->id;
                }
            }
//            $get = $study->card;
//            Common::send_error([
//                __METHOD__,
//                '$study' => $study,
//                '$study_all_new' => $study_all_new,
//                '$no_of_new' => $no_of_new,
//                'pluck' => $study->deck->card_groups->pluck('cards')->all(),
//                'Manager::getQueryLog()' => Manager::getQueryLog(),
//            ]);
        }
//        Common::send_error([
//            __METHOD__,
//            '$user_studies' => $user_studies,
//            '$study' => $study,
//        ]);

        $debug_info['user_studies'] = $user_studies;
        return [
            'card_ids'   => $all_card_ids,
            'debug_info' => $debug_info,
        ];
    }

    public static function get_all_card_ids_studied_today($user_id)
    {
        $query = Answered
            ::with([
                'study' => function ($query) use ($user_id) {
                    $query->where('user_id', '=', $user_id);
                }
            ])
            ->groupBy('card_id')
            ->where('create_at', '<', strtotime('today midnight'));
        Common::in_script([
            __METHOD__,
            'query toSql'       => $query->toSql(),
            'query get'         => $query->get(),
            'query getBindings' => $query->getBindings(),
        ]);
    }

}