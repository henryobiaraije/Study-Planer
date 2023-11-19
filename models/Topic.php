<?php

namespace Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Models\Tag;

use function StudyPlannerPro\get_uncategorized_deck_id;
use function StudyPlannerPro\get_uncategorized_topic_id;

class Topic extends Model {
	protected $table = SP_TABLE_TOPICS;

	use SoftDeletes;

	protected $dates = array( 'deleted_at' );
	protected $fillable = array( 'name', 'deck_id' );

	public function tags() {
		return $this->morphToMany( Tag::class, 'taggable', SP_TABLE_TAGGABLES );
	}

	public function cards() {
		return $this->hasManyThrough( Card::class, CardGroup::class );
	}

	public function card_groups() {
		return $this->hasMany( CardGroup::class, 'topic_id' );
	}

	public function studies() {
		return $this->hasMany( Study::class );
	}

	public function deck(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
		return $this->belongsTo( Deck::class, 'deck_id' );
	}

	public static function get_topic_simple( $args ): array {
		$default = array(
			'search'       => '',
			'page'         => 1,
			'per_page'     => 5,
			'with_trashed' => false,
			'only_trashed' => false,
			'deck_id'      => null
		);
		$args    = wp_parse_args( $args, $default );
		if ( $args['with_trashed'] ) {
			$items   = self::withoutTrashed()::with( 'tags' );
			$items_2 = self::withoutTrashed()::with( 'tags' );
		} elseif ( $args['only_trashed'] ) {
			$items   = self::onlyTrashed();
			$items_2 = self::onlyTrashed();
		} else {
			$items   = self::with( 'tags' );
			$items_2 = self::with( 'tags' );
		}
		if ( ! empty( $args['search'] ) ) {
			$items   = $items
				->where( 'name', 'like', "%{$args['search']}%" );
			$items_2 = $items_2
				->where( 'name', 'like', "%{$args['search']}%" );
		}
		$offset = ( $args['page'] - 1 );

		if ( $args['deck_id'] ) {
			$items   = $items->where( 'deck_id', $args['deck_id'] );
			$items_2 = $items_2->where( 'deck_id', $args['deck_id'] );
		}

		$items = $items->offset( $offset )
		               ->limit( $args['per_page'] )
		               ->orderByDesc( 'id' )->get();

		$all   = $items->all();
		$total = $items_2->count();

		return array(
			'items' => $all,
			'total' => $total,
		);

//		return $items->all();
	}

	public static function get_topics( $args ): array {
		$default = array(
			'search'       => '',
			'page'         => 1,
			'per_page'     => 5,
			'with_trashed' => false,
			'only_trashed' => false,
		);
		$args    = wp_parse_args( $args, $default );
		if ( $args['with_trashed'] ) {
			$deck = self::with( 'tags', 'deck.deck_group' )->withoutTrashed();
		} elseif ( $args['only_trashed'] ) {
			$deck = self::with( 'tags', 'deck.deck_group' )->onlyTrashed();
		} else {
			$deck = self::with( 'tags', 'deck.deck_group' );
		}
		$deck = $deck
			->where( 'name', 'like', "%{$args['search']}%" );

		$total  = $deck->count();
		$offset = ( $args['page'] - 1 );
		$deck   = $deck->offset( $offset )
		               ->with( 'cards' )
		               ->limit( $args['per_page'] )
		               ->orderByDesc( 'id' )->get();

		return array(
			'total'  => $total,
			'topics' => $deck->all(),
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

	/**
	 * Make sure card groups with a topic does not have uncategorized deck.
	 * @return void
	 */
	public static function make_sure_card_groups_with_real_topic_also_have_a_real_deck(): void {
		// Step 1: Loop through all card groups that doesn't belong to the uncategorized topic.
		// Step 2: For each cg,
		// If the cg belongs to the uncategorized deck, then change it to the deck the cg's topic belongs to
		// If you can't find the deck the cg's topic belongs to, then set the cg deck to uncategorized deck too.
		$uncategorized_topic_id = get_uncategorized_topic_id();
		$uncategorized_deck_id  = get_uncategorized_deck_id();

		if ( ! $uncategorized_topic_id ) {
			return;
		}
		if ( ! $uncategorized_deck_id ) {
			return;
		}

		// Get all card groups that have a topic that is not the uncategorized topic.
		$card_groups = CardGroup
			::query()
			->with( array(
				'topic' => function ( $query ) use ( $uncategorized_topic_id ) {
					$query->where( 'id', '!=', $uncategorized_topic_id );
				}
			) )
			->whereHas( 'topic', function ( $query ) use ( $uncategorized_topic_id ) {
				$query->where( 'id', '!=', $uncategorized_topic_id );
			} )
			->get();

		// Set all card group's deck_id to the uncategorized deck id.
		foreach ( $card_groups as $card_group ) {
			if ( $card_group->deck_id === (int) $uncategorized_deck_id ) {
				// Then try to change it because the topic is not uncategorized.

				// Get the deck this topic belongs to.
				$topic_deck = Topic
					::query()
					->with( array(
						'deck'
					) )
					->get()
					->first();

				if ( $topic_deck ) {
					$new_deck_id         = $topic_deck->deck_id;
					$card_group->deck_id = $new_deck_id;
					$card_group->save();
				} else {
					$card_group->deck_id = $uncategorized_deck_id;
					$card_group->save();
				}
			}
		}
	}

}
