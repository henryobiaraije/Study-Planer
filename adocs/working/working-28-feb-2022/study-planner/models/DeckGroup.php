<?php

namespace Model;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use StudyPlanner\Libs\Common;
use StudyPlanner\Models\Tag;
use function StudyPlanner\get_card_group_background_image;
use function StudyPlanner\get_uncategorized_deck_group_id;
use function StudyPlanner\get_uncategorized_deck_id;

class DeckGroup extends Model {
    protected $table = SP_DB_PREFIX.'deck_groups';

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = ['name'];
    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function tags() {
        return $this->morphToMany(Tag::class, 'taggable', SP_TABLE_TAGGABLES);
    }

    public function decks() {
        return $this->hasMany(Deck::class, 'deck_group_id');
    }

    public function decks_with_study($user_id) {
        return $this->hasMany(Deck::class, 'deck_group_id');
    }

    public static function get_deck_groups($args): array {
        $default = [
            'search'       => '',
            'page'         => 1,
            'per_page'     => 5,
            'with_trashed' => false,
            'only_trashed' => false,
        ];
        $args    = wp_parse_args($args, $default);
        if ($args['with_trashed']) {
            $deck_groups = DeckGroup::with('tags')->withoutTrashed();
        } elseif ($args['only_trashed']) {
            $deck_groups = DeckGroup::with('tags')->onlyTrashed();
        } else {
            $deck_groups = DeckGroup::with('tags');
        }
        $deck_groups = $deck_groups
            ->where('name', 'like', "%{$args['search']}%");

        $total       = $deck_groups->count();
        $offset      = ($args['page'] - 1);
        $deck_groups = $deck_groups->offset($offset)
            ->limit($args['per_page'])
            ->orderByDesc('id')->get();

        //			Common::send_error( [
        //				'ajax_admin_load_deck_group',
        //				'$args'        => $args,
        //				'$deck_groups' => $deck_groups->toSql(),
        //				'getQuery'     => $deck_groups->getQuery(),
        //			] );

        return [
            'total'       => $total,
            'deck_groups' => $deck_groups->all(),
        ];
    }

