<?php

namespace StudyPlannerPro\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Model\Answered;
use Model\Card;
use Model\CardGroup;
use Model\Deck;
use Model\DeckGroup;
use Model\User;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Models\Tag;
use function StudyPlannerPro\get_uncategorized_deck_group_id;
use function StudyPlannerPro\get_uncategorized_deck_id;
use function StudyPlannerPro\get_uncategorized_topic_id;
use function StudyPlannerPro\get_user_timezone_date_early_morning_today;
use function StudyPlannerPro\get_user_timezone_date_midnight_today;
use function StudyPlannerPro\sp_get_user_study;

class UserCard extends Model {
	protected $table = SP_TABLE_USER_CARDS;

	use SoftDeletes;

	protected $dates = array( 'deleted_at' );
	protected $fillable = array( 'user_id', 'card_group_id', 'created_at', 'updated_at' );

	public function user(): BelongsTo {
		return $this->belongsTo( User::class, 'user_id' );
	}

	public function card_group(): BelongsTo {
		return $this->belongsTo( CardGroup::class, 'card_group_id' );
	}

	/**
	 * Used for when both topics and decks can be studied.
	 *
	 * @param int $user_id
	 *
	 * @return array|array[]
	 */
	public static function get_user_cards_to_study( int $user_id ) {
		// Get all user cards.
		$all_user_cards = self::get_all_user_cards( $user_id );
		if ( empty( $all_user_cards['card_group_ids'] ) ) {
			return array(
				'deck_groups'                       => array(),
				'new_card_ids'                      => array(),
				'on_hold_card_ids'                  => array(),
				'revision_card_ids'                 => array(),
				'user_card_group_ids_being_studied' => array()
			);
		}

		$deck_group_uncategorized_id = get_uncategorized_deck_group_id();
		$deck_uncategorized_id       = get_uncategorized_deck_id();
		$topic_uncategorized_id      = get_uncategorized_topic_id();

		// Remove all card groups in any collection.
		$card_groups_in_collection = CardGroup::get_card_groups_in_any_collections();

		$user_study    = sp_get_user_study( $user_id );
		$user_study_id = $user_study->id;

		$last_answered_card_ids = self::get_all_last_answered_user_cards( $user_id, $user_study_id );
		$new_cards              = self::get_new_cards_not_answered_but_added( $user_id, $user_study_id, $last_answered_card_ids['card_ids'] );

		// Get cards organized by deck groups, decks, topics and card_groups.
		$deck_groups = DeckGroup::with(
			array(
				'decks.topics.card_groups.cards',
				'decks.studies'        => function ( $q ) use ( $user_id ) {
					$q->where( 'user_id', '=', $user_id );
				},
				'decks.studies.tags',
				'decks.studies.tags_excluded',
				'decks.topics.studies' => function ( $q ) use ( $user_id ) {
					$q->where( 'user_id', '=', $user_id );
				},
				'decks.studies.deck',
				'decks.topics.studies.topic',
				'decks.topics.studies.tags',
				'decks.topics.studies.tags_excluded',
				'studies'              => function ( $q ) use ( $user_id ) {
					$q->where( 'user_id', '=', $user_id );
				},
				'studies.tags',
				'studies.tags_excluded',
			)
		);

		// Remove all card groups in any collection.
		$deck_groups = $deck_groups
			->whereHas(
				'decks.topics.card_groups',
				function ( $query ) use ( $card_groups_in_collection ) {
					$query->whereNotIn( 'id', $card_groups_in_collection['card_group_ids'] );
				}
			);

		// Remove uncategorized deck group.
		$deck_groups = $deck_groups->where( 'id', '!=', $deck_group_uncategorized_id );

		// Remove uncategorized deck.
		$deck_groups = $deck_groups->whereHas(
			'decks',
			function ( $query ) use ( $deck_uncategorized_id ) {
				$query->where( 'id', '!=', $deck_uncategorized_id );
			}
		);

		// Remove uncategorized topic.
		$deck_groups = $deck_groups->whereHas(
			'decks.topics',
			function ( $query ) use ( $topic_uncategorized_id ) {
				$query->where( 'id', '!=', $topic_uncategorized_id );
			}
		)->get();

		// encode all questions in deck groups.
		foreach ( $deck_groups as $deck_group ) {
			foreach ( $deck_group->decks as $deck ) {
				foreach ( $deck->topics as $topic ) {
					foreach ( $topic->card_groups as $card_group ) {
						foreach ( $card_group->cards as $card ) {
							$card_type = $card->card_group->card_type;
							if ( in_array( $card_type, array( 'table', 'image' ) ) ) {
								if ( ! is_array( $card->answer ) ) {
									$card->answer = json_decode( $card->answer );
								}
								if ( ! is_array( $card->question ) ) {
									$card->question = json_decode( $card->question );
								}
								if ( ! is_array( $card_group->whole_question ) ) {
									$card_group->whole_question = json_decode( $card_group->whole_question );
								}
							}
						}
					}
				}
			}
		}

		return array(
			'deck_groups'                       => $deck_groups->all(),
			'new_card_ids'                      => $new_cards['card_ids'],
			'on_hold_card_ids'                  => $last_answered_card_ids['on_hold_and_due_ids'],
			'revision_card_ids'                 => $last_answered_card_ids['revision_and_due_ids'],
			'user_card_group_ids_being_studied' => $all_user_cards['card_group_ids']
		);
	}

