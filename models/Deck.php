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

class Deck extends Model {
	protected $table = SP_TABLE_DECKS;

	use SoftDeletes;

	protected $dates = [ 'deleted_at' ];
	protected $fillable = [ 'name', 'deck_group_id' ];


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

	public function topics(): \Illuminate\Database\Eloquent\Relations\HasMany {
		return $this->hasMany( Topic::class );
	}

	public function card_group() {
		return $this->hasMany( CardGroup::class, 'deck_id' );
	}

	public function card_groups() {
		return $this->hasMany( CardGroup::class, 'deck_id' );
	}

	public static function get_deck_simple( $args ): array {
		$default = [
			'search'        => '',
			'page'          => 1,
			'per_page'      => 5,
			'with_trashed'  => false,
			'only_trashed'  => false,
			'deck_group_id' => null,
		];
		$args    = wp_parse_args( $args, $default );
		if ( $args['with_trashed'] ) {
			$deck   = self::withoutTrashed()::with( 'tags' );
			$deck_2 = self::withoutTrashed()::with( 'tags' );
		} elseif ( $args['only_trashed'] ) {
			$deck   = self::onlyTrashed();
			$deck_2 = self::onlyTrashed();
		} else {
			$deck   = self::with( 'tags' );
			$deck_2 = self::with( 'tags' );
		}
		$deck   = $deck
			->where( 'name', 'like', "%{$args['search']}%" );
		$deck_2 = $deck_2
			->where( 'name', 'like', "%{$args['search']}%" );

		$offset = ( $args['page'] - 1 );

		if ( $args['deck_group_id'] ) {
			$deck   = $deck->where( 'deck_group_id', $args['deck_group_id'] );
			$deck_2 = $deck_2->where( 'deck_group_id', $args['deck_group_id'] );
		}

		$deck   = $deck->offset( $offset )
		               ->limit( $args['per_page'] )
		               ->orderByDesc( 'id' )->get();
		$deck_2 = $deck_2
			->orderByDesc( 'id' )->get();

		$all   = $deck->all();
		$total = $deck_2->count();

//		return $deck->all();
		return array(
			'items' => $all,
			'total' => $total,
		);
	}

	public static function get_decks( $args ): array {
		$default = [
			'search'       => '',
			'page'         => 1,
			'per_page'     => 5,
			'with_trashed' => false,
			'only_trashed' => false,
		];
		$args    = wp_parse_args( $args, $default );
		if ( $args['with_trashed'] ) {
			$deck = Deck::with( 'tags', 'deck_group' )->withoutTrashed();
		} elseif ( $args['only_trashed'] ) {
			$deck = Deck::with( 'tags', 'deck_group' )->onlyTrashed();
		} else {
			$deck = Deck::with( 'tags', 'deck_group' );
		}
		$deck = $deck
			->where( 'name', 'like', "%{$args['search']}%" );

		$total  = $deck->count();
		$offset = ( $args['page'] - 1 );
		$deck   = $deck
			->offset( $offset )
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
			'decks' => $deck->all(),
		];
	}

	public static function get_totals(): array {
		$all     = [
			'active'  => 0,
			'trashed' => 0,
		];
		$active  = Deck::query()
		               ->selectRaw( Manager::raw( 'count(*) as count' ) )
		               ->get();
		$trashed = Deck::onlyTrashed()
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