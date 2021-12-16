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
use StudyPlanner\Helpers\ChartAddedHelper;
use StudyPlanner\Helpers\ChartAnswerButtonsHelper;
use StudyPlanner\Helpers\ChartCardTypes;
use StudyPlanner\Helpers\ChartForecastHelper;
use StudyPlanner\Helpers\ChartHourlyBreakDown;
use StudyPlanner\Helpers\ChartIntervalHelper;
use StudyPlanner\Helpers\ChartProgress;
use StudyPlanner\Helpers\ChartReviewHelper;
use StudyPlanner\Libs\Common;
use StudyPlanner\Libs\Settings;
use StudyPlanner\Models\Tag;
use function StudyPlanner\get_mature_card_days;
use function StudyPlanner\get_user_timezone_date_early_morning_today;
use function StudyPlanner\get_user_timezone_date_midnight_today;
use function StudyPlanner\get_user_timezone_minutes_to_add;

class Study extends Model {
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

    private static $sp_debug = [];

    public function tagsExcluded() {
        return $this->morphToMany(Tag::class, 'taggable', SP_TABLE_TAGGABLES_EXCLUDED);
    }

    public function tags_excluded() {
        return $this->morphToMany(Tag::class, 'taggable', SP_TABLE_TAGGABLES_EXCLUDED);
    }

    public function tags() {
        return $this->morphToMany(Tag::class, 'taggable', SP_TABLE_TAGGABLES);
    }

    public function deck() {
        return $this->belongsTo(Deck::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function cards() {
        return $this->hasManyDeep(Card::class, [
            Deck::class,
            CardGroup::class,
        ]);
        //			] )->where( 'user_id', '=', $user_id );
    }

    public function answers() {
        return $this->hasMany(Answered::class);
    }

    public function cardsGroups() {
        return $this->hasManyThrough(CardGroup::class, Deck::class);
    }

    private static function add_debug($title, $info, $for_time = false) {
        self::$sp_debug[] = [
            'title'    => $title,
            'info'     => $info,
            'for_time' => $for_time,
        ];
    }

    public static function get_user_studies($args): array {
        $user_id = get_current_user_id();
        $default = [
            'search'       => '',
            'page'         => 1,
            'per_page'     => 5,
            'with_trashed' => false,
            'only_trashed' => false,
            'user_id'      => 0,
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
            'tags_excluded',
            'deck',
            'user',
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

    public static function get_user_study_by_id($study_id) {
        return Study::with('tags', 'deck')->find($study_id);
    }

    public static function get_user_stats_card_types($user_id, $span) {
        //        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable                         = [
            'heading'      => [],
            'pie_data'     => [],
            'pie_data2'    => [],
            'total_cards'  => 0,
            'total_mature' => 0,
            'total_young'  => 0,
            'total_new'    => 0,
        ];
        $matured_day_no                    = get_mature_card_days();
        $end_date                          = null;
        $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today($user_id);
        $user_timezone_midnight_today      = get_user_timezone_date_midnight_today($user_id);
        $start_date                        = $user_timezone_midnight_today;
        $_date                             = new DateTime($start_date);
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

        $all_answers_card_types_matured = ChartCardTypes::get_all_card_types_matured([
            'user_id'           => $user_id,
            "start_date"        => $start_date,
            'end_date'          => $end_date,
            'matured_card_days' => $matured_day_no,
            'no_date_limit'     => ($end_date === null),
            //            'card_ids_not_in' => $matured_cards['card_ids'],
        ])['answers'];
        $all_answers_card_types_young   = ChartCardTypes::get_all_card_types_young([
            'user_id'           => $user_id,
            "start_date"        => $start_date,
            'end_date'          => $end_date,
            'matured_card_days' => $matured_day_no,
            'no_date_limit'     => ($end_date === null),
            //            'card_ids_not_in' => $matured_cards['card_ids'],
        ])['answers'];
        $forecast_new_cards_to_study    = ChartForecastHelper::get_forecast_cards_new([
            'user_id' => $user_id,
        ])['all'];

        $total_matured   = count($all_answers_card_types_matured->all());
        $total_young     = count($all_answers_card_types_young->all());
        $new_cards       = collect($forecast_new_cards_to_study)
            ->pluck('card_groups')->flatten()
            ->pluck('cards')->flatten();
        $total_new_cards = count($new_cards->all());

        $mature_title             = "Matured: $total_matured";
        $young_title              = "Young+Learn: $total_young";
        $new_title                = "Unseen: $total_new_cards";
        $graphable['heading'][]   = $mature_title;
        $graphable['heading'][]   = $young_title;
        $graphable['heading'][]   = $new_title;
        $graphable['pie_data'][]  = [
            'name'  => $mature_title,
            'value' => $total_matured,
        ];
        $graphable['pie_data'][]  = [
            'name'  => $young_title,
            'value' => $total_young,
        ];
        $graphable['pie_data'][]  = [
            'name'  => $new_title,
            'value' => $total_new_cards,
        ];
        $graphable['total_cards'] = $total_matured + $total_new_cards + $total_young;
        $graphable['pie_data2']   = [$total_matured, $total_young, $total_new_cards];

        //        Common::send_error([
        //            __METHOD__,
        //            '$total_new_cards'                => $total_new_cards,
        //            '$total_matured'                  => $total_matured,
        //            '$total_young'                    => $total_young,
        //            '$new_cards'                      => $new_cards,
        //            '$graphable'                      => $graphable,
        //            '$all_answers_card_types_matured' => $all_answers_card_types_matured,
        //            '$all_answers_card_types_young'   => $all_answers_card_types_young,
        //            '$forecast_new_cards_to_study'    => $forecast_new_cards_to_study,
        //        ]);

        return [
            'graphable' => $graphable,
        ];


    }

    public static function get_user_card_forecast($user_id, $span) {
        //        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable                         = [
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
        $matured_day_no                    = get_mature_card_days();
        $end_date                          = null;
        $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today($user_id);
        $start_date                        = $user_timezone_early_morning_today;
        $_date                             = new DateTime($start_date);
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
            'graphable' => $graphable,
        ];


    }

    public static function get_user_stats_charts_hourly_breakdown($user_id, $date) {
        $measure_start_time = microtime(true);
        //        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable = [
            'heading'            => [
                '12am', '1am', '2am', '3am', '4am', '5am', '6am', '7am', '8am', '9am', '10am', '11am', '12pm',
                '1pm', '2pm', '3pm', '4pm', '5pm', '6pm', '7pm', '8pm', '9pm', '10pm', '11pm',
            ],
            'answers_per_hour'   => [],
            'percentage_correct' => [],
        ];
        $days      = [];
        for ($a = 0; $a < 24; $a++) {
            $days[] = [
                'hour'               => $a,
                'count'              => 0,
                'percentage_correct' => 0,
            ];
        }

        $all_answers_hourly_break_down = ChartHourlyBreakDown::get_all_answers_hourly_break_down([
            'user_id' => $user_id,
            'date'    => $date,
        ])['answers'];

        $all_answers_hourly_break_down_with_grades = ChartHourlyBreakDown::get_all_answers_hourly_break_down_with_grades([
            'user_id' => $user_id,
            'date'    => $date,
        ])['answers'];

        //        Common::send_error([
        //            __METHOD__,
        //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //            '$days'                               => $days,
        //        ]);

        $total_answers_in_all_hours = 0;
        $total_correct              = 0;
        foreach ($all_answers_hourly_break_down as $answer) {
            //            $day_diff_today   = $answer->day_diff_today;
            $the_hour_count             = $answer->the_hour_count;
            $grade                      = $answer->grade;
            $the_hour                   = $answer->the_hour;
            $total_answers_in_all_hours += $the_hour_count;
            $days[$the_hour]['hour']    = $the_hour;
            $days[$the_hour]['count']   = $the_hour_count;
            if (in_array($grade, ['hard', 'good', 'easy'])) {
                $total_correct += $the_hour_count;
            }
            //            Common::send_error([
            //                __METHOD__,
            //                '$the_hour_count'                     => $the_hour_count,
            //                '$the_hour'                           => $the_hour,
            //                '$answer'                             => $answer,
            //                '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
            //            ]);
        }

        foreach ($days as $key => $value) {
            $percentage_correct               = ($total_correct < 1) ? 0 : ($value['count'] / $total_correct) * 100;
            $days[$key]['percentage_correct'] = $percentage_correct;
        }
        foreach ($days as $key => $value) {
            $graphable['answers_per_hour'][]   = $value['count'];
            $graphable['percentage_correct'][] = $value['percentage_correct'];
        }

        //        Common::send_error([
        //            __METHOD__,
        //            '$days'                                      => $days,
        //            '$all_answers_hourly_break_down'             => $all_answers_hourly_break_down,
        //            '$all_answers_hourly_break_down_with_grades' => $all_answers_hourly_break_down_with_grades,
        //            '$graphable'                                 => $graphable,
        //        ]);

        return [
            'graphable' => $graphable,
        ];

    }

    public static function get_user_stats_charts_answer_buttons($user_id, $span) {
        $measure_start_time = microtime(true);
        //        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable                         = [
            'heading'  => [],
            'learning' => [],
            'y'        => [],
            'm'        => [],
        ];
        $matured_day_no                    = get_mature_card_days();
        $end_date                          = null;
        $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today($user_id);
        $user_timezone_mid_night_today     = get_user_timezone_date_midnight_today($user_id);
        //        $start_date                   = $user_timezone_early_morning_today;
        $end_date = $user_timezone_mid_night_today;
        $_date    = new DateTime($user_timezone_early_morning_today);
        if ('one_month' === $span) {
            $_date->sub(new DateInterval('P30D'));
        } elseif ('three_month' === $span) {
            $_date->sub(new DateInterval('P3M'));
        } elseif ('one_year' === $span) {
            $_date->sub(new DateInterval('P1Y'));
        } elseif ('all' === $span) {
            $oldest_answer_query = self::get_user_oldest_answer($user_id);
            $start_date          = $oldest_answer_query->get()->first()->next_due_at;
            //            Common::send_error([
            //                __METHOD__,
            //                '$oldest_answer_query sql' => $oldest_answer_query->toSql(),
            //                '$_date ' => $_date,
            //                '$oldest_answer_query sql getBindings' => $oldest_answer_query->getBindings(),
            //                '$oldest_answer_query get' => $oldest_answer_query->get(),
            //            ]);
        }
        if ('all' !== $span) {
            $start_date = $_date->format('Y-m-d H:i:s');
        }
        $_start_date = new DateTime($start_date);
        $_end_date   = new DateTime($end_date);

        $total_no_of_days = (int) $_end_date->diff($_start_date)->format("%a"); //3
        $days             = [
            'm'        => [
                'again'           => 0,
                'hard'            => 0,
                'good'            => 0,
                'easy'            => 0,
                'hold'            => 0,
                'total'           => 0,
                'total_correct'   => 0,
                'correct_percent' => 0,
            ],
            'y'        => [
                'again'           => 0,
                'hard'            => 0,
                'good'            => 0,
                'easy'            => 0,
                'hold'            => 0,
                'total'           => 0,
                'total_correct'   => 0,
                'correct_percent' => 0,
            ],
            // answered only once
            'learning' => [
                'again'           => 0,
                'hard'            => 0,
                'good'            => 0,
                'easy'            => 0,
                'hold'            => 0,
                'total'           => 0,
                'total_correct'   => 0,
                'correct_percent' => 0,
            ],
        ];
        $__a_count        = 0 - $total_no_of_days + 1;

        $forecast_all_answers_within_a_date = ChartAnswerButtonsHelper::get_all_answers_button_clicks([
            'user_id'       => $user_id,
            "start_date"    => $start_date,
            'end_date'      => $end_date,
            'no_date_limit' => ($end_date === null),
            //            'card_ids_not_in' => $matured_cards['card_ids'],
        ])['answers'];
        $all_answers_newly_learned          = ChartAnswerButtonsHelper::get_all_answers_answered_just_once($user_id)['answers'];
        $all_learning_answer_ids            = $all_answers_newly_learned->pluck('id')->all();

        //        Common::send_error([
        //            __METHOD__,
        //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //        ]);
        $all_is_learning = [];
        foreach ($forecast_all_answers_within_a_date as $answer) {
            //            $day_diff_today   = $answer->day_diff_today;
            $day_dif          = $answer->day_diff;
            $grade            = $answer->grade;
            $is_learning_card = in_array($answer->id, $all_learning_answer_ids);
            $is_matured       = $day_dif >= $matured_day_no;

            $_key = 'm';
            if ($is_matured) {
                $_key = 'm';
            } elseif ($is_learning_card) {
                $_key = 'learning';
            } else {
                // Is young
                $_key = 'y';
            }

            $all_is_learning[] = [
                '$is_learning_card'        => $is_learning_card,
                '$answer'                  => $answer,
                '$answer->card_id'         => $answer->card_id,
                '$all_learning_answer_ids' => $all_learning_answer_ids,
                '$is_matured'              => $is_matured,
            ];

            $days[$_key][$grade]++;
            $days[$_key]['total']++;
            if (!in_array($answer->grade, ['again', 'hold'])) {
                $days[$_key]['total_correct']++;
            }

            //            if ($day_dif >= $matured_day_no) {
            //                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
            //                $days[$day_diff_today]['m']['1_again']++;
            //                $days[$day_diff_today]['m']['answers'][] = $answer;
            //            }

            //            Common::send_error([
            //                __METHOD__,
            //                '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
            //                '$is_learning_card'                   => $is_learning_card,
            //                '$answer'                             => $answer,
            //                '$day_diff_today'                     => $day_diff_today,
            //                '$all_learning_cards'                 => $all_learning_cards_id,
            //                '$days'                               => $days,
            //                '$answer->card_id'                    => $answer->card_id,
            //                'type'                                => gettype($all_learning_cards_id),
            //                '$grade'                              => $grade,
            //                '$all_answers_newly_learned'          => $all_answers_newly_learned,
            //                '$_start_date'                        => $_start_date,
            //                '$_end_date'                          => $_end_date,
            //            ]);

        }

        $graphable['learning']               = [
            $days['learning']['again'],
            $days['learning']['hard'],
            $days['learning']['good'],
            $days['learning']['easy'],
        ];
        $graphable['m']                      = [
            $days['m']['again'],
            $days['m']['hard'],
            $days['m']['good'],
            $days['m']['easy'],
        ];
        $graphable['y']                      = [
            $days['y']['again'],
            $days['y']['hard'],
            $days['y']['good'],
            $days['y']['easy'],
        ];
        $learning_correct_percent            = ($days['learning']['total'] < 1) ? 0 : ($days['learning']['total_correct'] / $days['learning']['total']) * 100;
        $y_correct_percent                   = ($days['y']['total'] < 1) ? 0 : ($days['y']['total_correct'] / $days['y']['total']) * 100;
        $m_correct_percent                   = ($days['m']['total'] < 1) ? 0 : ($days['m']['total_correct'] / $days['m']['total']) * 100;
        $days['learning']['correct_percent'] = number_format(($learning_correct_percent), 2);
        $days['y']['correct_percent']        = number_format($y_correct_percent, 2);
        $days['m']['correct_percent']        = number_format($m_correct_percent, 2);
        $graphable['days']                   = $days;

        //        Common::send_error([
        //            __METHOD__,
        //            'user_id'                               => $user_id,
        //            "start_date"                            => $start_date,
        //            'end_date'                              => $end_date,
        //            '$forecast_all_answers_within_a_date'   => $forecast_all_answers_within_a_date,
        //            '$days'                                 => $days,
        //            '$all_learning_answer_ids'              => $all_learning_answer_ids,
        //            '$all_is_learning'                      => $all_is_learning,
        //            '$all_answers_newly_learned'            => $all_answers_newly_learned,
        //            '$matured_day_no'                       => $matured_day_no,
        //            '$graphable'                            => $graphable,
        //            '$forecast_all_answers_within_a_date 0' => $forecast_all_answers_within_a_date[0],
        //        ]);

        return [
            'graphable' => $graphable,
        ];

    }

