<?php

	/**
	 * Tag Model
	 */

	namespace StudyPlannerPro\Models;

	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use Model\DeckGroup;

	if ( ! defined( 'ABSPATH' ) ) {
		exit();// exit if accessed directly
	}

	class Tag extends Model {

		use SoftDeletes;

		protected $table = SP_DB_PREFIX . 'tags';

		protected $fillable = [
			'name',
		];

		public function deck_groups() {
			return $this->morphedByMany(
				DeckGroup::class,
				'taggable',
				SP_TABLE_TAGGABLES
			);
		}

		public static function get_tags( $args ) : array {
			$default = [
				'search'       => '',
				'page'         => 1,
				'per_page'     => 5,
				'with_trashed' => false,
				'only_trashed' => false,
			];
			$args    = wp_parse_args( $args, $default );
			if ( $args['with_trashed'] ) {
				$query = Tag::withoutTrashed();
			} elseif ( $args['only_trashed'] ) {
				$query = Tag::onlyTrashed();
			} else {
				$query = Tag::query();
			}
			$query = $query
				->where( 'name', 'like', "%{$args['search']}%" );

			$total  = $query->count();
			$offset = ( $args['page'] - 1 );
			$query  = $query->offset( $offset )
				->limit( $args['per_page'] )
				->orderByDesc( 'id' )->get();

//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'$args'        => $args,
//				'$deck_groups' => $deck_groups->toSql(),
//				'getQuery'     => $deck_groups->getQuery(),
//			] );

			return [
				'total' => $total,
				'items' => $query->all(),
			];
		}

		public static function get_simple_tags( $args ) : array {
			$default = [
				'search'       => '',
				'page'         => 1,
				'per_page'     => 5,
				'with_trashed' => false,
				'only_trashed' => false,
			];
			$args    = wp_parse_args( $args, $default );
			if ( $args['with_trashed'] ) {
				$query = Tag::withoutTrashed();
			} elseif ( $args['only_trashed'] ) {
				$query = Tag::onlyTrashed();
			} else {
				$query = Tag::query();
			}
			$query  = $query
				->where( 'name', 'like', "%{$args['search']}%" );
			$offset = ( $args['page'] - 1 );
			$query  = $query->offset( $offset )
				->limit( $args['per_page'] )
				->orderByDesc( 'id' )->get();

//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'$args'        => $args,
//				'$deck_groups' => $deck_groups->toSql(),
//				'getQuery'     => $deck_groups->getQuery(),
//			] );

			return [
				'items' => $query->all(),
			];
		}

		public static function get_totals() : array {
			$all     = [
				'active'  => 0,
				'trashed' => 0,
			];
			$active  = Tag::query()
				->selectRaw( Manager::raw( 'count(*) as count' ) )
				->get();
			$trashed = Tag::onlyTrashed()
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
