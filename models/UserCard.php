<?php

namespace StudyPlannerPro\Models;

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
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Models\Tag;
use function StudyPlannerPro\get_uncategorized_deck_group_id;
use function StudyPlannerPro\get_uncategorized_deck_id;
use function StudyPlannerPro\get_user_timezone_date_early_morning_today;
use function StudyPlannerPro\get_user_timezone_date_midnight_today;
use function StudyPlannerPro\sp_get_user_study;

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

		$all_user_cards = self::get_all_user_cards( $user_id );


		$user_study    = sp_get_user_study( $user_id );
		$user_study_id = $user_study->id;
		// Get cards organized by deck groups, decks, topics and card_groups.
		$deck_groups = DeckGroup
			::with( [
				'decks.topics.card_groups.cards' => function ( $q ) use ( $all_user_cards ) {
					$q->whereIn( 'id', $all_user_cards['card_ids'] );
				}
			] );

		// Remove uncategorized deck group.
		$deck_groups->where( 'id', '!=', $deck_group_uncategorized_id );

		// Remove uncategorized deck.
		$deck_groups->whereHas( 'decks', function ( $query ) use ( $deck_uncategorized_id ) {
			$query->where( 'id', '!=', $deck_uncategorized_id );
		} );

		// Must have some cards.
//		$new_cards = self::get_new_cards( $user_id, $user_study_id );

		return [
			'deck_groups' => $deck_groups->get()->all(),
		];
	}

	/**
	 * Get user cards.
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public static function get_all_user_cards( int $user_id ): array {
		$user_cards = self
			::query()
			->with( 'card_group.cards' )
			->where( 'user_id', '=', $user_id )
			->get()->all();

		$cards    = [];
		$card_ids = [];
		foreach ( $user_cards as $user_card ) {
			$card_group               = $user_card->card_group;
			$cards[ $card_group->id ] = $card_group->cards;
			foreach ( $card_group->cards as $card ) {
				$card_ids[] = $card->id;
			}
		}

		return array(
			'cards'    => $cards,
			'card_ids' => $card_ids,
		);
	}

	/**
	 * Get cards that has not been studied before.
	 *
	 * @param int $user_id The user's id.
	 * @param int $user_study_id The user's study id.
	 *
	 * @return int[]
	 */
	public static function get_new_cards( int $user_id, int $user_study_id ): array {

		$user_timezone_early_morning_today = get_user_timezone_date_early_morning_today( $user_id );
		$user_timezone_midnight_today      = get_user_timezone_date_midnight_today( $user_id );

		$card_groups = self
			::query()
			->where( 'user_id', '=', $user_id )
			->with( [
				'card_group.cards' => function ( $query ) use ( $user_study_id, $user_timezone_early_morning_today, $user_timezone_midnight_today ) {
					// Exclude all cards that has been Answered before.
					$query
						->whereNotIn( 'id', function ( $query ) use ( $user_study_id, $user_timezone_early_morning_today, $user_timezone_midnight_today ) {
							$query
								->select( 'card_id' )
								->from( SP_TABLE_ANSWERED )
								->where( 'study_id', '=', $user_study_id )
								// distinct card_id
								->groupBy( 'card_id' );
						} );
				}
			] )->get()->all();


		// return card ids.
		$card_ids = [];
		foreach ( $card_groups as $user_card ) {
			foreach ( $user_card->card_group->cards as $card ) {
				$card_ids[] = $card->id;
			}
		}

		return $card_ids;
	}

}