    public static function get_user_stats_progress_chart($user_id, $year) {
        $measure_start_time = microtime(true);
        //        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable = [
            'heading'              => [],
            'days_and_count'       => [],
            'total_daily_average'  => [],
            'total_daily_percent'  => [],
            'total_longest_streak' => [],
            'total_current_streak' => [],
        ];

        //        $forecast_all_answers_within_a_date = ChartProgress::get_all_answers_count_by_day([
        //            'user_id' => $user_id,
        //            "year"    => $year,
        //        ])['answers'];
        $days_learnt_count  = ChartProgress::get_days_learnt_count([
            'user_id' => $user_id,
            "year"    => $year,
        ]);
        $days_learnt_streak = ChartProgress::get_days_learnt_streak([
            'user_id' => $user_id,
            "year"    => $year,
        ]);
        // todo calculate streak

        $days_learned = $days_learnt_count['total'];

        $longest_streak = [];
        $streak_store   = [];
        $current_streak = [];
        $previous_date  = 0;
        foreach ($days_learnt_streak['answers'] as $key => $one) {
            if (0 === $key) {
                $current_streak[] = $one;
                $longest_streak   = $current_streak;
                $previous_date    = $one->the_date;
                continue;
            }
            $the_date         = $one->the_date;
            $_date_minus_1day = new DateTime($the_date);
            $_date_minus_1day->sub(new DateInterval('P1D'));
            $_date_minus_1day         = $_date_minus_1day->format('Y-m-d');
            $current_streak_is_broken = ($previous_date !== $_date_minus_1day);
            $previous_date            = $one->the_date;
            //            Common::send_error([
            //                __METHOD__,
            //                '$key'                      => $key,
            //                '$previous_date'            => $previous_date,
            //                '$days_learned'             => $days_learned,
            //                '$days_learnt_count'        => $days_learnt_count,
            //                '$current_streak_is_broken' => $current_streak_is_broken,
            //                '$days_learnt_streak'       => $days_learnt_streak,
            //                '$_date_minus_1day'         => $_date_minus_1day,
            //            ]);
            if ($current_streak_is_broken) {
                $streak_store[] = [
                    'broken'            => true,
                    'streak'            => $current_streak,
                    '$previous_date'    => $previous_date,
                    '$_date_minus_1day' => $_date_minus_1day,
                ];
                if (count($current_streak) > count($longest_streak)) {
                    $longest_streak = $current_streak;
                }
                $current_streak = [];
            }
            $current_streak[] = $one;
            //            Common::send_error([
            //                __METHOD__,
            //                '$key'                => $key,
            //                '$day_number'         => $day_number,
            //                '$days_learned'       => $days_learned,
            //                //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
            //                '$days_learnt_count'  => $days_learnt_count,
            //                '$days_learnt_streak' => $days_learnt_streak,
            //            ]);

        }

        $graphable['days_and_count']       = $days_learnt_count['answers'];
        $graphable['total_daily_average']  = number_format($days_learnt_count['average'], 2);
        $graphable['total_daily_percent']  = number_format($days_learnt_count['percentage_days_learnt'], 2);
        $graphable['total_longest_streak'] = count($longest_streak);
        $graphable['total_current_streak'] = count($current_streak);


        //        Common::send_error([
        //            __METHOD__,
        //            '$days_learned'       => $days_learned,
        //            //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //            '$days_learnt_count'  => $days_learnt_count,
        //            '$days_learnt_streak' => $days_learnt_streak,
        //            '$longest_streak'     => $longest_streak,
        //            '$streak_store'       => $streak_store,
        //            '$current_streak'     => $current_streak,
        //            '$graphable'          => $graphable,
        //        ]);

        $measure_end_time                       = microtime(true);
        $measure_execution_time                 = ($measure_end_time - $measure_start_time);
        $graphable['zz_measure_execution_time'] = $measure_execution_time;
        $graphable['zz_debug']                  = self::$sp_debug;