	/**
	 * Get user cards.
	 * Used only for when only topics can be studied.
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public static function get_user_cards_to_study___( int $user_id ) {
		// Get all user cards.
		$all_user_cards = self::get_all_user_cards( $user_id );
		if ( empty( $all_user_cards['card_group_ids'] ) ) {
			return array(
				'deck_groups'                       => array(),
				'new_card_ids'                      => array(),
				'on_hold_card_ids'                  => array(),
				'revision_card_ids'                 => array(),
				'user_card_group_ids_being_studied' => array()
			);
		}

		$deck_group_uncategorized_id = get_uncategorized_deck_group_id();
		$deck_uncategorized_id       = get_uncategorized_deck_id();

		// Remove all card groups in any collection.
		$card_groups_in_collection = CardGroup::get_card_groups_in_any_collections();


		$user_study    = sp_get_user_study( $user_id );
		$user_study_id = $user_study->id;


		$last_answered_card_ids = self::get_all_last_answered_user_cards( $user_id, $user_study_id );
		$new_cards              = self::get_new_cards_not_answered_but_added( $user_id, $user_study_id, $last_answered_card_ids['card_ids'] );

//		Common::send_error( '', array(
//			__METHOD__,
//			__LINE__,
//			'user_study_id'          => $user_study_id,
//			'user_id'                => $user_id,
//			'last_answered_card_ids' => $last_answered_card_ids,
//		) );

		// Get cards organized by deck groups, decks, topics and card_groups.
		$deck_groups = DeckGroup::with(
			array(
				'decks.topics.card_groups.cards' => function ( $q ) use ( $new_cards, $last_answered_card_ids ) {
					$q->whereIn(
						'id',
						array_values(
							array_merge(
								$new_cards['card_ids'],
								$last_answered_card_ids['on_hold_and_due_ids'],
								$last_answered_card_ids['revision_and_due_ids']
							)
						)
					);
				},
				'decks.studies'                  => function ( $q ) use ( $user_id ) {
					$q->where( 'user_id', '=', $user_id );
				},
			)
		);

		// Remove all card groups in any collection.
		$deck_groups = $deck_groups
			->whereHas(
				'decks.topics.card_groups',
				function ( $query ) use ( $card_groups_in_collection ) {
					$query->whereNotIn( 'id', $card_groups_in_collection['card_group_ids'] );
				}
			);

		// Remove uncategorized deck group.
		$deck_groups = $deck_groups->where( 'id', '!=', $deck_group_uncategorized_id );

		// Remove uncategorized deck.
		$deck_groups = $deck_groups->whereHas(
			'decks',
			function ( $query ) use ( $deck_uncategorized_id ) {
				$query->where( 'id', '!=', $deck_uncategorized_id );
			}
		)->get();


		// encode all questions in deck groups.
		foreach ( $deck_groups as $deck_group ) {
			foreach ( $deck_group->decks as $deck ) {
				foreach ( $deck->topics as $topic ) {
					foreach ( $topic->card_groups as $card_group ) {
						foreach ( $card_group->cards as $card ) {
							$card_type = $card->card_group->card_type;
							if ( in_array( $card_type, array( 'table', 'image' ) ) ) {
								if ( ! is_array( $card->answer ) ) {
									$card->answer = json_decode( $card->answer );
								}
								if ( ! is_array( $card->question ) ) {
									$card->question = json_decode( $card->question );
								}
								if ( ! is_array( $card_group->whole_question ) ) {
									$card_group->whole_question = json_decode( $card_group->whole_question );
								}
							}
						}
					}
				}
			}
		}

		return array(
			'deck_groups'                       => $deck_groups->all(),
			'new_card_ids'                      => $new_cards['card_ids'],
			'on_hold_card_ids'                  => $last_answered_card_ids['on_hold_and_due_ids'],
			'revision_card_ids'                 => $last_answered_card_ids['revision_and_due_ids'],
			'user_card_group_ids_being_studied' => $all_user_cards['card_group_ids']
		);
	}

	/**
	 * Get user cards.
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public static function get_all_user_cards( int $user_id ): array {
		$user_cards = self::query()
		                  ->with( 'card_group.cards' )
		                  ->where( 'user_id', '=', $user_id )
		                  ->get()->all();

		$cards          = array();
		$card_ids       = array();
		$card_group_ids = array();
		foreach ( $user_cards as $user_card ) {
			$card_group               = $user_card->card_group;
			$cards[ $card_group->id ] = $card_group->cards;
			foreach ( $card_group->cards as $card ) {
				$card_ids[] = $card->id;
			}
			$card_group_ids[] = $card_group->id;
		}

		return array(
			'cards'          => $cards,
			'card_ids'       => $card_ids,
			'card_group_ids' => $card_group_ids,
		);
	}

	/**
	 * Get all answered last answered cards.
	 *
	 * @param int $user_id The user's id.
	 * @param int $user_study_id The user's study id.
	 *
	 * @return array
	 */
	public static function get_all_last_answered_user_cards( int $user_id, int $user_study_id ): array {
		$card_answered = Answered
			::query()
			->with(
				array(
					'card',
					'study' => function ( $q ) use ( $user_study_id ) {
						$q->where( 'id', '=', $user_study_id );
					},
				)
			)
			->groupBy( 'card_id' )
			->orderBy( 'created_at', 'desc' )
			->get()->all();

		/**
		 * @var string $today
		 */
		$today = Common::getDateTime();

		$cards                     = array();
		$card_ids                  = array();
		$cards_on_hold             = array();
		$cards_on_hold_and_due     = array();
		$cards_in_revision         = array();
		$cards_in_revision_and_due = array();
		foreach ( $card_answered as $answered ) {
			$card = $answered->card;
			if ( ! $card ) {
				continue;
			}
			$cards[]    = $card;
			$card_ids[] = $card->id;
			$grade      = $answered->grade;
			if ( $grade === 'hold' ) {
				$cards_on_hold[] = $card;
			} else {
				$cards_in_revision[] = $card;
			}

			// Check if card is due today.
			$card_due_date = $answered->next_due_at;
			// e.i. if due date is today or before today.
			if ( strtotime( $card_due_date ) <= strtotime( date( 'Y-m-d', strtotime( $today ) ) ) ) {
				if ( $answered->grade === 'hold' ) {
					$cards_on_hold_and_due[] = $card;
				} else {
					$cards_in_revision_and_due[] = $card;
				}
			}
		}

		return array(
			'cards'                => $cards,
			'card_ids'             => $card_ids,
			'on_hold'              => $cards_on_hold,
			'on_hold_and_due'      => $cards_on_hold_and_due,
			'on_hold_and_due_ids'  => array_map(
				static function ( $card ) {
					return $card->id;
				},
				$cards_on_hold_and_due
			),
			'revision'             => $cards_in_revision,
			'revision_and_due'     => $cards_in_revision_and_due,
			'revision_and_due_ids' => array_map(
				static function ( $card ) {
					return $card->id;
				},
				$cards_in_revision_and_due
			),
		);
	}

