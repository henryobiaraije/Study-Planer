<?php

namespace StudyPlanner\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Model\Card;
use Model\CardGroup;
use Model\Deck;
use Model\DeckGroup;
use Model\User;
use StudyPlanner\Libs\Common;
use StudyPlanner\Models\Tag;
use function StudyPlanner\get_uncategorized_deck_group_id;
use function StudyPlanner\get_uncategorized_deck_id;

class UserCard extends Model {
	protected $table = SP_TABLE_USER_CARDS;

	use SoftDeletes;

	protected $dates = array( 'deleted_at' );
	protected $fillable = array( 'user_id', 'card_group_id' );

	public function user(): BelongsTo {
		return $this->belongsTo( User::class, 'user_id' );
	}

	public function card_group(): BelongsTo {
		return $this->belongsTo( CardGroup::class, 'card_group_id' );
	}

	public static function get_user_cards( int $user_id ) {
		$deck_group_uncategorized_id = get_uncategorized_deck_group_id();
		$deck_uncategorized_id       = get_uncategorized_deck_id();
		// Get cards organized by deck groups, decks, topics and card_groups.
		$deck_groups = DeckGroup
			::with( 'decks.topics.card_groups.cards' );

		// Remove uncategorized deck group.
		$deck_groups->where( 'id', '!=', $deck_group_uncategorized_id );

		// Remove uncategorized deck.
		$deck_groups->whereHas( 'decks', function ( $query ) use ( $deck_uncategorized_id ) {
			$query->where( 'id', '!=', $deck_uncategorized_id );
		} );

		// Must have some cards.
//		$deck_groups->whereHas( 'decks.topics.card_groups.cards' );

		return [
			'deck_groups' => $deck_groups->get()->all(),
		];
	}

}
