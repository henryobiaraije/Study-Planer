<?php

	namespace Model;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class DeckGroup extends Model {
		protected $table = SP_DB_PREFIX . 'deck_groups';

		use SoftDeletes;

		protected $dates = [ 'deleted_at' ];

		protected $fillable = [ 'name' ];
		protected $casts    = [
			'is_admin' => 'boolean',
		];


		public static function get_deck_groups( $args ) {
			$default = [
				'search'       => '',
				'page'         => 1,
				'per_page'     => 5,
				'with_trashed' => false,
				'only_trashed' => false,
			];
			$args    = wp_parse_args( $args, $default );


			if ( $args['with_trashed'] ) {
				$deck_groups = DeckGroup::withoutTrashed()::query();
			} elseif ( $args['only_trashed'] ) {
				$deck_groups = DeckGroup::onlyTrashed()::query();
			} else {
				$deck_groups = DeckGroup::query();
			}
			$deck_groups = $deck_groups
				->where( 'name', 'like', "%{$args['id']}%" );

			$total       = $deck_groups->count();
			$deck_groups = $deck_groups->offset( $args['page'] )
				->limit( $args['per_page'] )->orderByDesc( 'id' )
				->get();


			return [
				'total'       => $total,
				'deck_groups' => $deck_groups->all(),
			];
//			$all = [];
//			foreach ( $deck_groups as $group ) {
//				$all[] = [
//					'id' => $group->id,
//					''
//				];
//			}
//
//			return $all;
		}


	}