	/**
	 * Get cards that has not been studied before.
	 *
	 * @param int $user_id The user's id.
	 * @param int $user_study_id The user's study id.
	 * @param array $last_answered_card_ids The last answered card ids.
	 *
	 * @return int[]
	 */
	public static function get_new_cards_not_answered_but_added( int $user_id, int $user_study_id, array $last_answered_card_ids = array() ): array {

		$user_timezone_early_morning_today = get_user_timezone_date_early_morning_today( $user_id );
		$user_timezone_midnight_today      = get_user_timezone_date_midnight_today( $user_id );
		$all_users_card                    = self::get_all_user_cards( $user_id );

		$card_groups = self::query()
		                   ->where( 'user_id', '=', $user_id )
		                   ->with(
			                   array(
				                   'card_group.cards' => function ( $query ) use ( $all_users_card, $last_answered_card_ids ) {
					                   // Exclude all cards that has been Answered before.
					                   $query
						                   ->whereIn( 'id', $all_users_card['card_ids'] )
						                   ->whereNotIn( 'id', $last_answered_card_ids );
				                   },
			                   )
		                   )->get()->all();

		// return card ids.
		$card_ids       = array();
		$cards          = array();
		$card_group_ids = array();
		$topic_ids      = array();
		foreach ( $card_groups as $user_card ) {
			foreach ( $user_card->card_group->cards as $card ) {
				$card_ids[]       = $card->id;
				$cards[]          = $card;
				$card_group_ids[] = $user_card->card_group->id;
				$topic_ids[]      = $user_card->card_group->topic_id ?? 0;
			}
		}

		return array(
			'cards'          => $cards,
			'card_ids'       => $card_ids,
			'card_group_ids' => $card_group_ids,
			'topic_ids'      => $topic_ids,
		);
	}