        //        Common::send_error([
        //            '$start_date'                         => $start_date,
        //            '$end_date'                           => $end_date,
        //            '$span'                               => $span,
        //            '$graphable'                          => $graphable,
        //            '$total_time_hours'                   => $total_time_hours,
        //            '$no_of_days'                         => $total_no_of_days,
        //            '$total_answers_count'                => $total_answers_count,
        //            '$total_days_studied'                 => $total_days_studied,
        //            '$days'                               => $days,
        //            '$__a_count'                          => $__a_count,
        //            '$days_not_learnt'                    => $days_not_learnt,
        //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //            'Manager::getQueryLog()'              => Manager::getQueryLog(),
        //        ]);

        return [
            'graphable' => $graphable,
        ];

    }

    public static function get_user_stats_charts_added($user_id, $span) {
        $measure_start_time = microtime(true);
        //        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable                         = [
            'heading'                   => [],
            'new_cards_added'           => [],
            'cumulative_new_cards'      => [],
            'total_new_cards'           => 0,
            'average_new_cards_per_day' => 0,
        ];
        $matured_day_no                    = get_mature_card_days();
        $end_date                          = null;
        $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today($user_id);
        $user_timezone_midnight_today      = get_user_timezone_date_midnight_today($user_id);
        //        $start_date                   = $user_timezone_early_morning_today;
        $end_date = $user_timezone_midnight_today;
        $_date    = new DateTime($user_timezone_early_morning_today);
        if ('one_month' === $span) {
            $_date->sub(new DateInterval('P30D'));
        } elseif ('three_month' === $span) {
            $_date->sub(new DateInterval('P3M'));
        } elseif ('one_year' === $span) {
            $_date->sub(new DateInterval('P1Y'));
        } elseif ('all' === $span) {
            $oldest_answer_query = Answered
                ::orderBy('next_due_at')
                ->limit(1);
            $start_date          = $oldest_answer_query->get()->first()->next_due_at;
            //            Common::send_error([
            //                __METHOD__,
            //                '$oldest_answer_query sql' => $oldest_answer_query->toSql(),
            //                '$_date ' => $_date,
            //                '$oldest_answer_query sql getBindings' => $oldest_answer_query->getBindings(),
            //                '$oldest_answer_query get' => $oldest_answer_query->get(),
            //            ]);
        }
        if ('all' !== $span) {
            $start_date = $_date->format('Y-m-d H:i:s');
        }
        $_start_date = new DateTime($start_date);
        $_end_date   = new DateTime($end_date);

        $total_no_of_days = (int) $_end_date->diff($_start_date)->format("%a"); //3
        $days             = [];
        $__a_count        = 0 - $total_no_of_days + 1;
        for ($_a = 0; $_a < $total_no_of_days; $_a++) {
            $graphable['heading'][] = $__a_count.'d';
            $__a_count++;
            $days[] = [
                'new_cards_added'      => 0,
                'new_cards_cumulative' => 0,
            ];
        }

        $forecast_all_answers_within_a_date = ChartAddedHelper::get_all_new_cards_added([
            'user_id'       => $user_id,
            "start_date"    => $start_date,
            'end_date'      => $end_date,
            'no_date_limit' => ($end_date === null),
            //            'card_ids_not_in' => $matured_cards['card_ids'],
        ])['answers'];

        //                Common::send_error([
        //                    __METHOD__,
        //                    'user_id'       => $user_id,
        //                    "start_date"    => $start_date,
        //                    'end_date'      => $end_date,
        //                    '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //                ]);


        foreach ($forecast_all_answers_within_a_date as $answer) {
            $day_diff_today                           = $answer->day_diff_today;
            $days[$day_diff_today]['new_cards_added'] += 1;
            //            Common::send_error([
            //                __METHOD__,
            //                '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
            //                '$answer'                             => $answer,
            //                '$day_diff_today'                     => $day_diff_today,
            //                '$days'                               => $days,
            //            ]);

        }
        //        Common::send_error([
        //            __METHOD__,
        //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //            '$answer'                             => $answer,
        //            '$day_diff_today'                     => $day_diff_today,
        //            '$days'                               => $days,
        //        ]);

        $cumulative_new_cards = 0;
        $days_not_learnt      = 0;
        foreach ($days as $key => $day) {
            $cumulative_new_cards                += $day['new_cards_added'];
            $graphable['new_cards_added'][]      = $day['new_cards_added'];
            $graphable['cumulative_new_cards'][] = $cumulative_new_cards;
            if (empty($day['new_cards_added'])) {
                $days_not_learnt++;
            }
        }
        $total_days_studied                     = $total_no_of_days - $days_not_learnt;
        $total_new_cards                        = count($forecast_all_answers_within_a_date);
        $graphable['total_new_cards']           = $total_new_cards;
        $graphable['average_new_cards_per_day'] = number_format(($total_new_cards / $total_no_of_days), 2);

        //        Common::send_error([
        //            __METHOD__,
        //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //            '$answer'                             => $answer,
        //            '$day_diff_today'                     => $day_diff_today,
        //            '$days'                               => $days,
        //            'user_id'       => $user_id,
        //            "start_date"    => $start_date,
        //            'end_date'      => $end_date,
        //            '$graphable'                          => $graphable,
        //        ]);

        $measure_end_time                       = microtime(true);
        $measure_execution_time                 = ($measure_end_time - $measure_start_time);
        $graphable['zz_measure_execution_time'] = $measure_execution_time;
        $graphable['zz_debug']                  = self::$sp_debug;

        //        Common::send_error([
        //            '$start_date'                         => $start_date,
        //            '$end_date'                           => $end_date,
        //            '$span'                               => $span,
        //            '$graphable'                          => $graphable,
        //            '$total_time_hours'                   => $total_time_hours,
        //            '$no_of_days'                         => $total_no_of_days,
        //            '$total_answers_count'                => $total_answers_count,
        //            '$total_days_studied'                 => $total_days_studied,
        //            '$days'                               => $days,
        //            '$__a_count'                          => $__a_count,
        //            '$days_not_learnt'                    => $days_not_learnt,
        //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //            'Manager::getQueryLog()'              => Manager::getQueryLog(),
        //        ]);

        return [
            'graphable' => $graphable,
        ];

    }

    public static function get_user_stats_charts_intervals($user_id, $span) {
        $measure_start_time = microtime(true);
        //        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable                         = [
            'heading'                 => [],
            'day_diffs'               => [],
            'day_diff_percentages'    => [],
            'day_diff_average'        => 0,
            'longest_interval'        => 0,
            'formed_longest_interval' => 0,
        ];
        $matured_day_no                    = get_mature_card_days();
        $end_date                          = null;
        $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today($user_id);
        $user_timezone_midnight_today      = get_user_timezone_date_midnight_today($user_id);
        //        $start_date                   = $user_timezone_early_morning_today;
        $end_date = $user_timezone_midnight_today;
        $_date    = new DateTime($user_timezone_early_morning_today);
        if ('one_month' === $span) {
            $_date->sub(new DateInterval('P30D'));
        } elseif ('three_month' === $span) {
            $_date->sub(new DateInterval('P3M'));
        } elseif ('one_year' === $span) {
            $_date->sub(new DateInterval('P1Y'));
        } elseif ('all' === $span) {
            $oldest_answer_query = Answered
                ::orderBy('next_due_at')
                ->limit(1);
            $start_date          = $oldest_answer_query->get()->first()->next_due_at;
            //            Common::send_error([
            //                __METHOD__,
            //                '$oldest_answer_query sql' => $oldest_answer_query->toSql(),
            //                '$_date ' => $_date,
            //                '$oldest_answer_query sql getBindings' => $oldest_answer_query->getBindings(),
            //                '$oldest_answer_query get' => $oldest_answer_query->get(),
            //            ]);
        }
        if ('all' !== $span) {
            $start_date = $_date->format('Y-m-d H:i:s');
        }
        $_start_date = new DateTime($start_date);
        $_end_date   = new DateTime($end_date);

        $total_no_of_days = (int) $_end_date->diff($_start_date)->format("%a"); //3
        $days             = [
            'day_diff' => [],
        ];
        $__a_count        = 0 - $total_no_of_days + 1;

        $forecast_all_answers_within_a_date = ChartIntervalHelper::get_all_answers_with_next_due_intervals([
            'user_id'       => $user_id,
            "start_date"    => $start_date,
            'end_date'      => $end_date,
            'no_date_limit' => ($end_date === null),
            //            'card_ids_not_in' => $matured_cards['card_ids'],
        ])['answers'];

        //                Common::send_error([
        //                    __METHOD__,
        //                    'user_id'       => $user_id,
        //                    "start_date"                          => $start_date,
        //                    'end_date'                            => $end_date,
        //                    '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //                ]);

        $max_day_diff         = 0;
        $day_diff_count_total = 0;
        $no_of_day_diffs      = 0;
        foreach ($forecast_all_answers_within_a_date as $answer) {
            // Get counts
            $no_of_day_diffs++;
            $day_diff_count       = $answer->day_diff_count;
            $day_diff             = $answer->day_diff;
            $day_diff_count_total += $day_diff_count;
            $days['day_diff'][]   = [
                'day_diff'              => $day_diff,
                'count'                 => $day_diff_count,
                'percentage_cumulation' => 0,
            ];
            if ($day_diff > $max_day_diff) {
                $max_day_diff = $day_diff;
            }
            //            $days[$day_diff_today]['new_cards_added'] += 1;
            //            Common::send_error([
            //                __METHOD__,
            //                '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
            //                '$answer'                             => $answer,
            //                '$interval_count'                     => $interval_count,
            //                '$day_diff'                           => $day_diff,
            //                '$days'                               => $days,
            //            ]);
        }


        //        Common::send_error([
        //            __METHOD__,
        //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //            '$answer'                             => $answer,
        //            '$day_diff_today'                     => $day_diff_today,
        //            '$days'                               => $days,
        //        ]);

        $cumulate_day_diff_so_far = 0;

        foreach ($days['day_diff'] as $key => $value) {
            // Get percentages
            $cumulate_day_diff_so_far                        += $value['count'];
            $days['day_diff'][$key]['percentage_cumulation'] = ($cumulate_day_diff_so_far / $day_diff_count_total) * 100;

            //            Common::send_error([
            //                __METHOD__,
            //                '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
            //                '$answer'                             => $answer,
            //                '$max_day_diff'                       => $max_day_diff,
            //                '$day_diff_count_total'               => $day_diff_count_total,
            //                '$days'                               => $days,
            //                '$value'                              => $value,
            //            ]);
        }
        for ($a = 0; $a <= $max_day_diff; $a++) {
            // Get headings
            $graphable['heading'][]                = $a.'d';
            $graphable['day_diffs'][$a]            = 0;
            $graphable['day_diff_percentages'][$a] = 0;
        }
        if (empty($graphable['heading'])) {
            $graphable['heading'] = ['1d', '2d', '3d', '4d', '5d', '6d', '7d'];
        }
        foreach ($days['day_diff'] as $value) {
            // Fill $graphings
            $graphable['day_diffs'][$value['day_diff']]            = $value['count'];
            $graphable['day_diff_percentages'][$value['day_diff']] = $value['percentage_cumulation'];
        }
        $day_diff_average                     = (0 === $no_of_day_diffs) ? 0 : $day_diff_count_total / $no_of_day_diffs;
        $graphable['days']                    = $days;
        $graphable['longest_interval']        = $max_day_diff;
        $graphable['day_diff_average']        = number_format($day_diff_average, 2);
        $graphable['formed_day_diff_average'] = Common::get_days_or_months($day_diff_average);
        $graphable['formed_longest_interval'] = Common::get_days_or_months($max_day_diff);

        //        Common::send_error([
        //            __METHOD__,
        //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //            '$answer'                             => $answer,
        //            '$no_of_day_diffs'                    => $no_of_day_diffs,
        //            '$max_day_diff'                       => $max_day_diff,
        //            '$day_diff_count_total'               => $day_diff_count_total,
        //            '$days'                               => $days,
        //            '$value'                              => $value,
        //            '$graphable'                          => $graphable,
        //        ]);


        $measure_end_time                       = microtime(true);
        $measure_execution_time                 = ($measure_end_time - $measure_start_time);
        $graphable['zz_measure_execution_time'] = $measure_execution_time;
        $graphable['zz_debug']                  = self::$sp_debug;


        return [
            'graphable' => $graphable,
        ];

    }

    public static function get_user_card_review_count_and_time($user_id, $span) {
        $measure_start_time = microtime(true);
        //        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable                         = [
            'heading'                       => [],
            'cumulative'                    => [],
            'y'                             => [],
            'm'                             => [],
            'newly_learned'                 => [],
            'relearned'                     => [],
            'y_time'                        => [],
            'm_time'                        => [],
            'newly_learned_time'            => [],
            'relearned_time'                => [],
            'cumulative_y'                  => [],
            'cumulative_m'                  => [],
            'cumulative_newly_learned'      => [],
            'cumulative_relearned'          => [],
            'cumulative_y_time'             => [],
            'cumulative_m_time'             => [],
            'cumulative_newly_learned_time' => [],
            'cumulative_relearned_time'     => [],
            'y_debug'                       => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'm_debug'                       => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'newly_learned_debug'           => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'relearned_debug'               => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'm_cumulative'                  => 0,
            'y_cumulative'                  => 0,
            'newly_learned_cumulative'      => 0,
            're_learned_cumulative'         => 0,
            'm_cumulative_time'             => 0,
            'y_cumulative_time'             => 0,
            'newly_learned_cumulative_time' => 0,
            're_learned_cumulative_time'    => 0,
            'total_reviews_time'            => 0,
            'average'                       => 0,
            'average_if_studied_per_day'    => 0,
            'total_days'                    => 0,
            'total_hours'                   => 0,
            'total_minutes'                 => 0,
            'total_seconds'                 => 0,
            'total_days_time'               => 0,
            'days_studied_count'            => 0,
            'days_studied_time'             => 0,
            'days_not_studied__time'        => 0,
            'days_studied_percent_time'     => 0,
        ];
        $matured_day_no                    = get_mature_card_days();
        $end_date                          = null;
        $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today($user_id);
        //        $start_date                   = $user_timezone_early_morning_today;
        $end_date = $user_timezone_early_morning_today;
        $_date    = new DateTime($user_timezone_early_morning_today);
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

        $total_no_of_days = (int) $_end_date->diff($_start_date)->format("%a"); //3
        $days             = [];
        $__a_count        = 0 - $total_no_of_days + 1;
        for ($_a = 0; $_a < $total_no_of_days; $_a++) {
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
                    'count'           => 0,
                    'seconds_took'    => 0,
                    'minutes_took'    => 0,
                    'hours_took'      => 0,
                    'cumulative'      => 0,
                    'cumulative_time' => 0,
                    'answers'         => [],
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

        //                Common::send_error([
        //                    '$start_date'                         => $start_date,
        //                    '$end_date'                           => $end_date,
        //                    '$span'                               => $span,
        //                    '$days'                               => $days,
        //                    '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //                    'Manager::getQueryLog()'              => Manager::getQueryLog(),
        //                ]);

        $_cumulative_m             = 0;
        $_cumulative_y             = 0;
        $_cumulative_newly_learned = 0;
        $_cumulative_re_learned    = 0;
        $days_not_learnt           = 0;
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
            $seconds_took     = $answer->time_diff_second_spent;
            $minutes_took     = $seconds_took / 60;
            $hours_took       = $seconds_took / 3600;

            if ($is_matured) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_m += $hours_took;
                $days[$day_diff_today]['m']['count']++;
                $days[$day_diff_today]['m']['seconds_took'] += $seconds_took;
                $days[$day_diff_today]['m']['minutes_took'] += $minutes_took;
                $days[$day_diff_today]['m']['hours_took']   += $hours_took;
                $days[$day_diff_today]['m']['cumulative']   += $_cumulative_m;
                $days[$day_diff_today]['m']['answers'][]    = $answer;
            }
            if ($is_young) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_y += $hours_took;
                $days[$day_diff_today]['y']['count']++;
                $days[$day_diff_today]['y']['seconds_took'] += $seconds_took;
                $days[$day_diff_today]['y']['minutes_took'] += $minutes_took;
                $days[$day_diff_today]['y']['hours_took']   += $hours_took;
                $days[$day_diff_today]['y']['cumulative']   += $_cumulative_y;
                $days[$day_diff_today]['y']['answers'][]    = $answer;
            }
            if ($is_newly_learned) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_newly_learned += $hours_took;
                $days[$day_diff_today]['newly_learned']['count']++;
                $days[$day_diff_today]['newly_learned']['seconds_took'] += $seconds_took;
                $days[$day_diff_today]['newly_learned']['minutes_took'] += $minutes_took;
                $days[$day_diff_today]['newly_learned']['hours_took']   += $hours_took;
                $days[$day_diff_today]['newly_learned']['cumulative']   += $_cumulative_newly_learned;
                $days[$day_diff_today]['newly_learned']['answers'][]    = $answer;
            }
            if ($is_relearned) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_re_learned += $hours_took;
                $days[$day_diff_today]['re_learned']['count']++;
                $days[$day_diff_today]['re_learned']['seconds_took'] += $seconds_took;
                $days[$day_diff_today]['re_learned']['minutes_took'] += $minutes_took;
                $days[$day_diff_today]['re_learned']['hours_took']   += $hours_took;
                $days[$day_diff_today]['re_learned']['cumulative']   += $_cumulative_re_learned;
                $days[$day_diff_today]['re_learned']['answers'][]    = $answer;
            }

            //            Common::send_error([
            //                '$no_to_revise'                       => $no_to_revise,
            //                '$answer'                             => $answer,
            //                '$no_on_hold'                         => $no_on_hold,
            //                '$revise_all'                         => $revise_all,
            //                '$study_all_on_hold'                  => $study_all_on_hold,
            //                '$day_dif'                            => $day_dif,
            //                '$start_date'                         => $start_date,
            //                '$end_date'                           => $end_date,
            //                '$span'                               => $span,
            //                '$days'                               => $days,
            //                '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
            //                //                '$forecast_new_cards_to_study'   => $forecast_new_cards_to_study,
            //                //                '$forecast_all_answers_distinct' => $forecast_all_answers_distinct,
            //                'Manager::getQueryLog()'              => Manager::getQueryLog(),
            //            ]);
        }


        //        Common::send_error([
        //            '$start_date'                                          => $start_date,
        //            '$end_date'                                            => $end_date,
        //            '$span'                                                => $span,
        //            '$days'                                                => $days,
        //            'Manager::getQueryLog()'                               => Manager::getQueryLog(),
        //        ]);

        $cumulative_count             = 0;
        $total_time_hours             = 0;
        $total_hours_for_days_studied = 0;
        //        $graphable['total_reviews'] = count($forecast_all_answers_within_a_date);
        //        rsort($days);
        $cumulative_y_count             = 0;
        $cumulative_m_count             = 0;
        $cumulative_newly_learned_count = 0;
        $cumulative_re_learned_count    = 0;
        $cumulative_y_time              = 0;
        $cumulative_m_time              = 0;
        $cumulative_newly_learned_time  = 0;
        $cumulative_re_learned_time     = 0;
        foreach ($days as $key => $day) {
            $cumulative_y_count             += $day['y']['count'];
            $cumulative_m_count             += $day['m']['count'];
            $cumulative_newly_learned_count += $day['newly_learned']['count'];
            $cumulative_re_learned_count    += $day['re_learned']['count'];
            $cumulative_y_time              += $day['y']['hours_took'];
            $cumulative_m_time              += $day['m']['hours_took'];
            $cumulative_newly_learned_time  += $day['newly_learned']['hours_took'];
            $cumulative_re_learned_time     += $day['re_learned']['hours_took'];
            $today_time_for_today           = ($day['y']['hours_took']
                + $day['m']['hours_took']
                + $day['newly_learned']['hours_took']
                + $day['re_learned']['hours_took']);
            $total_time_hours               += $today_time_for_today;

            if (empty($cumulative_y_count)
                && empty($cumulative_m_count)
                && empty($cumulative_newly_learned_count)
                && empty($cumulative_re_learned_count)) {
                // Not learnt today
                $days_not_learnt++;
            } else {
                // He learnt today
                $total_hours_for_days_studied += $today_time_for_today;
            }

            $graphable['y_time'][]                        = $day['y']['hours_took'];
            $graphable['m_time'][]                        = $day['m']['hours_took'];
            $graphable['newly_learned_time'][]            = $day['newly_learned']['hours_took'];
            $graphable['relearned_time'][]                = $day['re_learned']['hours_took'];
            $graphable['cumulative_y_time'][]             = $cumulative_y_time;
            $graphable['cumulative_m_time'][]             = $cumulative_m_time;
            $graphable['cumulative_newly_learned_time'][] = $cumulative_newly_learned_time;
            $graphable['cumulative_relearned_time'][]     = $cumulative_re_learned_time;

            $graphable['y'][]                        = $day['y']['count'];
            $graphable['m'][]                        = $day['m']['count'];
            $graphable['newly_learned'][]            = $day['newly_learned']['count'];
            $graphable['relearned'][]                = $day['re_learned']['count'];
            $graphable['cumulative_y'][]             = $cumulative_y_count;
            $graphable['cumulative_m'][]             = $cumulative_m_count;
            $graphable['cumulative_newly_learned'][] = $cumulative_newly_learned_count;
            $graphable['cumulative_relearned'][]     = $cumulative_re_learned_count;
            $cumulative_count                        += ($day['m']['count'] + $day['y']['count']);
            $graphable['cumulative'][]               = $cumulative_count;
            //            $graphable['y_debug']['answers'][]       = $day['m']['answers'];
            //            $graphable['y_debug']['new_cards'][]     = $day['y']['new_cards'];
            //            $graphable['m_debug']['answers'][]       = $day['m']['answers'];

        }


        $total_answers_count = self::get_total_answer_count_for_user($user_id, $start_date, $end_date);
        $total_time_minutes  = $total_time_hours * 60;
        $total_time_seconds  = $total_time_hours * 3600;
        $total_days_studied  = $total_no_of_days - $days_not_learnt;
        self::add_debug('Days Studied', [
            '$no_of_days - $days_not_learnt' =>
                "($total_no_of_days - $days_not_learnt)  = $total_days_studied",
        ], true);
        self::add_debug('Days Studied', [
            '$no_of_days - $days_not_learnt' =>
                "($total_no_of_days - $days_not_learnt)  = $total_days_studied",
        ]);
        self::add_debug('Total (Hours)', [
            '$total_time_hours' => "$total_time_hours",
        ], true);
        $average_if_studied_per_day = ($total_answers_count / $total_no_of_days);
        $average                    = ($total_days_studied < 1) ? 0 : $total_answers_count / $total_days_studied;
        self::add_debug('Average for days studied', [
            '$total_answers_count / $total_days_studied' =>
                "($total_answers_count / $total_days_studied)  = $average",
        ]);
        self::add_debug('If you studied every day', [
            '($total_answers_count / $total_no_of_days)' =>
                "($total_answers_count / $total_no_of_days)  = $average_if_studied_per_day",
        ]);
        $total_answers_per_day = ($total_days_studied < 1) ? 0 : $total_answers_count / $total_days_studied;

        $average_time_for_days_studied_hours   = ($total_days_studied < 1) ? 0 : $total_hours_for_days_studied / $total_days_studied; // Correct
        $average_time_for_days_studied_minutes = ($total_days_studied < 1) ? 0 : ($total_hours_for_days_studied * 60) / $total_days_studied;
        $average_time_for_days_studied_seconds = ($total_days_studied < 1) ? 0 : ($total_hours_for_days_studied * 3600) / $total_days_studied;
        self::add_debug('Average for days studied (minutes)', [
            '($total_hours_for_days_studied * 60) / $total_days_studied' =>
                "($total_hours_for_days_studied * 60) / $total_days_studied = $average_time_for_days_studied_minutes",
        ], true);

        $average_time_if_studied_every_day_hours   = ($total_no_of_days < 1) ? 0 : $total_hours_for_days_studied / $total_no_of_days; // Correct
        $average_time_if_studied_every_day_minutes = ($total_no_of_days < 1) ? 0 : ($total_hours_for_days_studied / 60) / $total_no_of_days;
        $average_time_if_studied_every_day_seconds = ($total_no_of_days < 1) ? 0 : ($total_hours_for_days_studied / 3600) / $total_no_of_days;
        self::add_debug('If you studied every day (average) (minutes)', [
            '($total_hours_for_days_studied / 60) / $no_of_days' =>
                "($total_hours_for_days_studied / 60) / $total_no_of_days = $average_time_if_studied_every_day_minutes",
        ], true);

        //        $average_answer_time_hours   = $total_time_hours / $total_answers_count;
        //        $average_answer_time_minutes = $total_answers_count / ($total_time_hours * 60);
        //        $average_answer_time_seconds = $total_answers_count / ($total_time_hours * 3600);
        //        self::add_debug('Average answer time (minutes)', [
        //            '$total_answers_count / ($total_time_hours * 60)' =>
        //                "$total_answers_count / ($total_time_hours * 60) = $average_answer_time_minutes",
        //        ]);

        //        Common::send_error([
        //            '$start_date'            => $start_date,
        //            '$end_date'              => $end_date,
        //            '$span'                  => $span,
        //            '$days'                  => $days,
        //            '$total_time_hours'      => $total_time_hours,
        //            'Manager::getQueryLog()' => Manager::getQueryLog(),
        //        ]);

        // You are studying this no of cards per hour (Correct)
        $average_answered_cards_per_hour         = ($total_time_hours < 1) ? 0 : $total_answers_count / $total_time_hours; // cards per hour
        $average_answered_cards_per_minute       = ($total_time_hours < 1) ? 0 : $total_answers_count / ($total_time_hours * 60); // cards studied per minute
        $average_answered_cards_per_seconds_time = ($average_answered_cards_per_minute < 1) ? 0 : 60 / $average_answered_cards_per_minute;
        self::add_debug('Average answer time (minutes)', [
            '$total_answers_count / $total_time_hours' =>
                "$total_answers_count / $total_time_hours = $average_answered_cards_per_minute",
        ], true);
        self::add_debug('Average answer time (seconds)', [
            '60 / Average answer time (minutes)' =>
                "60 / $average_answered_cards_per_minute = $average_answered_cards_per_seconds_time",
        ], true);
        $days_studied_percent = ($total_days_studied / $total_no_of_days) * 100;

        //                Common::send_error([
        //                    '$start_date'                              => $start_date,
        //                    '$average_answered_cards_per_minute'       => $average_answered_cards_per_minute,
        //                    '$average_answered_cards_per_seconds_time' => $average_answered_cards_per_seconds_time,
        //                    '$average_time_for_days_studied_hours'     => $average_time_for_days_studied_hours,
        //                    '$average_time_for_days_studied_minutes'   => $average_time_for_days_studied_minutes,
        //                    '$end_date'                                => $end_date,
        //                    '$span'                                    => $span,
        //                    '$graphable'                               => $graphable,
        //                    '$total_time_hours'                        => $total_time_hours,
        //                    '$no_of_days'                              => $total_no_of_days,
        //                    '$total_answers_count'                     => $total_answers_count,
        //                    '$total_days_studied'                      => $total_days_studied,
        //                    '$days'                                    => $days,
        //                    '$__a_count'                               => $__a_count,
        //                    '$days_not_learnt'                         => $days_not_learnt,
        //                    '$forecast_all_answers_within_a_date'      => $forecast_all_answers_within_a_date,
        //                    'Manager::getQueryLog()'                   => Manager::getQueryLog(),
        //                ]);


        $formed_total_time       = $total_time_hours < 1.0 ?
            ($total_time_minutes < 1.0 ? number_format($total_time_seconds, 2).' seconds'
                : number_format($total_time_minutes, 2).' minutes') : number_format($total_time_hours, 2).' hours';
        $debug_formed_total_time = "<b>Average for days studied: </b>";

        $graphable['total_days']                                = $total_no_of_days;
        $graphable['days_not_studied_count']                    = $days_not_learnt;
        $graphable['days_studied_count']                        = $total_days_studied;
        $graphable['days_studied_percent']                      = number_format($days_studied_percent, 2);
        $graphable['total_reviews']                             = $total_answers_count;
        $graphable['average_if_studied_per_day']                = number_format($average_if_studied_per_day, 2);
        $graphable['average']                                   = number_format($average, 2);
        $graphable['total_hours']                               = number_format(($total_time_hours), 2);
        $graphable['total_minutes']                             = number_format(($total_time_minutes), 2);
        $graphable['total_seconds']                             = number_format($total_time_seconds, 2);
        $graphable['formed_total_time']                         = $formed_total_time;
        $graphable['total_reviews_time']                        = $total_answers_count;
        $graphable['average_time_for_days_studied_hours']       = number_format($average_time_for_days_studied_hours, 2);
        $graphable['average_time_for_days_studied_minutes']     = number_format($average_time_for_days_studied_minutes, 2);
        $graphable['average_time_for_days_studied_seconds']     = number_format($average_time_for_days_studied_seconds, 2);
        $graphable['average_time_if_studied_every_day_hours']   = number_format($average_time_if_studied_every_day_hours, 2);
        $graphable['average_time_if_studied_every_day_minutes'] = number_format(($average_time_if_studied_every_day_hours * 60), 2);
        $graphable['average_time_if_studied_every_day_seconds'] = number_format(($average_time_if_studied_every_day_hours * 3600), 2);
        //        $graphable['average_answer_time_hours']                 = number_format($average_answer_time_hours, 2);
        //        $graphable['average_answer_time_minutes']               = number_format($average_answer_time_minutes, 2);
        //        $graphable['average_answer_time_seconds']               = number_format($average_answer_time_seconds, 2);
        $graphable['average_answer_cards_per_hour']           = number_format(($average_answered_cards_per_hour), 2);
        $graphable['average_answer_cards_per_minute']         = number_format(($average_answered_cards_per_minute), 2);
        $graphable['average_answered_cards_per_seconds_time'] = number_format(($average_answered_cards_per_seconds_time), 2);

        $measure_end_time                       = microtime(true);
        $measure_execution_time                 = ($measure_end_time - $measure_start_time);
        $graphable['zz_measure_execution_time'] = $measure_execution_time;
        $graphable['zz_debug']                  = self::$sp_debug;

        //        Common::send_error([
        //            '$start_date'                         => $start_date,
        //            '$end_date'                           => $end_date,
        //            '$span'                               => $span,
        //            '$graphable'                          => $graphable,
        //            '$total_time_hours'                   => $total_time_hours,
        //            'tttotal_answer_count_for_user'         => self::get_total_answer_count_for_user($user_id),
        //            '$no_of_days'                         => $total_no_of_days,
        //            '$total_answers_count'                => $total_answers_count,
        //            '$total_days_studied'                 => $total_days_studied,
        //            '$days'                               => $days,
        //            '$__a_count'                          => $__a_count,
        //            '$days_not_learnt'                    => $days_not_learnt,
        //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //            'Manager::getQueryLog()'              => Manager::getQueryLog(),
        //        ]);

        return [
            'graphable' => $graphable,
        ];

    }

    public static function get_user_card_review_count_and_time2($user_id, $span) {
        $measure_start_time = microtime(true);
        //        $matured_cards = self::get_user_matured_card_ids($user_id);
        $graphable                         = [
            'heading'                       => [],
            'cumulative'                    => [],
            'y'                             => [],
            'm'                             => [],
            'newly_learned'                 => [],
            'relearned'                     => [],
            'y_time'                        => [],
            'm_time'                        => [],
            'newly_learned_time'            => [],
            'relearned_time'                => [],
            'cumulative_y'                  => [],
            'cumulative_m'                  => [],
            'cumulative_newly_learned'      => [],
            'cumulative_relearned'          => [],
            'cumulative_y_time'             => [],
            'cumulative_m_time'             => [],
            'cumulative_newly_learned_time' => [],
            'cumulative_relearned_time'     => [],
            'y_debug'                       => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'm_debug'                       => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'newly_learned_debug'           => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'relearned_debug'               => [
                'answers'   => [],
                'new_cards' => [],
            ],
            'm_cumulative'                  => 0,
            'y_cumulative'                  => 0,
            'newly_learned_cumulative'      => 0,
            're_learned_cumulative'         => 0,
            'm_cumulative_time'             => 0,
            'y_cumulative_time'             => 0,
            'newly_learned_cumulative_time' => 0,
            're_learned_cumulative_time'    => 0,
            'total_reviews_time'            => 0,
            'average'                       => 0,
            'average_if_studied_per_day'    => 0,
            'total_days'                    => 0,
            'total_hours'                   => 0,
            'total_minutes'                 => 0,
            'total_seconds'                 => 0,
            'total_days_time'               => 0,
            'days_studied_count'            => 0,
            'days_studied_time'             => 0,
            'days_not_studied__time'        => 0,
            'days_studied_percent_time'     => 0,
        ];
        $matured_day_no                    = get_mature_card_days();
        $end_date                          = null;
        $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today($user_id);
        //        $start_date                   = $user_timezone_early_morning_today;
        $end_date = $user_timezone_early_morning_today;
        $_date    = new DateTime($user_timezone_early_morning_today);
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

        $total_no_of_days = (int) $_end_date->diff($_start_date)->format("%a"); //3
        $days             = [];
        $__a_count        = 0 - $total_no_of_days + 1;
        for ($_a = 0; $_a < $total_no_of_days; $_a++) {
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
                    'count'           => 0,
                    'seconds_took'    => 0,
                    'minutes_took'    => 0,
                    'hours_took'      => 0,
                    'cumulative'      => 0,
                    'cumulative_time' => 0,
                    'answers'         => [],
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
        $days_not_learnt           = 0;
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
            $seconds_took     = $answer->time_diff_second_spent;
            $minutes_took     = $seconds_took / 60;
            $hours_took       = $seconds_took / 3600;

            if ($is_matured) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_m += $hours_took;
                $days[$day_diff_today]['m']['count']++;
                $days[$day_diff_today]['m']['seconds_took'] += $seconds_took;
                $days[$day_diff_today]['m']['minutes_took'] += $minutes_took;
                $days[$day_diff_today]['m']['hours_took']   += $hours_took;
                $days[$day_diff_today]['m']['cumulative']   += $_cumulative_m;
                $days[$day_diff_today]['m']['answers'][]    = $answer;
            }
            if ($is_young) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_y += $hours_took;
                $days[$day_diff_today]['y']['count']++;
                $days[$day_diff_today]['y']['seconds_took'] += $seconds_took;
                $days[$day_diff_today]['y']['minutes_took'] += $minutes_took;
                $days[$day_diff_today]['y']['hours_took']   += $hours_took;
                $days[$day_diff_today]['y']['cumulative']   += $_cumulative_y;
                $days[$day_diff_today]['y']['answers'][]    = $answer;
            }
            if ($is_newly_learned) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_newly_learned += $hours_took;
                $days[$day_diff_today]['newly_learned']['count']++;
                $days[$day_diff_today]['newly_learned']['seconds_took'] += $seconds_took;
                $days[$day_diff_today]['newly_learned']['minutes_took'] += $minutes_took;
                $days[$day_diff_today]['newly_learned']['hours_took']   += $hours_took;
                $days[$day_diff_today]['newly_learned']['cumulative']   += $_cumulative_newly_learned;
                $days[$day_diff_today]['newly_learned']['answers'][]    = $answer;
            }
            if ($is_relearned) {
                //todo ignore the max no of on_hold or revise he needs to answer each day. So don't roll over remaining cards
                $_cumulative_re_learned += $hours_took;
                $days[$day_diff_today]['re_learned']['count']++;
                $days[$day_diff_today]['re_learned']['seconds_took'] += $seconds_took;
                $days[$day_diff_today]['re_learned']['minutes_took'] += $minutes_took;
                $days[$day_diff_today]['re_learned']['hours_took']   += $hours_took;
                $days[$day_diff_today]['re_learned']['cumulative']   += $_cumulative_re_learned;
                $days[$day_diff_today]['re_learned']['answers'][]    = $answer;
            }

            //            Common::send_error([
            //                '$no_to_revise'                       => $no_to_revise,
            //                '$answer'                             => $answer,
            //                '$no_on_hold'                         => $no_on_hold,
            //                '$revise_all'                         => $revise_all,
            //                '$study_all_on_hold'                  => $study_all_on_hold,
            //                '$day_dif'                            => $day_dif,
            //                '$start_date'                         => $start_date,
            //                '$end_date'                           => $end_date,
            //                '$span'                               => $span,
            //                '$days'                               => $days,
            //                '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
            //                //                '$forecast_new_cards_to_study'   => $forecast_new_cards_to_study,
            //                //                '$forecast_all_answers_distinct' => $forecast_all_answers_distinct,
            //                'Manager::getQueryLog()'              => Manager::getQueryLog(),
            //            ]);
        }


        //        Common::send_error([
        //            '$start_date'                                          => $start_date,
        //            '$end_date'                                            => $end_date,
        //            '$span'                                                => $span,
        //            '$days'                                                => $days,
        //            'Manager::getQueryLog()'                               => Manager::getQueryLog(),
        //        ]);

        $cumulative_count             = 0;
        $total_time_hours             = 0;
        $total_hours_for_days_studied = 0;
        //        $graphable['total_reviews'] = count($forecast_all_answers_within_a_date);
        //        rsort($days);
        $cumulative_y_count             = 0;
        $cumulative_m_count             = 0;
        $cumulative_newly_learned_count = 0;
        $cumulative_re_learned_count    = 0;
        $cumulative_y_time              = 0;
        $cumulative_m_time              = 0;
        $cumulative_newly_learned_time  = 0;
        $cumulative_re_learned_time     = 0;
        foreach ($days as $key => $day) {
            $cumulative_y_count             += $day['y']['count'];
            $cumulative_m_count             += $day['m']['count'];
            $cumulative_newly_learned_count += $day['newly_learned']['count'];
            $cumulative_re_learned_count    += $day['re_learned']['count'];
            $cumulative_y_time              += $day['y']['hours_took'];
            $cumulative_m_time              += $day['m']['hours_took'];
            $cumulative_newly_learned_time  += $day['newly_learned']['hours_took'];
            $cumulative_re_learned_time     += $day['re_learned']['hours_took'];
            $today_time_for_today           = ($day['y']['hours_took']
                + $day['m']['hours_took']
                + $day['newly_learned']['hours_took']
                + $day['re_learned']['hours_took']);
            $total_time_hours               += $today_time_for_today;

            if (empty($cumulative_y_count)
                && empty($cumulative_m_count)
                && empty($cumulative_newly_learned_count)
                && empty($cumulative_re_learned_count)) {
                // Not learnt today
                $days_not_learnt++;
            } else {
                // He learnt today
                $total_hours_for_days_studied += $today_time_for_today;
            }

            $graphable['y_time'][]                        = $day['y']['hours_took'];
            $graphable['m_time'][]                        = $day['m']['hours_took'];
            $graphable['newly_learned_time'][]            = $day['newly_learned']['hours_took'];
            $graphable['relearned_time'][]                = $day['re_learned']['hours_took'];
            $graphable['cumulative_y_time'][]             = $cumulative_y_time;
            $graphable['cumulative_m_time'][]             = $cumulative_m_time;
            $graphable['cumulative_newly_learned_time'][] = $cumulative_newly_learned_time;
            $graphable['cumulative_relearned_time'][]     = $cumulative_re_learned_time;

            $graphable['y'][]                        = $day['y']['count'];
            $graphable['m'][]                        = $day['m']['count'];
            $graphable['newly_learned'][]            = $day['newly_learned']['count'];
            $graphable['relearned'][]                = $day['re_learned']['count'];
            $graphable['cumulative_y'][]             = $cumulative_y_count;
            $graphable['cumulative_m'][]             = $cumulative_m_count;
            $graphable['cumulative_newly_learned'][] = $cumulative_newly_learned_count;
            $graphable['cumulative_relearned'][]     = $cumulative_re_learned_count;
            $cumulative_count                        += ($day['m']['count'] + $day['y']['count']);
            $graphable['cumulative'][]               = $cumulative_count;
            //            $graphable['y_debug']['answers'][]       = $day['m']['answers'];
            //            $graphable['y_debug']['new_cards'][]     = $day['y']['new_cards'];
            //            $graphable['m_debug']['answers'][]       = $day['m']['answers'];

        }


        $total_answers_count = self::get_total_answer_count_for_user($user_id, $start_date, $end_date);
        $total_time_minutes  = $total_time_hours * 60;
        $total_time_seconds  = $total_time_hours * 3600;
        $total_days_studied  = $total_no_of_days - $days_not_learnt;
        self::add_debug('Days Studied', [
            '$no_of_days - $days_not_learnt' =>
                "($total_no_of_days - $days_not_learnt)  = $total_days_studied",
        ], true);
        self::add_debug('Days Studied', [
            '$no_of_days - $days_not_learnt' =>
                "($total_no_of_days - $days_not_learnt)  = $total_days_studied",
        ]);
        self::add_debug('Total (Hours)', [
            '$total_time_hours' => "$total_time_hours",
        ], true);
        $average_if_studied_per_day = ($total_answers_count / $total_no_of_days);
        $average                    = ($total_days_studied < 1) ? 0 : $total_answers_count / $total_days_studied;
        self::add_debug('Average for days studied', [
            '$total_answers_count / $total_days_studied' =>
                "($total_answers_count / $total_days_studied)  = $average",
        ]);
        self::add_debug('If you studied every day', [
            '($total_answers_count / $total_no_of_days)' =>
                "($total_answers_count / $total_no_of_days)  = $average_if_studied_per_day",
        ]);
        $total_answers_per_day = ($total_days_studied < 1) ? 0 : $total_answers_count / $total_days_studied;

        $average_time_for_days_studied_hours   = ($total_days_studied < 1) ? 0 : $total_hours_for_days_studied / $total_days_studied; // Correct
        $average_time_for_days_studied_minutes = ($total_days_studied < 1) ? 0 : ($total_hours_for_days_studied * 60) / $total_days_studied;
        $average_time_for_days_studied_seconds = ($total_days_studied < 1) ? 0 : ($total_hours_for_days_studied * 3600) / $total_days_studied;
        self::add_debug('Average for days studied (minutes)', [
            '($total_hours_for_days_studied * 60) / $total_days_studied' =>
                "($total_hours_for_days_studied * 60) / $total_days_studied = $average_time_for_days_studied_minutes",
        ], true);

        $average_time_if_studied_every_day_hours   = ($total_no_of_days < 1) ? 0 : $total_hours_for_days_studied / $total_no_of_days; // Correct
        $average_time_if_studied_every_day_minutes = ($total_no_of_days < 1) ? 0 : ($total_hours_for_days_studied / 60) / $total_no_of_days;
        $average_time_if_studied_every_day_seconds = ($total_no_of_days < 1) ? 0 : ($total_hours_for_days_studied / 3600) / $total_no_of_days;
        self::add_debug('If you studied every day (average) (minutes)', [
            '($total_hours_for_days_studied / 60) / $no_of_days' =>
                "($total_hours_for_days_studied / 60) / $total_no_of_days = $average_time_if_studied_every_day_minutes",
        ], true);

        //        $average_answer_time_hours   = $total_time_hours / $total_answers_count;
        //        $average_answer_time_minutes = $total_answers_count / ($total_time_hours * 60);
        //        $average_answer_time_seconds = $total_answers_count / ($total_time_hours * 3600);
        //        self::add_debug('Average answer time (minutes)', [
        //            '$total_answers_count / ($total_time_hours * 60)' =>
        //                "$total_answers_count / ($total_time_hours * 60) = $average_answer_time_minutes",
        //        ]);

        //        Common::send_error([
        //            '$start_date'            => $start_date,
        //            '$end_date'              => $end_date,
        //            '$span'                  => $span,
        //            '$days'                  => $days,
        //            '$total_time_hours'      => $total_time_hours,
        //            'Manager::getQueryLog()' => Manager::getQueryLog(),
        //        ]);

        // You are studying this no of cards per hour (Correct)
        $average_answered_cards_per_hour         = ($total_time_hours < 1) ? 0 : $total_answers_count / $total_time_hours; // cards per hour
        $average_answered_cards_per_minute       = ($total_time_hours < 1) ? 0 : $total_answers_count / ($total_time_hours * 60); // cards studied per minute
        $average_answered_cards_per_seconds_time = ($average_answered_cards_per_minute < 1) ? 0 : 60 / $average_answered_cards_per_minute;
        self::add_debug('Average answer time (minutes)', [
            '$total_answers_count / $total_time_hours' =>
                "$total_answers_count / $total_time_hours = $average_answered_cards_per_minute",
        ], true);
        self::add_debug('Average answer time (seconds)', [
            '60 / Average answer time (minutes)' =>
                "60 / $average_answered_cards_per_minute = $average_answered_cards_per_seconds_time",
        ], true);
        $days_studied_percent = ($total_days_studied / $total_no_of_days) * 100;

        //        Common::send_error([
        //            '$start_date'                              => $start_date,
        //            '$average_answer_time_minutes'             => $average_answer_time_minutes,
        //            '$average_answer_time_seconds'             => $average_answer_time_seconds,
        //            '$average_answered_cards_per_minute'       => $average_answered_cards_per_minute,
        //            '$average_answered_cards_per_seconds'      => $average_answered_cards_per_seconds,
        //            '$average_answered_cards_per_seconds_time' => $average_answered_cards_per_seconds_time,
        //            '$average_answer_time_hours'               => $average_answer_time_hours,
        //            '$average_time_for_days_studied_hours'     => $average_time_for_days_studied_hours,
        //            '$average_time_for_days_studied_minutes'   => $average_time_for_days_studied_minutes,
        //            '$end_date'                                => $end_date,
        //            '$span'                                    => $span,
        //            '$graphable'                               => $graphable,
        //            '$total_time_hours'                        => $total_time_hours,
        //            '$no_of_days'                              => $total_no_of_days,
        //            '$total_answers_count'                     => $total_answers_count,
        //            '$total_days_studied'                      => $total_days_studied,
        //            '$days'                                    => $days,
        //            '$__a_count'                               => $__a_count,
        //            '$days_not_learnt'                         => $days_not_learnt,
        //            '$forecast_all_answers_within_a_date'      => $forecast_all_answers_within_a_date,
        //            'Manager::getQueryLog()'                   => Manager::getQueryLog(),
        //        ]);


        $formed_total_time       = $total_time_hours < 1.0 ?
            ($total_time_minutes < 1.0 ? number_format($total_time_seconds, 2).' seconds'
                : number_format($total_time_minutes, 2).' minutes') : number_format($total_time_hours, 2).' hours';
        $debug_formed_total_time = "<b>Average for days studied: </b>";

        $graphable['total_days']                                = $total_no_of_days;
        $graphable['days_not_studied_count']                    = $days_not_learnt;
        $graphable['days_studied_count']                        = $total_days_studied;
        $graphable['days_studied_percent']                      = number_format($days_studied_percent, 2);
        $graphable['total_reviews']                             = $total_answers_count;
        $graphable['average_if_studied_per_day']                = number_format($average_if_studied_per_day, 2);
        $graphable['average']                                   = number_format($average, 2);
        $graphable['total_hours']                               = number_format(($total_time_hours), 2);
        $graphable['total_minutes']                             = number_format(($total_time_minutes), 2);
        $graphable['total_seconds']                             = number_format($total_time_seconds, 2);
        $graphable['formed_total_time']                         = $formed_total_time;
        $graphable['total_reviews_time']                        = $total_answers_count;
        $graphable['average_time_for_days_studied_hours']       = number_format($average_time_for_days_studied_hours, 2);
        $graphable['average_time_for_days_studied_minutes']     = number_format($average_time_for_days_studied_minutes, 2);
        $graphable['average_time_for_days_studied_seconds']     = number_format($average_time_for_days_studied_seconds, 2);
        $graphable['average_time_if_studied_every_day_hours']   = number_format($average_time_if_studied_every_day_hours, 2);
        $graphable['average_time_if_studied_every_day_minutes'] = number_format(($average_time_if_studied_every_day_hours * 60), 2);
        $graphable['average_time_if_studied_every_day_seconds'] = number_format(($average_time_if_studied_every_day_hours * 3600), 2);
        //        $graphable['average_answer_time_hours']                 = number_format($average_answer_time_hours, 2);
        //        $graphable['average_answer_time_minutes']               = number_format($average_answer_time_minutes, 2);
        //        $graphable['average_answer_time_seconds']               = number_format($average_answer_time_seconds, 2);
        $graphable['average_answer_cards_per_hour']           = number_format(($average_answered_cards_per_hour), 2);
        $graphable['average_answer_cards_per_minute']         = number_format(($average_answered_cards_per_minute), 2);
        $graphable['average_answered_cards_per_seconds_time'] = number_format(($average_answered_cards_per_seconds_time), 2);

        $measure_end_time                       = microtime(true);
        $measure_execution_time                 = ($measure_end_time - $measure_start_time);
        $graphable['zz_measure_execution_time'] = $measure_execution_time;
        $graphable['zz_debug']                  = self::$sp_debug;

        //        Common::send_error([
        //            '$start_date'                         => $start_date,
        //            '$end_date'                           => $end_date,
        //            '$span'                               => $span,
        //            'total_answer_count_for_user'         => self::get_total_answer_count_for_user($user_id),
        //            '$graphable'                          => $graphable,
        //            '$total_time_hours'                   => $total_time_hours,
        //            '$no_of_days'                         => $total_no_of_days,
        //            '$total_answers_count'                => $total_answers_count,
        //            '$total_days_studied'                 => $total_days_studied,
        //            '$days'                               => $days,
        //            '$__a_count'                          => $__a_count,
        //            '$days_not_learnt'                    => $days_not_learnt,
        //            '$forecast_all_answers_within_a_date' => $forecast_all_answers_within_a_date,
        //            'Manager::getQueryLog()'              => Manager::getQueryLog(),
        //        ]);

        return [
            'graphable' => $graphable,
        ];

    }

    /**
     * Get cards on hold for forecast
     * @param $args
     * @return array[]
     */

    public static function get_forecast_cards_on_hold($args) {
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

    public static function get_forecast_cards_to_revise($args) {
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
    public static function get_user_matured_card_ids($user_id) {
        $mature_card_days = get_mature_card_days();
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

    public static function get_user_cards_____($study_id, $user_id) {

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

            $study             = Study::with('tags', 'tags_excluded')->find($study_id);
            $deck_id           = $study->deck_id;
            $tags              = [];
            $tags_excluded = [];
            $add_all_tags      = $study->all_tags;
            $study_all_new     = $study->study_all_new;
            $revise_all        = $study->revise_all;
            $study_all_on_hold = $study->study_all_on_hold;
            $no_of_new         = $study->no_of_new;
            $no_on_hold        = $study->no_on_hold;

            if (!$add_all_tags) {
                $tags = $study->tags->pluck('id');
                $tags_excluded = $study->tagsExcluded->pluck('id');
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
                $cards_query = $cards_query->whereNotIn('t.id', $tags_excluded);
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


//            return [
//                'cards' => $cards,
//            ];

        } catch (ItemNotFoundException $e) {
            //todo handle later
            return [
                'cards' => [],
            ];
        }


    }

    public static function get_user_cards_on_hold($study_id, $user_id, $particular_date = null) {

        try {
            $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today($user_id);

            $study        = Study::with('tags', 'tags_excluded')->findOrFail($study_id);
            $deck_id      = $study->deck_id;
            $tags         = [];
            $tags_excluded = [];
            $add_all_tags = $study->all_tags;
            $revise_all   = $study->revise_all;
            $no_to_revise = $study->no_to_revise;

            if (!$add_all_tags) {
                $tags = $study->tags->pluck('id');
                $tags_excluded = $study->tagsExcluded->pluck('id');
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
                ->where('created_at', '>', $user_timezone_early_morning_today)
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
            //					'$user_timezone_early_morning_today'        => $user_timezone_early_morning_today,
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
                $cards_query = $cards_query->whereNotIn('t.id', $tags_excluded);
            }

            /*** Revise a few cards? ***/
            if (!$revise_all) {
                $cards_query = $cards_query->limit($no_of_new_remaining_to_revise_today);
            }

            /*** Return only those answered before (Not in cards revised today) and grade = hold ***/
            $cards_query = $cards_query
                ->whereIn('c.id', function ($q) use (
                    $user_timezone_early_morning_today,
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
                        ->where('next_due_at', '<=', $user_timezone_early_morning_today)
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

    public static function get_user_cards_to_revise($study_id, $user_id) {

        try {
            $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today($user_id);

            $study        = Study::with('tags', 'tags_excluded')->findOrFail($study_id);
            $deck_id      = $study->deck_id;
            $tags         = [];
            $tags_excluded = [];
            $add_all_tags = $study->all_tags;
            $revise_all   = $study->revise_all;
            $no_to_revise = $study->no_to_revise;

            if (!$add_all_tags) {
                $tags = $study->tags->pluck('id');
                $tags_excluded = $study->tagsExcluded->pluck('id');
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
                ->where('created_at', '>', $user_timezone_early_morning_today)
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
                $cards_query = $cards_query->whereNotIn('t.id', $tags_excluded);
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
            //					->whereNotIn( 'c.id', function ( $q ) use ( $user_timezone_early_morning_today ) {
            //						$q->select( 'card_id' )->from( SP_TABLE_ANSWERED )
            //							->where( 'grade', '!=', 'again' );
            //					} );

            /*** Return only those answered before (Not in cards revised today) ***/
            $cards_query = $cards_query
                ->whereIn('c.id', function ($q) use (
                    $user_timezone_early_morning_today,
                    $card_ids_revised_today,
                    $study_id
                ) {
                    $q->select('card_id')->from(SP_TABLE_ANSWERED)
                        ->whereNotIn('card_id', $card_ids_revised_today)
                        ->whereNotIn('grade', ['hold'])
                        ->where('study_id', $study_id)
                        ->where('next_due_at', '<=', $user_timezone_early_morning_today)
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

    public static function get_user_cards_new($study_id, $user_id) {

        try {
            $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today($user_id);
            $user_timezone_midnight_today      = get_user_timezone_date_midnight_today($user_id);
            //            Common::send_error( [
            //                '$study_id'                   => $study_id,
            //            ] );
            $study         = Study::with('tags', 'tags_excluded')->findOrFail($study_id);
            $deck_id       = $study->deck_id;
            $tags          = [];
            $tags_excluded = [];
            $add_all_tags  = $study->all_tags;
            $study_all_new = $study->study_all_new;
            $no_of_new     = $study->no_of_new;

            if (!$add_all_tags) {
                $tags          = $study->tags->pluck('id');
                $tags_excluded = $study->tagsExcluded->pluck('id');
            }


            /*** Get all new cards answered today "Only those answered once and today are truly new" ***/
            $query_new_answered_today     = Answered::where('study_id', '=', $study_id)
                ->where('created_at', '>', $user_timezone_early_morning_today)
                ->where('grade', '!=', 'again')
                ->where('answered_as_new', '=', true);
            $new_card_ids_answered_today  = $query_new_answered_today->pluck('card_id');
            $count_new_studied_today      = $new_card_ids_answered_today->count();
            $no_of_new_remaining_to_study = $no_of_new - $count_new_studied_today;

            //            Common::send_error([
            //                'sql'                           => $query_new_answered_today->toSql(),
            //                'getBindings'                   => $query_new_answered_today->getBindings(),
            //                '$count_new_studied_today'      => $count_new_studied_today,
            //                '$no_of_new_remaining_to_study' => $no_of_new_remaining_to_study,
            //                '$new_card_ids_answered_today'  => $new_card_ids_answered_today,
            //                '$tags_excluded'                => $tags_excluded,
            //                '$study'                        => $study,
            //            ]);

            /*** Prepare basic query ***/
            $cards_query = Manager::table(SP_TABLE_CARDS.' as c')
                ->leftJoin(SP_TABLE_CARD_GROUPS.' as cg', 'cg.id', '=', 'c.card_group_id')
                ->leftJoin(SP_TABLE_DECKS.' as d', 'd.id', '=', 'cg.deck_id')
                ->leftJoin(SP_TABLE_TAGGABLES.' as tg', 'tg.taggable_id', '=', 'cg.id')
                ->leftJoin(SP_TABLE_TAGGABLES_EXCLUDED.' as tgex', 'tgex.taggable_id', '=', 'cg.id')
                ->leftJoin(SP_TABLE_TAGS.' as t', 't.id', '=', 'tg.tag_id')
                ->where('tg.taggable_type', '=', CardGroup::class)
                ->where(function ($query) use ($user_timezone_midnight_today) {
                    $query
                        ->where(function ($q) use ($user_timezone_midnight_today) {
                            $q->whereNotNull('cg.scheduled_at')
                                ->where('cg.scheduled_at', '<=', $user_timezone_midnight_today);
                        })
                        ->orWhere(function ($q) {
                            $q->whereNull('cg.scheduled_at');
                        });
                })
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
                $cards_query = $cards_query->whereNotIn('t.id', $tags_excluded);
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
                    $user_timezone_early_morning_today,
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
                        ->where('aaa.created_at', '>', $user_timezone_early_morning_today)
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
//                '$card_ids'                          => $card_ids,
//                '$user_timezone_early_morning_today' => $user_timezone_early_morning_today,
//                '$all_cards->toSql()'                => $all_cards->toSql(),
//                '$all_cards->getBindings()'          => $all_cards->getBindings(),
//                '$all_cards->get()'                  => $all_cards->get(),
//                '$cards_query->toSql()'              => $cards_query->toSql(),
//                '$cards_query->getBindings()'        => $cards_query->getBindings(),
//                '$cards_query->get('                 => $cards_query->get(),
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
            //            Common::send_error([
            //                __METHOD__,
            //                '$e' => $e->getMessage(),
            //            ]);
            //todo handle later
            return [
                'cards' => [],
            ];
        } catch (ModelNotFoundException $e) {
            //            Common::send_error([
            //                __METHOD__,
            //                '$e' => $e->getMessage(),
            //            ]);
            //todo handle later
            return [
                'cards' => [],
            ];
        }


    }

    public static function get_user_cards_to_study($study_id, $user_id) {
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

    public static function get_study_due_summary($study_id, $user_id) {
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
    public static function get_all_new_cards_in_user_studies($user_id) {
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
                },
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

    public static function get_all_card_ids_studied_today($user_id) {
        $query = Answered
            ::with([
                'study' => function ($query) use ($user_id) {
                    $query->where('user_id', '=', $user_id);
                },
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

    private static function get_total_answer_count_for_user($user_id, $start_date, $end_date) {
        $count = Manager::table(SP_TABLE_ANSWERED.' as a')
            ->join(SP_TABLE_STUDY.' as s', 's.id', '=', 'a.study_id')
            ->join(SP_TABLE_USERS.' as u', 'u.id', '=', 's.user_id')
            ->where('s.user_id', '=', $user_id)
            ->whereBetween('a.created_at', [$start_date, $end_date])
            ->count();
        return $count;
    }

    private static function get_user_oldest_answer($user_id) {
        $answer = Manager::table(SP_TABLE_ANSWERED.' as a')
            ->join(SP_TABLE_STUDY.' as s', 's.id', '=', 'a.study_id')
            ->join(SP_TABLE_USERS.' as u', 'u.id', '=', 's.user_id')
            ->where('s.user_id', '=', $user_id)
            ->orderBy('a.created_at', 'asc')
            ->limit(1)
            ->get()->first();
        return $answer;
    }

    private static function get_user_newest_answer($user_id) {
        $answer = Manager::table(SP_TABLE_ANSWERED.' as a')
            ->join(SP_TABLE_STUDY.' as s', 's.id', '=', 'a.study_id')
            ->join(SP_TABLE_USERS.' as u', 'u.id', '=', 's.user_id')
            ->where('s.user_id', '=', $user_id)
            ->orderBy('a.created_at', 'desc')
            ->limit(1)
            ->get()->first();
        return $answer;
    }

}