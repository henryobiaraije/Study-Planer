<?php

namespace StudyPlanner\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Model\Card;
use Model\CardGroup;

class Collections extends Model {
	protected $table = SP_TABLE_COLLECTIONS;

	use SoftDeletes;

	protected $dates = array( 'deleted_at' );
	protected $fillable = array( 'name' );

	public function cards(): \Illuminate\Database\Eloquent\Relations\HasManyThrough {
		return $this->hasManyThrough( Card::class, CardGroup::class );
	}

	public function cardGroups() {
		return $this->hasMany( CardGroup::class );
	}

	public function card_groups() {
		return $this->hasMany( CardGroup::class, 'collection_id' );
	}

	public static function get_collections_simple( $args ): array {
		$default = array(
			'search'       => '',
			'page'         => 1,
			'per_page'     => 5,
			'with_trashed' => false,
			'only_trashed' => false,
		);
		$args    = wp_parse_args( $args, $default );
		// if ( $args['with_trashed'] ) {
		// $items = self::withoutTrashed()::with( 'tags' );
		// } elseif ( $args['only_trashed'] ) {
		// $items = self::onlyTrashed();
		// } else {
		// $items = self::with( 'tags' );
		// }
		$items  = self::where( 'name', 'like', "%{$args['search']}%" );
		$offset = ( $args['page'] - 1 );
		$items  = $items->offset( $offset )
		                ->limit( $args['per_page'] )
		                ->orderByDesc( 'id' )->get();

		return $items->all();
	}

	public static function get_collections( $args ): array {
		$default     = array(
			'search'       => '',
			'page'         => 1,
			'per_page'     => 5,
			'with_trashed' => false,
			'only_trashed' => false,
		);
		$args        = wp_parse_args( $args, $default );
		$collections = self::where( 'name', 'like', "%{$args['search']}%" );

		$total       = $collections->count();
		$offset      = ( $args['page'] - 1 );
		$collections = $collections
			->offset( $offset )
			->with( 'card_groups' )
			->limit( $args['per_page'] )
			->orderByDesc( 'id' )->get();

		return array(
			'total'       => $total,
			'collections' => $collections->all(),
		);
	}

	public static function get_totals(): array {
		$all     = array();
		$active  = self::query()
		               ->selectRaw( Manager::raw( 'count(*) as count' ) )
		               ->get();
		$trashed = self::onlyTrashed()
		               ->selectRaw( Manager::raw( 'count(*) as count' ) )->get();

		$all['active']  = $active[0]['count'];
		$all['trashed'] = $trashed[0]['count'];

		return $all;
	}


}