	/**
	 * Get all cards on hold for today.
	 *
	 * @param int $user_id The user's id.
	 * @param int $user_study_id The user's study id.
	 * @param array $last_answered_card_ids_on_hold_and_due The last answered card ids that are on hold and are due.
	 *
	 * @return int[]
	 */
	public static function get_cards_on_hold_for_today( int $user_id, int $user_study_id, array $last_answered_card_ids_on_hold_and_due = array() ): array {
		// $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today( $user_id );
		// $user_timezone_midnight_today      = get_user_timezone_date_midnight_today( $user_id );
		$all_users_card = self::get_all_user_cards( $user_id );

		$card_groups = self::query()
		                   ->where( 'user_id', '=', $user_id )
		                   ->with(
			                   array(
				                   'card_group.cards' => function ( $query ) use ( $all_users_card, $last_answered_card_ids_on_hold_and_due ) {
					                   // Exclude all cards that has been Answered before.
					                   $query
						                   ->whereIn( 'id', $last_answered_card_ids_on_hold_and_due );
				                   },
			                   )
		                   )
		                   ->get()->all();

		// return card ids.
		$card_ids = array();
		$cards    = array();
		foreach ( $card_groups as $user_card ) {
			foreach ( $user_card->card_group->cards as $card ) {
				$card_ids[] = $card->id;
				$cards[]    = $card;
			}
		}

		return array(
			'cards'    => $cards,
			'card_ids' => $card_ids,
		);
	}


	/**
	 * Get all cards in revision for today.
	 *
	 * @param int $user_id The user's id.
	 * @param int $user_study_id The user's study id.
	 * @param array $last_answered_card_ids_in_revision_and_due The last answered card ids that are on hold and are due.
	 *
	 * @return int[]
	 */
	public static function get_cards_in_revision_for_today( int $user_id, int $user_study_id, array $last_answered_card_ids_in_revision_and_due = array() ): array {
		// $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today( $user_id );
		// $user_timezone_midnight_today      = get_user_timezone_date_midnight_today( $user_id );
		$all_users_card = self::get_all_user_cards( $user_id );

		$card_groups = self::query()
		                   ->where( 'user_id', '=', $user_id )
		                   ->with(
			                   array(
				                   'card_group.cards' => function ( $query ) use ( $all_users_card, $last_answered_card_ids_in_revision_and_due ) {
					                   // Exclude all cards that has been Answered before.
					                   $query
						                   ->whereIn( 'id', $last_answered_card_ids_in_revision_and_due );
				                   },
			                   )
		                   )
		                   ->get()->all();

		// return card ids.
		$card_ids = array();
		$cards    = array();
		foreach ( $card_groups as $user_card ) {
			foreach ( $user_card->card_group->cards as $card ) {
				$card_ids[] = $card->id;
				$cards[]    = $card;
			}
		}

		return array(
			'cards'    => $cards,
			'card_ids' => $card_ids,
		);
	}


}