    public static function get_deck_groups_front_end($args): array {
        $user_id                     = get_current_user_id();
        $default                     = [
            'search'       => '',
            'page'         => 1,
            'per_page'     => 5,
            'with_trashed' => false,
            'only_trashed' => false,
        ];
        $args                        = wp_parse_args($args, $default);
        $uncategorized_deck_group_id = get_uncategorized_deck_group_id();
        $uncategorized_deck_id       = get_uncategorized_deck_id();
        $deck_groups                 = DeckGroup
            ::with([
                'tags',
                'decks'             => function ($query) use ($uncategorized_deck_id) {
                    $query->withCount('card_groups')
                        ->where('id', '!=', $uncategorized_deck_id);
                },
                'decks.card_groups' => function ($query) {
                    $query->withCount('cards');
                },
                'decks.studies'     => function ($q) use ($user_id) {
                    $q->where('user_id', '=', $user_id);
                },
            ])
            ->where('name', 'like', "%{$args['search']}%")
            ->where('id', '!=', $uncategorized_deck_group_id)
            ->withCount([
                'decks',
            ]);

        $total       = $deck_groups->count();
        $offset      = ($args['page'] - 1);
        $deck_groups = $deck_groups->offset($offset)
            ->limit($args['per_page'])
            ->orderByDesc('id');
//        Common::send_error([
//            __METHOD__,
//            'query' => $deck_groups->toSql(),
//        ]);
        $deck_groups = $deck_groups->get();
//        Common::send_error([
//            'ajax_admin_load_deck_group',
//            '$args'        => $args,
//            '$deck_groups' => $deck_groups,
//        ]);
        $all_deck_groups = [];
        foreach ($deck_groups as $dg) {
            $decks          = $dg->decks;
            $dg_due_summary = [
                'new'              => 0,
                'revision'         => 0,
                'previously_false' => 0,
                'total'            => 0,
            ];
            foreach ($decks as $_deck) {
                //                $_study              = $_deck->studies->first();
                $_study              = Study
                    ::where('deck_id', '=', $_deck->id)
                    ->where('user_id', '=', $user_id)
                    ->get()
                    ->first();
                $_deck->_study_first = $_study;
                if (empty($_study)) {
                    $_deck->due_summary = Study::get_study_due_summary(0, $user_id);
                } else {
                    $_deck->due_summary = Study::get_study_due_summary($_study->id, $user_id);
                }
                $deck_total_due_cards               = $_deck->due_summary['new']
                    + $_deck->due_summary['revision']
                    + $_deck->due_summary['previously_false'];
                $_deck->deck_total_due_cards        = $deck_total_due_cards;
                $dg_due_summary['total']            += $deck_total_due_cards;
                $dg_due_summary['new']              = $dg_due_summary['new'] + $_deck->due_summary['new'];
                $dg_due_summary['revision']         = $dg_due_summary['revision'] + $_deck->due_summary['revision'];
                $dg_due_summary['previously_false'] = $dg_due_summary['previously_false'] + $_deck->due_summary['previously_false'];
                $card_counts                        = $_deck->card_groups->pluck('cards_count');
                $_deck->card_count                  = array_sum($card_counts->toArray());
                if ($_deck->id === 9) {
                    //                    Common::send_error([
                    //                        'ajax_admin_load_deck_group',
                    //                        '$user_id'         => $user_id,
                    //                        '$args'            => $args,
                    //                        'cg'               => $card_counts,
                    ////                        'cg sum'           => array_sum($card_counts->toArray()),
                    //                        '$all_deck_groups' => $all_deck_groups,
                    //                        '$deck_groups'     => $deck_groups,
                    //                        '$_deck'           => $_deck,
                    //                        //				'$deck_groups'     => $deck_groups->toSql(),
                    //                        //				'getQuery'         => $deck_groups->getQuery(),
                    //                    ]);
                }
            }


            $decks_arr = $decks->all();
            usort($decks_arr, function ($a, $b) {
                if ($a["deck_total_due_cards"] === $b["deck_total_due_cards"]) {
                    return 0;
                }
                return ($a["deck_total_due_cards"] > $b["deck_total_due_cards"]) ? -1 : 1;
            });
            //            if (5 === $dg->id) {
            //                Common::send_error([
            //                    'ajax_admin_load_deck_group',
            //                    '$user_id'         => $user_id,
            //                    'gettype'          => gettype($decks),
            //                    '$args'            => $args,
            //                    '$all_deck_groups' => $all_deck_groups,
            //                    '$deck_groups'     => $deck_groups,
            //                    '$decks'           => $decks,
            //                ]);
            //            }
            $dg->decks_arr   = $decks_arr;
            $dg->decks       = $decks;
            $dg->due_summary = $dg_due_summary;
            $dg->total_due   = $dg_due_summary['total']
                + $dg_due_summary['new']
                + $dg_due_summary['revision']
                + $dg_due_summary['previously_false'];
        }
        $deck_group_arr = $deck_groups->all();
        usort($deck_group_arr, function ($a, $b) {
            if ($a["total_due"] == $b["total_due"]) {
                return 0;
            }
            return ($a["total_due"] > $b["total_due"]) ? -1 : 1;
        });
        //        Common::send_error([
        //            'ajax_admin_load_deck_group',
        //            '$user_id'         => $user_id,
        //            '$args'            => $args,
        //            '$all_deck_groups' => $all_deck_groups,
        //            '$deck_groups'     => $deck_groups,
        //            '$deck_group_arr'  => $deck_group_arr,
        //            //				'$deck_groups'     => $deck_groups->toSql(),
        //            //				'getQuery'         => $deck_groups->getQuery(),
        //        ]);

        return [
            'total'       => $total,
            'deck_groups' => $deck_group_arr,
        ];
    }

