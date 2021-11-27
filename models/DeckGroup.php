<?php

	namespace Model;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use Illuminate\Database\Query\Builder;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Models\Tag;

	class DeckGroup extends Model {
		protected $table = SP_DB_PREFIX . 'deck_groups';

		use SoftDeletes;

		protected $dates = [ 'deleted_at' ];

		protected $fillable = [ 'name' ];
		protected $casts    = [
			'is_admin' => 'boolean',
		];

		public function tags() {
			return $this->morphToMany( Tag::class, 'taggable', SP_TABLE_TAGGABLES );
		}

		public function decks() {
			return $this->hasMany( Deck::class, 'deck_group_id' );
		}

		public function decks_with_study( $user_id ) {
			return $this->hasMany( Deck::class, 'deck_group_id' );
		}

		public static function get_deck_groups( $args ) : array {
			$default = [
				'search'       => '',
				'page'         => 1,
				'per_page'     => 5,
				'with_trashed' => false,
				'only_trashed' => false,
			];
			$args    = wp_parse_args( $args, $default );
			if ( $args['with_trashed'] ) {
				$deck_groups = DeckGroup::with( 'tags' )->withoutTrashed();
			} elseif ( $args['only_trashed'] ) {
				$deck_groups = DeckGroup::with( 'tags' )->onlyTrashed();
			} else {
				$deck_groups = DeckGroup::with( 'tags' );
			}
			$deck_groups = $deck_groups
				->where( 'name', 'like', "%{$args['search']}%" );

			$total       = $deck_groups->count();
			$offset      = ( $args['page'] - 1 );
			$deck_groups = $deck_groups->offset( $offset )
				->limit( $args['per_page'] )
				->orderByDesc( 'id' )->get();

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

		public static function get_deck_groups_front_end( $args ) : array {
			$user_id = get_current_user_id();
			$default = [
				'search'       => '',
				'page'         => 1,
				'per_page'     => 5,
				'with_trashed' => false,
				'only_trashed' => false,
			];
			$args    = wp_parse_args( $args, $default );
			if ( $args['with_trashed'] ) {
//				$deck_groups = DeckGroup::with( 'tags', 'decks' )->withoutTrashed();
			} elseif ( $args['only_trashed'] ) {
//				$deck_groups = DeckGroup::with( 'tags', 'decks' )->onlyTrashed();
			} else {
//				$deck_groups = DeckGroup::with( 'tags', 'decks_with_study' );
				$deck_groups = DeckGroup::with( [
					'tags',
					'decks',
					'decks.studies' => function ( $q ) use ( $user_id ) {
						$q->where( 'user_id', '=', $user_id );
//						 $query->select( SP_TABLE_STUDY.' as st')
//						->where('user_id', '=', $user_id );
					},
				] );
//				dd(Manager::getQueryLog());
			}
			$deck_groups = $deck_groups
				->where( 'name', 'like', "%{$args['search']}%" );

			$total       = $deck_groups->count();
			$offset      = ( $args['page'] - 1 );
			$deck_groups = $deck_groups->offset( $offset )
				->limit( $args['per_page'] )
				->orderByDesc( 'id' );
//			Common::send_error( [
//				__METHOD__,
//				'query' => $deck_groups->toSql(),
//			] );
			$deck_groups = $deck_groups->get();
//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'$args'        => $args,
//				'$deck_groups' => $deck_groups,
//			] );
			$all_deck_groups = [];
			foreach ( $deck_groups as $dg ) {
				$decks          = $dg->decks;
				$dg_due_summary = [
					'new'              => 0,
					'revision'         => 0,
					'previously_false' => 0,
				];
				foreach ( $decks as $_deck ) {
					$_study              = $_deck->studies->first();
					$_deck->_study_first = $_study;
					if ( empty( $_study ) ) {
						$_deck->due_summary = Study::get_study_due_summary( 0, $user_id );
					} else {
						$_deck->due_summary = Study::get_study_due_summary( $_study->id, $user_id );
					}
					$dg_due_summary['new']              = $dg_due_summary['new'] + $_deck->due_summary['new'];
					$dg_due_summary['revision']         = $dg_due_summary['revision'] + $_deck->due_summary['revision'];
					$dg_due_summary['previously_false'] = $dg_due_summary['previously_false'] + $_deck->due_summary['previously_false'];
				}
				$dg->due_summary = $dg_due_summary;
			}
//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'$args'            => $args,
//				'$all_deck_groups' => $all_deck_groups,
//				'$deck_groups'     => $deck_groups,
////				'$deck_groups'     => $deck_groups->toSql(),
////				'getQuery'         => $deck_groups->getQuery(),
//			] );

			return [
				'total'       => $total,
				'deck_groups' => $deck_groups->all(),
			];
		}

		public static function get_deck_groups_front_end_one( $deck_group_id ) {
			$user_id = get_current_user_id();

			$deck_group = DeckGroup::with( [
				'tags',
				'decks',
				'decks.studies',
			] )->find( $deck_group_id );

			$dg             = $deck_group;
			$decks          = $dg->decks;
			$dg_due_summary = [
				'new'              => 0,
				'revision'         => 0,
				'previously_false' => 0,
			];
			foreach ( $decks as $_deck ) {
				$_study              = $_deck->studies->first();
				$_deck->_study_first = $_study;
				if ( empty( $_study ) ) {
					$_deck->due_summary = Study::get_study_due_summary( 0, $user_id );
				} else {
					$_deck->due_summary = Study::get_study_due_summary( $_study->id, $user_id );
				}
				$dg_due_summary['new']              = $dg_due_summary['new'] + $_deck->due_summary['new'];
				$dg_due_summary['revision']         = $dg_due_summary['revision'] + $_deck->due_summary['revision'];
				$dg_due_summary['previously_false'] = $dg_due_summary['previously_false'] + $_deck->due_summary['previously_false'];
			}
			$dg->due_summary = $dg_due_summary;


			return $dg;
		}

		public static function get_deck_groups_simple( $args ) : array {
			$default = [
				'search'       => '',
				'page'         => 1,
				'per_page'     => 5,
				'with_trashed' => false,
				'only_trashed' => false,
			];
			$args    = wp_parse_args( $args, $default );
			if ( $args['with_trashed'] ) {
				$deck_groups = DeckGroup::withoutTrashed()::with( 'tags' );
			} elseif ( $args['only_trashed'] ) {
				$deck_groups = DeckGroup::onlyTrashed();
			} else {
				$deck_groups = DeckGroup::with( 'tags' );
			}
			$deck_groups = $deck_groups
				->where( 'name', 'like', "%{$args['search']}%" );
			$offset      = ( $args['page'] - 1 );
			$deck_groups = $deck_groups->offset( $offset )
				->limit( $args['per_page'] )
				->orderByDesc( 'id' )->get();

			return $deck_groups->all();
		}

		public static function get_totals() : array {
			$all     = [
				'active'  => 0,
				'trashed' => 0,
			];
			$active  = DeckGroup::query()
				->selectRaw( Manager::raw( 'count(*) as count' ) )
				->get();
			$trashed = DeckGroup::onlyTrashed()
				->selectRaw( Manager::raw( 'count(*) as count' ) )->get();

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