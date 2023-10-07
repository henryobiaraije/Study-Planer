<?php

namespace Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use StudyPlanner\Libs\Common;
use StudyPlanner\Models\Tag;

class Topic extends Model {
	protected $table = SP_TABLE_TOPICS;

	use SoftDeletes;

	protected $dates    = array( 'deleted_at' );
	protected $fillable = array( 'name', 'deck_id' );

	public function tags() {
		return $this->morphToMany( Tag::class, 'taggable', SP_TABLE_TAGGABLES );
	}

	public function cards() {
		return $this->hasManyThrough( Card::class, CardGroup::class );
	}

	public function studies() {
		return $this->hasMany( Study::class );
	}

	public function cardGroups() {
		return $this->hasMany( CardGroup::class );
	}

	public function deck_group() {
		return $this->belongsTo( DeckGroup::class, 'deck_group_id' );
	}

	public function card_group() {
		return $this->hasMany( CardGroup::class, 'deck_id' );
	}

	public function card_groups() {
		return $this->hasMany( CardGroup::class, 'deck_id' );
	}

	public static function get_deck_simple( $args ): array {
		$default = array(
			'search'       => '',
			'page'         => 1,
			'per_page'     => 5,
			'with_trashed' => false,
			'only_trashed' => false,
		);
		$args    = wp_parse_args( $args, $default );
		if ( $args['with_trashed'] ) {
			$items = self::withoutTrashed()::with( 'tags' );
		} elseif ( $args['only_trashed'] ) {
			$items = self::onlyTrashed();
		} else {
			$items = self::with( 'tags' );
		}
		$items  = $items
			->where( 'name', 'like', "%{$args['search']}%" );
		$offset = ( $args['page'] - 1 );
		$items  = $items->offset( $offset )
						->limit( $args['per_page'] )
						->orderByDesc( 'id' )->get();

		return $items->all();
	}

	public static function get_decks( $args ): array {
		$default = array(
			'search'       => '',
			'page'         => 1,
			'per_page'     => 5,
			'with_trashed' => false,
			'only_trashed' => false,
		);
		$args    = wp_parse_args( $args, $default );
		if ( $args['with_trashed'] ) {
			$deck = self::with( 'tags', 'deck_group' )->withoutTrashed();
		} elseif ( $args['only_trashed'] ) {
			$deck = self::with( 'tags', 'deck_group' )->onlyTrashed();
		} else {
			$deck = self::with( 'tags', 'deck_group' );
		}
		$deck = $deck
			->where( 'name', 'like', "%{$args['search']}%" );

		$total  = $deck->count();
		$offset = ( $args['page'] - 1 );
		$deck   = $deck->offset( $offset )
					   ->limit( $args['per_page'] )
					   ->orderByDesc( 'id' )->get();

		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// '$args'        => $args,
		// '$deck_groups' => $deck_groups->toSql(),
		// 'getQuery'     => $deck_groups->getQuery(),
		// ] );

		return array(
			'total' => $total,
			'decks' => $deck->all(),
		);
	}

	public static function get_totals(): array {
		$all     = array(
			'active'  => 0,
			'trashed' => 0,
		);
		$active  = self::query()
					   ->selectRaw( Manager::raw( 'count(*) as count' ) )
					   ->get();
		$trashed = self::onlyTrashed()
					   ->selectRaw( Manager::raw( 'count(*) as count' ) )->get();

		$all['active']  = $active[0]['count'];
		$all['trashed'] = $trashed[0]['count'];

		// Common::send_error( [
		// 'query log' => Manager::getQueryLog(),
		// 'active query' => $active->toSql(),
		// '$active'  => $active,
		// '$trashed' => $trashed,
		// 'count'    => $active[0]['count'],
		// ] );

		return $all;
	}


}