    public static function get_deck_groups_front_end_one($deck_group_id) {
        $user_id = get_current_user_id();

        $deck_group = DeckGroup::with([
            'tags',
            'decks.studies'     => function ($query) use ($user_id) {
                $query->where('user_id', '=', $user_id);
            },
            'decks'             => function ($query) {
                $query->withCount('card_groups');
            },
            'decks.card_groups' => function ($query) {
                $query->withCount('cards');
            },
        ])->find($deck_group_id);

        $dg             = $deck_group;
        $decks          = $dg->decks;
        $dg_due_summary = [
            'new'              => 0,
            'revision'         => 0,
            'previously_false' => 0,
        ];
        foreach ($decks as $_deck) {
            //            $_study              = $_deck->studies->first();
            $_study              = Study
                ::where('deck_id', '=', $_deck->id)
                ->where('user_id', '=', $user_id)
                ->get()
                ->first();
            $_deck->_study_first = $_study;
            if (empty($_study)) {
                $_deck->due_summary = Study::get_study_due_summary(0, $user_id);
            } else {
                $_deck->due_summary = Study::get_study_due_summary($_study->id, $user_id);
            }
            $deck_total_due_cards               = $_deck->due_summary['new']
                + $_deck->due_summary['revision']
                + $_deck->due_summary['previously_false'];
            $_deck->deck_total_due_cards        = $deck_total_due_cards;
            $dg_due_summary['total']            += $deck_total_due_cards;


            $card_counts                        = $_deck->card_groups->pluck('cards_count');
            $_deck->card_count                  = array_sum($card_counts->toArray());
            $dg_due_summary['new']              = $dg_due_summary['new'] + $_deck->due_summary['new'];
            $dg_due_summary['revision']         = $dg_due_summary['revision'] + $_deck->due_summary['revision'];
            $dg_due_summary['previously_false'] = $dg_due_summary['previously_false'] + $_deck->due_summary['previously_false'];
        }
        $dg->due_summary = $dg_due_summary;
        $decks_arr = $decks->all();
        usort($decks_arr, function ($a, $b) {
            if ($a["deck_total_due_cards"] === $b["deck_total_due_cards"]) {
                return 0;
            }
            return ($a["deck_total_due_cards"] > $b["deck_total_due_cards"]) ? -1 : 1;
        });
        $dg->decks_arr   = $decks_arr;

        //        Common::send_error([
        //            __METHOD__,
        //            '$decks' => $decks,
        //            '$dg'    => $dg,
        //        ]);

        return $dg;
    }

    public static function get_deck_groups_simple($args): array {
        $default = [
            'search'       => '',
            'page'         => 1,
            'per_page'     => 5,
            'with_trashed' => false,
            'only_trashed' => false,
        ];
        $args    = wp_parse_args($args, $default);
        if ($args['with_trashed']) {
            $deck_groups = DeckGroup::withoutTrashed()::with('tags');
        } elseif ($args['only_trashed']) {
            $deck_groups = DeckGroup::onlyTrashed();
        } else {
            $deck_groups = DeckGroup::with('tags');
        }
        $deck_groups = $deck_groups
            ->where('name', 'like', "%{$args['search']}%");
        $offset      = ($args['page'] - 1);
        $deck_groups = $deck_groups->offset($offset)
            ->limit($args['per_page'])
            ->orderByDesc('id')->get();

        return $deck_groups->all();
    }

    public static function get_totals(): array {
        $all     = [
            'active'  => 0,
            'trashed' => 0,
        ];
        $active  = DeckGroup::query()
            ->selectRaw(Manager::raw('count(*) as count'))
            ->get();
        $trashed = DeckGroup::onlyTrashed()
            ->selectRaw(Manager::raw('count(*) as count'))->get();

        $all['active']  = $active[0]['count'];
        $all['trashed'] = $trashed[0]['count'];

        //			Common::send_error( [
        //				'query log' => Manager::getQueryLog(),
        ////				'active query' => $active->toSql(),
        //				'$active'  => $active,
        //				'$trashed' => $trashed,
        //				'count'    => $active[0]['count'],
        //			] );

        return $all;
    }


}