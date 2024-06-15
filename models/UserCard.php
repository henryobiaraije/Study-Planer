<?php

namespace StudyPlannerPro\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Model\Answered;
use Model\Card;
use Model\CardGroup;
use Model\DeckGroup;
use Model\Study;
use Model\User;
use StudyPlannerPro\Initializer;
use StudyPlannerPro\Libs\Common;
use function StudyPlannerPro\get_uncategorized_topic_id;
use function StudyPlannerPro\sp_get_user_studies;
use function StudyPlannerPro\sp_in_sql_mode;

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
	public static function get_user_cards_to_study( int $user_id ): array {
		$in_user_cards_detail = self::get_groups_decks_and_topic_ids_in_user_cards( $user_id );
		$deck_groups = DeckGroup
			::with(
				array(
					'decks.topics.card_groups.cards',
					'decks.topics' 	   => function ( $q ) use ( $in_user_cards_detail ) {
						$q->whereIn( 'id', $in_user_cards_detail['topic_ids'] );
					},
					'decks'                => function ( $q ) use ( $in_user_cards_detail ) {
						$q->whereIn( 'id', $in_user_cards_detail['deck_ids'] );
					},
					// Decks.
					'decks.studies'        => function ( $q ) use ( $user_id ) {
						$q->where( 'user_id', '=', $user_id );
					},
					'decks.studies.deck',
					'decks.studies.topic',
					'decks.studies.tags',
					'decks.studies.tags_excluded',
					'decks.topics.studies' => function ( $q ) use ( $user_id ) {
						$q->where( 'user_id', '=', $user_id );
					},
					// Topics
					'decks.topics.studies.topic',
					'decks.topics.studies.tags',
					'decks.topics.studies.tags_excluded',
				)
			)
			->whereIn( 'id', $in_user_cards_detail['deck_group_ids'] )
			->get();




		// encode all questions in deck groups.
		foreach ( $deck_groups as $deck_group ) {
			$deck_group->count_new_cards = 0;
			$deck_group->count_revision  = 0;
			$deck_group->count_on_hold   = 0;
			$deck_group->in_user_cards   = in_array( $deck_group->id, $in_user_cards_detail['deck_group_ids'] );

			foreach ( $deck_group->decks as $deck ) {
				$deck->count_new_cards = 0;
				$deck->count_revision  = 0;
				$deck->count_on_hold   = 0;
				$deck->in_user_cards   = in_array( $deck->id, $in_user_cards_detail['deck_ids'] );

				$study = $deck->studies->first();
				if ( $study instanceof Study ) {
					if ( $study->active ) {
						$cards_to_study = self::get_study_cards(
							$user_id,
							$study,
							$study->all_tags ? array() : $study->tags()->get()->pluck( 'id' )->toArray(),
							$study->all_tags ? array() : $study->tags_excluded()->get()->pluck( 'id' )->toArray(),
							$study->no_of_new > 0 ? $study->no_of_new : 1000,
							$study->no_to_revise > 0 ? $study->no_to_revise : 1000,
							$study->no_on_hold > 0 ? $study->no_on_hold : 1000,
							$deck->id,
							0
						);

//					$topic->count_new_cards = count( $cards_to_study['new_cards'] );
						// set new counts.
						$deck->count_new_cards       += count( $cards_to_study['new_cards'] );
						$deck_group->count_new_cards += count( $cards_to_study['new_cards'] );
						// set revision counts.
						$deck->count_revision       += count( $cards_to_study['revision_cards'] );
						$deck_group->count_revision += count( $cards_to_study['revision_cards'] );
						// set on hold counts.
						$deck->count_on_hold       += count( $cards_to_study['on_hold_cards'] );
						$deck_group->count_on_hold += count( $cards_to_study['on_hold_cards'] );

						$deck->cards = $cards_to_study['all_cards'];
					} else {
						$deck->cards = array();
					}
//					$cards       = self::get_cards_to_study_in_study(
//						$study,
//						$interested_card_group_ids,
//						$user_cards_not_studied['card_ids'],
//						$user_cards_answered['revision_and_due_ids'],
//						$user_cards_answered['on_hold_and_due_ids']
//					);


				} else {
					$deck->cards = array();
				}

				foreach ( $deck->topics as $topic ) {
					$topic->count_new_cards = 0;
					$topic->count_revision  = 0;
					$topic->count_on_hold   = 0;
					$topic->in_user_cards   = in_array( $topic->id, $in_user_cards_detail['topic_ids'] );

					$study = $topic->studies->first();

					if ( $study instanceof Study ) {
						if ( ! $study->active ) {
							continue;
						}
						$cards_to_study = self::get_study_cards(
							$user_id,
							$study,
							$study->all_tags ? array() : $study->tags()->get()->pluck( 'id' )->toArray(),
							$study->all_tags ? array() : $study->tags_excluded()->get()->pluck( 'id' )->toArray(),
							$study->no_of_new > 0 ? $study->no_of_new : 1000,
							$study->no_to_revise > 0 ? $study->no_to_revise : 1000,
							$study->no_on_hold > 0 ? $study->no_on_hold : 1000,
							0,
							$topic->id
						);

						// set new counts.
						$topic->count_new_cards      = count( $cards_to_study['new_cards'] );
						$deck->count_new_cards       += count( $cards_to_study['new_cards'] );
						$deck_group->count_new_cards += count( $cards_to_study['new_cards'] );
						// set revision counts.
						$topic->count_revision      = count( $cards_to_study['revision_cards'] );
						$deck->count_revision       += count( $cards_to_study['revision_cards'] );
						$deck_group->count_revision += count( $cards_to_study['revision_cards'] );
						// set on hold counts.
						$topic->count_on_hold      = count( $cards_to_study['on_hold_cards'] );
						$deck->count_on_hold       += count( $cards_to_study['on_hold_cards'] );
						$deck_group->count_on_hold += count( $cards_to_study['on_hold_cards'] );

						$topic->cards = $cards_to_study['all_cards'];
					} else {
						$topic->cards = array();
					}
				}
			}
		}

		$user_cards = UserCard::query()
		                      ->where( 'user_id', '=', $user_id )
		                      ->get()->count();

		return array(
			'deck_groups'      => $deck_groups->all(),
			'user_cards_count' => $user_cards,
		);
	}

	public static function get_user_cards_to_study_old_1( int $user_id ): array {
//		$today_datetime = Common::getDateTime();
//		$today_date     = Common::get_date();
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
		Initializer::add_debug( $all_user_cards );

		$topic_uncategorized_id = get_uncategorized_topic_id();

		// Remove all card groups in any collection.
		$card_groups_in_collection = CardGroup::get_card_groups_in_any_collections();

		// Get all user studies.
		$user_studies = sp_get_user_studies( $user_id );

		// User un-studied topics.
		$user_cards_answered    = self::get_all_last_answered_user_cards( $user_studies['study_ids'] );
		$user_cards_not_studied = self::get_new_cards_not_answered_but_added( $user_id,
			$user_cards_answered['card_ids'] );

		$card_groups = CardGroup
			::query()
			// Exclude all card groups in any collection.
			->whereNotIn( 'id', $card_groups_in_collection['card_group_ids'] )
			// Exclude card groups in uncategorized topic.
			->where( 'topic_id', '!=', $topic_uncategorized_id )
			// Include only card groups in the user cards
			->whereIn( 'id', $all_user_cards['card_group_ids'] );

		$interested_card_group_ids = $card_groups->get()->pluck( 'id' )->toArray();

		$deck_groups = DeckGroup
			::with(
				array(
					'decks.topics.card_groups.cards',
					// Decks.
					'decks.studies'        => function ( $q ) use ( $user_id ) {
						$q->where( 'user_id', '=', $user_id );
					},
					'decks.studies.deck',
					'decks.studies.topic',
					'decks.studies.tags',
					'decks.studies.tags_excluded',
					'decks.topics.studies' => function ( $q ) use ( $user_id ) {
						$q->where( 'user_id', '=', $user_id );
					},
					// Topics
					'decks.topics.studies.topic',
					'decks.topics.studies.tags',
					'decks.topics.studies.tags_excluded',
					'studies'              => function ( $q ) use ( $user_id ) {
						$q->where( 'user_id', '=', $user_id );
					},
					'studies.tags',
					'studies.tags_excluded',
				)
			)
			// Limit to only interested card groups.
			->whereHas(
				'decks.topics.card_groups',
				function ( $query ) use ( $interested_card_group_ids ) {
					$query->whereIn( 'id', $interested_card_group_ids );
				}
			)->get();

		// encode all questions in deck groups.
		foreach ( $deck_groups as $deck_group ) {
			foreach ( $deck_group->decks as $deck ) {
//				if ( 37 !== $deck->id ) {
//					continue; // todo its for test, remove;
//				}
				$study = $deck->studies->first();
				if ( $study instanceof Study ) {
					$cards       = self::get_cards_to_study_in_study(
						$study,
						$interested_card_group_ids,
						$user_cards_not_studied['card_ids'],
						$user_cards_answered['revision_and_due_ids'],
						$user_cards_answered['on_hold_and_due_ids']
					);
					$deck->cards = $cards;
					Initializer::add_debug_with_key(
						'getting_cards_for_deck__' . $deck->name,
						array(
//							'cards' => $cards,
							'user_cards_not_studied' => $user_cards_not_studied,
							'user_cards_answered'    => $user_cards_answered,
						)
					);
				} else {
					$deck->cards = array();
				}

				foreach ( $deck->topics as $topic ) {
					$study = $topic->studies->first();

					if ( $study instanceof Study ) {
						if ( ! $study->active ) {
							continue;
						}
						$cards          = self::get_cards_to_study_in_study(
							$study,
							$interested_card_group_ids,
							$user_cards_not_studied['card_ids'],
							$user_cards_answered['revision_and_due_ids'],
							$user_cards_answered['on_hold_and_due_ids']
						);
						$cards_to_study = self::get_study_cards(
							$user_id,
							$study,
							$study->all_tags ? array() : $study->tags()->get()->pluck( 'id' )->toArray(),
							$study->all_tags ? array() : $study->tags_excluded()->get()->pluck( 'id' )->toArray(),
							$study->no_of_new > 0 ? $study->no_of_new : 1000,
							$study->no_to_revise > 0 ? $study->no_to_revise : 1000,
							$study->no_on_hold > 0 ? $study->no_on_hold : 1000,
							0,
							$topic->id
						);
						$topic->cards   = $cards;
						// todo fill
//                        $topic->new_cards = [];
//                        $topic->revision_cards = [];
//                        $topic->on_hold_cards = [];
					} else {
						$topic->cards = array();
					}
				}
			}
		}

		return array(
			'deck_groups'                       => $deck_groups->all(),
			'new_card_ids'                      => $user_cards_not_studied['card_ids'],
			'on_hold_card_ids'                  => $user_cards_answered['on_hold_and_due_ids'],
			'revision_card_ids'                 => $user_cards_answered['revision_and_due_ids'],
			'user_card_group_ids_being_studied' => $all_user_cards['card_group_ids'],
			'debug'                             => Initializer::$debug,
			'study_ids'                         => $user_studies['study_ids'],
		);
	}


	/**
	 * Used for when both topics and decks can be studied.
	 *
	 * @param int $user_id
	 *
	 * @return array|array[]
	 */
	public static function get_user_cards_to_study__( int $user_id ): array {
//		$today_datetime = Common::getDateTime();
//		$today_date     = Common::get_date();
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
		Initializer::add_debug( $all_user_cards );

		$topic_uncategorized_id = get_uncategorized_topic_id();

		// Remove all card groups in any collection.
		$card_groups_in_collection = CardGroup::get_card_groups_in_any_collections();

		// Get all user studies.
		$user_studies = sp_get_user_studies( $user_id );

		// User un-studied topics.
		$user_cards_answered    = self::get_all_last_answered_user_cards( $user_studies['study_ids'] );
		$user_cards_not_studied = self::get_new_cards_not_answered_but_added( $user_id,
			$user_cards_answered['card_ids'] );

		$card_groups = CardGroup
			::query()
			// Exclude all card groups in any collection.
			->whereNotIn( 'id', $card_groups_in_collection['card_group_ids'] )
			// Exclude card groups in uncategorized topic.
			->where( 'topic_id', '!=', $topic_uncategorized_id )
			// Include only card groups in the user cards
			->whereIn( 'id', $all_user_cards['card_group_ids'] );

		$interested_card_group_ids = $card_groups->get()->pluck( 'id' )->toArray();

		$deck_groups = DeckGroup
			::with(
				array(
					'decks.topics.card_groups.cards',
					// Decks.
					'decks.studies'        => function ( $q ) use ( $user_id ) {
						$q->where( 'user_id', '=', $user_id );
					},
					'decks.studies.deck',
					'decks.studies.topic',
					'decks.studies.tags',
					'decks.studies.tags_excluded',
					'decks.topics.studies' => function ( $q ) use ( $user_id ) {
						$q->where( 'user_id', '=', $user_id );
					},
					// Topics
					'decks.topics.studies.topic',
					'decks.topics.studies.tags',
					'decks.topics.studies.tags_excluded',
					'studies'              => function ( $q ) use ( $user_id ) {
						$q->where( 'user_id', '=', $user_id );
					},
					'studies.tags',
					'studies.tags_excluded',
				)
			)
			// Limit to only interested card groups.
			->whereHas(
				'decks.topics.card_groups',
				function ( $query ) use ( $interested_card_group_ids ) {
					$query->whereIn( 'id', $interested_card_group_ids );
				}
			)->get();

		// encode all questions in deck groups.
		foreach ( $deck_groups as $deck_group ) {
			foreach ( $deck_group->decks as $deck ) {
//				if ( 37 !== $deck->id ) {
//					continue; // todo its for test, remove;
//				}
				$study = $deck->studies->first();
				if ( $study instanceof Study ) {
					$cards       = self::get_cards_to_study_in_study(
						$study,
						$interested_card_group_ids,
						$user_cards_not_studied['card_ids'],
						$user_cards_answered['revision_and_due_ids'],
						$user_cards_answered['on_hold_and_due_ids']
					);
					$deck->cards = $cards;
					Initializer::add_debug_with_key(
						'getting_cards_for_deck__' . $deck->name,
						array(
//							'cards' => $cards,
							'user_cards_not_studied' => $user_cards_not_studied,
							'user_cards_answered'    => $user_cards_answered,
						)
					);
				} else {
					$deck->cards = array();
				}
				foreach ( $deck->topics as $topic ) {
					$study = $topic->studies->first();

					if ( $study instanceof Study ) {
						$cards        = self::get_cards_to_study_in_study(
							$study,
							$interested_card_group_ids,
							$user_cards_not_studied['card_ids'],
							$user_cards_answered['revision_and_due_ids'],
							$user_cards_answered['on_hold_and_due_ids']
						);
						$topic->cards = $cards;
					} else {
						$topic->cards = array();
					}
				}
			}
		}

		return array(
			'deck_groups'                       => $deck_groups->all(),
			'new_card_ids'                      => $user_cards_not_studied['card_ids'],
			'on_hold_card_ids'                  => $user_cards_answered['on_hold_and_due_ids'],
			'revision_card_ids'                 => $user_cards_answered['revision_and_due_ids'],
			'user_card_group_ids_being_studied' => $all_user_cards['card_group_ids'],
			'debug'                             => Initializer::$debug,
			'study_ids'                         => $user_studies['study_ids'],
		);
	}

	public static function get_groups_decks_and_topic_ids_in_user_cards( int $user_id ): array {
		$table_names               = self::get_table_names();
		$tb_cards                  = $table_names['tb_cards'];
		$tb_card_groups            = $table_names['tb_card_groups'];
		$tb_user_cards             = $tb_user_cards = $table_names['tb_user_cards'];
		$tb_study                  = $tb_study = $table_names['tb_study'];
		$tb_decks                  = $tb_decks = $table_names['tb_decks'];
		$tb_deck_groups            = $table_names['tb_deck_groups'];
		$tb_topics                 = $table_names['tb_topics'];
		$tb_collections            = $table_names['tb_collections'];
		$tb_answered               = $table_names['tb_answered'];
		$tb_tags                   = $table_names['tb_tags'];
		$tb_taggable               = $table_names['tb_taggable'];
		$tb_taggable_excluded      = $table_names['tb_taggable_excluded'];
		$taggable_type_card_groups = 'Model\\\CardGroup';
		$taggable_type_study       = 'Model\Study';

		$sql_user_card_groups = "
			SELECT cg_uc.card_group_id from {$tb_user_cards} as cg_uc
			WHERE cg_uc.user_id IN ({$user_id})
		";

		$sql_user_topics = "
			SELECT t_33.topic_id from {$tb_card_groups} as t_33
			WHERE t_33.id IN ({$sql_user_card_groups})
			AND t_33.topic_id IS NOT NULL
		";

		$sql_user_decks = "
			SELECT d_44.deck_id from {$tb_topics} as d_44
			WHERE d_44.id IN (
			    {$sql_user_topics}
			)
		";

		$sql_user_deck_groups = "
			SELECT dg_55.deck_group_id from {$tb_decks} as dg_55 
			WHERE dg_55.id IN (
			    {$sql_user_decks}
			)
		";

		global $wpdb;

		$result_topics = $wpdb->get_results( $sql_user_topics, ARRAY_A );
		$result_decks  = $wpdb->get_results( $sql_user_decks, ARRAY_A );
		$result_groups = $wpdb->get_results( $sql_user_deck_groups, ARRAY_A );

		$topic_ids = array_map( 'intval', array_column( $result_topics, 'topic_id' ) );
		$deck_ids  = array_map( 'intval', array_column( $result_decks, 'deck_id' ) );
		$group_ids = array_map( 'intval', array_column( $result_groups, 'deck_group_id' ) );

		return array(
			'deck_group_ids' => $group_ids,
			'deck_ids'       => $deck_ids,
			'topic_ids'      => $topic_ids,
		);
	}

	public static function get_table_names() {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$tb_cards             = "{$prefix}sp_cards";
		$tb_card_groups       = "{$prefix}sp_card_groups";
		$tb_user_cards        = "{$prefix}sp_user_cards";
		$tb_study             = "{$prefix}sp_study";
		$tb_decks             = "{$prefix}sp_decks";
		$tb_deck_groups       = "{$prefix}sp_deck_groups";
		$tb_topics            = "{$prefix}sp_topics";
		$tb_collections       = "{$prefix}sp_collections";
		$tb_answered          = "{$prefix}sp_answered";
		$tb_tags              = "{$prefix}sp_tags";
		$tb_taggable          = "{$prefix}sp_taggables";
		$tb_taggable_excluded = "{$prefix}sp_taggables_excluded";

		return array(
			'tb_cards'             => $tb_cards,
			'tb_card_groups'       => $tb_card_groups,
			'tb_user_cards'        => $tb_user_cards,
			'tb_study'             => $tb_study,
			'tb_decks'             => $tb_decks,
			'tb_deck_groups'       => $tb_deck_groups,
			'tb_topics'            => $tb_topics,
			'tb_collections'       => $tb_collections,
			'tb_answered'          => $tb_answered,
			'tb_tags'              => $tb_tags,
			'tb_taggable'          => $tb_taggable,
			'tb_taggable_excluded' => $tb_taggable_excluded
		);

	}

	/**
	 * Used for when both topics and decks can be studied.
	 *
	 * @param int $user_id The user id.
	 * @param array $tag_ids_to_include The tag ids to include. Send empty array to include all.
	 * @param array $tag_ids_to_exclude The tag ids to exclude. When not empty, all  cards will be returned except those that have any of the tags in $tag_ids_to_exclude
	 *
	 * @return array|array[]
	 */
	public static function get_new_user_cards_to_study_new_cards( int $user_id, array $tag_ids_to_include, array $tag_ids_to_exclude ): array {
		$sql = array(
			'select' => array(),
			'where'  => array()
		);

		global $wpdb;
		$prefix                 = $wpdb->prefix;
		$topic_uncategorized_id = get_uncategorized_topic_id();

		$w_cards = '';

		/**
		 * For New Cards
		 * - Get all user cards
		 * - Remove all cards in collection
		 * -
		 */

		$tb_cards             = "{$prefix}sp_cards";
		$tb_card_groups       = "{$prefix}sp_card_groups";
		$tb_user_cards        = "{$prefix}sp_user_cards";
		$tb_study             = "{$prefix}sp_study";
		$tb_decks             = "{$prefix}sp_decks";
		$tb_deck_groups       = "{$prefix}sp_deck_groups";
		$tb_topics            = "{$prefix}sp_topics";
		$tb_collections       = "{$prefix}sp_collections";
		$tb_answered          = "{$prefix}sp_answered";
		$tb_tags              = "{$prefix}sp_tags";
		$tb_taggable          = "{$prefix}sp_taggables";
		$tb_taggable_excluded = "{$prefix}sp_taggables_excluded";

		$sql_all_user_cards = "";

		// Exclude all card in the uncategorized topics.
		$sql_cards_in_uncategorized_topic = "
			SELECT c_uc.id from {$tb_cards} as c_uc
			WHERE c_uc.card_group_id IN (
			  SELECT cg_uc.id from {$tb_card_groups} as cg_uc
			  WHERE cg_uc.topic_id = {$topic_uncategorized_id}
			)	 
		";

		$result_cards_in_uncategorized_topics = $wpdb->get_results( $sql_cards_in_uncategorized_topic, ARRAY_A );

		// Exclude all cards in collections.
		$sql_exclude_collection = " 
			SELECT id from {$tb_cards} as c_cl
			WHERE c_cl.card_group_id IN (
				 SELECT id from {$tb_card_groups} 
				 WHERE collection_id > 0
			)
		";

		$result_cards_in_collections = $wpdb->get_results( $sql_exclude_collection, ARRAY_A );

		// Get new cards.
		$sql_new_cards = "
			SELECT id from {$tb_cards} as c
			WHERE c.card_group_id IN (
				SELECT id from {$tb_card_groups} as cg
				WHERE id IN (
						SELECT card_group_id from {$tb_user_cards} as uc
						WHERE uc.user_id = {$user_id}
				) 
			)
			AND c.id NOT IN (
				SELECT DISTINCT card_id from {$tb_answered} as a
				WHERE a.study_id IN (
					SELECT id FROM {$tb_study} 
					WHERE user_id = {$user_id}
				) 
			)
		";

		$new_results = $wpdb->get_results( $sql_new_cards, ARRAY_A );

		// Get new cards.
		$sql = "
			SELECT * from {$tb_cards} as c1 
			WHERE c1.id NOT IN ($sql_exclude_collection) 
			AND c1.id IN ($sql_new_cards) 
			AND c1.id NOT IN ($sql_cards_in_uncategorized_topic)
		";

//		$sql = "
//			SELECT * from {$tb_cards} as c1
//			WHERE c1.id NOT IN ($sql_exclude_collection)
//			WHERE c1.id IN ($sql_new_cards)
//		";

//		$sql = "
//			SELECT * from {$tb_cards} as c1
//			# WHERE c.id NOT IN ($sql_exclude_collection)
//			WHERE c1.id IN ($sql_new_cards)
//			# WHERE c.topic_id != $topic_uncategorized_id
//		";


		$results = $wpdb->get_results( $sql, ARRAY_A );


		return array();
	}

	/**
	 * Add data to debug array.
	 *
	 * @param array $debug The debug array.
	 * @param string $key Key of one value in $debug.
	 * @param array $data The data to log.
	 *
	 * @return array
	 */
	private static function format_debug_data( array $debug, string $key, array $data ): array {
//		$last_count = count( $debug[ $key ] );
//		$new_data   = array();
//		if ( ! empty( $debug[ $key ] ) ) {
//			$new_data = $data;
//		} else {
//			$new_data = $data;
//		}
//		$debug[ $key ][ $last_count + 1 . '_log' ] = $data;
		$debug[ $key ] = $data;

		$count_sequential                                                   = count( $debug['all_logs_sequential'] );
		$debug['all_logs_sequential'][ $count_sequential + 1 . '_' . $key ] = $data;


		return $debug;
	}

	public static function get_study_cards(
		int $study_user_id,
		Study $study,
		array $tags_to_include,
		array $tags_to_exclude,
		int $no_of_new,
		int $no_of_revision,
		int $no_hold,
		int $deck_id = 0,
		int $topic_id = 0
	) {
		$study_id                   = $study->id;
		$study_no_of_new            = $study->no_of_new;
		$study_no_to_revise         = $study->no_to_revise;
		$study_no_on_hold           = $study->no_on_hold;
		$study_to_array             = $study->toArray();
		$today_datetime             = Common::getDateTime();
		$today_date                 = Common::get_date();
		$today_date_with_empty_time = Common::get_date_with_empty_time( false );

		if ( $study->study_all_new ) {
			$study_no_of_new = 10000;
		}
		if ( $study->revise_all ) {
			$study_no_to_revise = 10000;
		}
		if ( $study->study_all_on_hold ) {
			$study_no_on_hold = 10000;
		}


		$debug = array(
			'all_logs_sequential'          => array(),
			'variables'                    => array(),
			'uncategorized_topic'          => array(),
			'collections'                  => array(),
			'study_to_array'               => $study_to_array,
			'study_id'                     => $study_id,
			//
			'user_cards_groups'            => array(),
			'user_cards'                   => array(),
			'user_studies'                 => array(),
			//
			'topic_cards'                  => array(),
			'deck_cards'                   => array(),
			//
			'cards_answered_distinct'      => array(),
			'cards_not_answered'           => array(),
			//
			'included_tags_required'       => false,
			'included_tags'                => array(),
			'included_tags_taggable'       => array(),
			//
			'excluded_tags_required'       => false,
			'excluded_tags'                => array(),
			'excluded_tags_taggable'       => array(),
			//
			'due_and_in_revision'          => array(),
			'due_and_on_hold'              => array(),
			'due_till_today'               => array(),
			//
			'count_studied_today_new'      => array(),
			'count_studied_today_revision' => array(),
			'count_studied_today_on_hold'  => array(),
			//
			'answered_as_new_today'        => array(),
			'answered_as_revision_today'   => array(),
			'answered_as_on_hold_today'    => array(),
			//
			'result_cards_new'             => array(),
			'result_cards_revision'        => array(),
			'result_cards_on_hold'         => array(),
			'sp_in_sql_mode'               => sp_in_sql_mode()
		);

		// <editor-fold desc="Variables" >
		/**
		 * For All
		 * - Exclude tags
		 * - Include tags
		 * - Cards must be in user cards
		 * -
		 */
		global $wpdb;
		$wpdb->show_errors();
		$prefix                 = $wpdb->prefix;
		$topic_uncategorized_id = get_uncategorized_topic_id();

		$w_cards = '';

		/**
		 * For New Cards
		 * - Get all user cards
		 * - Remove all cards in collection
		 * -
		 */

		$table_names               = self::get_table_names();
		$tb_cards                  = $table_names['tb_cards'];
		$tb_card_groups            = $table_names['tb_card_groups'];
		$tb_user_cards             = $tb_user_cards = $table_names['tb_user_cards'];
		$tb_study                  = $tb_study = $table_names['tb_study'];
		$tb_decks                  = $tb_decks = $table_names['tb_decks'];
		$tb_deck_groups            = $table_names['tb_deck_groups'];
		$tb_topics                 = $table_names['tb_topics'];
		$tb_collections            = $table_names['tb_collections'];
		$tb_answered               = $table_names['tb_answered'];
		$tb_tags                   = $table_names['tb_tags'];
		$tb_taggable               = $table_names['tb_taggable'];
		$tb_taggable_excluded      = $table_names['tb_taggable_excluded'];
		$taggable_type_card_groups = 'Model\\\CardGroup';
		$taggable_type_study       = 'Model\Study';

		// </editor-fold desc="Variables" >

		// <editor-fold desc="Uncategorized Topics" >

		// Exclude all card in the uncategorized topics.
		$sql_cards_in_uncategorized_topic = "
			SELECT c_uc.id from {$tb_cards} as c_uc
			WHERE c_uc.card_group_id IN (
			  SELECT cg_uc.id from {$tb_card_groups} as cg_uc
			  WHERE cg_uc.topic_id = {$topic_uncategorized_id}
			)	 
		";
		$debug                            = self::maybe_execute_query(
			$sql_cards_in_uncategorized_topic,
			'id',
			$debug,
			'uncategorized_topic',
		);

		// </editor-fold desc="Uncategorized Topics" >

		// <editor-fold desc="Collections" >
		$sql_exclude_collection = " 
			SELECT id from {$tb_cards} as c_cl
			WHERE c_cl.card_group_id IN (
				 SELECT id from {$tb_card_groups} 
				 WHERE collection_id > 0
			)
		";
		$debug                  = self::maybe_execute_query(
			$sql_exclude_collection,
			'id',
			$debug,
			'collections',
		);

		// </editor-fold desc="Collections" >


		// <editor-fold desc="User Cards Groups " >
		$sql_user_card_groups = "
			SELECT id from {$tb_card_groups} as cg
			WHERE id IN (
				 SELECT card_group_id from {$tb_user_cards} as uc 
				 WHERE uc.user_id = $study_user_id 
			)
		";
		$debug                = self::maybe_execute_query(
			$sql_user_card_groups,
			'id',
			$debug,
			'user_cards_groups',
		);
		// </editor-fold desc="User Cards Groups " >


		// <editor-fold desc="User Cards " >

		// Get cards in user cards.
		$sql_user_cards = "
			SELECT id from {$tb_cards} as c
			WHERE c.card_group_id IN (
				SELECT id from {$tb_card_groups} as cg
				WHERE id IN (
						{$sql_user_card_groups}
				)
			)
		";
		$debug          = self::maybe_execute_query(
			$sql_user_cards,
			'id',
			$debug,
			'user_cards',
		);

		// </editor-fold desc="User Cards " >

		// <editor-fold desc="Topic Cards" >

		// Get cards in topic.
		if ( ! empty( $study->topic_id ) ) {
			$sql_cards_in_topic = "
				SELECT id FROM $tb_cards as c_t
				WHERE c_t.card_group_id IN (
					SELECT id from {$tb_card_groups} as cg_t 
					WHERE cg_t.topic_id = $topic_id	
				) 
		";
			$debug              = self::maybe_execute_query(
				$sql_cards_in_topic,
				'id',
				$debug,
				'topic_cards',
			);
		}

		// </editor-fold desc="Topic Cards" >

		// <editor-fold desc="Deck Cards" >

		if ( ! empty( $study->deck_id ) ) {
			$sql_cards_in_deck = "
				SELECT id FROM $tb_cards as c_t
				WHERE c_t.card_group_id IN (
				    SELECT id from {$tb_card_groups} as cg_t
				    WHERE cg_t.topic_id IN (
						SELECT id from {$tb_topics} as t_d
						WHERE t_d.deck_id = $deck_id
					)
			   )
		";
			$debug             = self::maybe_execute_query(
				$sql_cards_in_deck,
				'id',
				$debug,
				'deck',
			);
		}

		// </editor-fold desc="Deck Cards" >

		// <editor-fold desc="Included Tags">

		if ( ! empty( $tags_to_include ) ) {
			$debug['included_tags_required'] = true;

			// Get card groups in Included_tags.
			$implode_tags                     = '(' . implode( ',', $tags_to_include ) . ')';
			$sql_cards_groups_in_include_tags = "
				 SELECT taggable_id from {$tb_taggable} as tga_tint
				 WHERE tga_tint.tag_id IN {$implode_tags} 	
				 AND tga_tint.taggable_type = '{$taggable_type_card_groups}'
			";
			$debug                            = self::execute_query(
				$sql_cards_groups_in_include_tags,
				'taggable_id',
				$debug,
				'included_tags_taggable',
			);

			$sql_cards_in_tags_to_include = "
				SELECT c_in.id from $tb_cards  as c_in
				WHERE c_in.card_group_id IN (
					 {$sql_cards_groups_in_include_tags}
				)
			";
			$debug                        = self::execute_query(
				$sql_cards_in_tags_to_include,
				'id',
				$debug,
				'included_tags',
			);
		}

		// </editor-fold desc="Included Tags">

		// <editor-fold desc="Excluded Tags">
		// Excluded Tags.
		if ( empty( $tags_to_include ) && ! empty( $tags_to_exclude ) ) {
			$debug['excluded_tags_required'] = true;

			// Get card groups in Included_tags.
			$implode_excluded_tags            = '(' . implode( ',', $tags_to_exclude ) . ')';
			$sql_all_cards_groups_in_excluded = "
				 SELECT taggable_id from {$tb_taggable} as tgae_tint
				 WHERE tgae_tint.tag_id IN {$implode_excluded_tags} 	
				 AND tgae_tint.taggable_type = '{$taggable_type_card_groups}'
				 AND tgae_tint.taggable_id IN (
				 	${sql_user_card_groups}
				)
			";
			$debug                            = self::execute_query(
				$sql_all_cards_groups_in_excluded,
				'taggable_id',
				$debug,
				'excluded_tags_taggable',
			);

			// So these are the cards in the excluded tags.
			$sql_cards_in_tags_to_exclude = "
				SELECT id from $tb_cards  as c_in 
				WHERE c_in.card_group_id IN (
						{$sql_all_cards_groups_in_excluded}
				)
			";
			$debug                        = self::execute_query(
				$sql_cards_in_tags_to_exclude,
				'id',
				$debug,
				'excluded_tags',
			);
		}

		// </editor-fold desc="Excluded Tags">

		// <editor-fold desc="User Studies">

		$sql_user_studies = "
		   SELECT s_u_s.id FROM {$tb_study} as s_u_s
            WHERE s_u_s.user_id = {$study_user_id}
		";
		$debug            = self::execute_query(
			$sql_user_studies,
			'id',
			$debug,
			'user_studies'
		);

		// </editor-fold desc="User Studies">

		// <editor-fold desc="Answered Cards' answer id Distinct by card id">

		$sql_last_answer_ids_by_card_id = "
            SELECT MAX(id) FROM {$tb_answered} as a_last
            WHERE a_last.study_id IN (
				{$sql_user_studies}
            )
            GROUP BY a_last.card_id
		";
		$debug                          = self::maybe_execute_query(
			$sql_last_answer_ids_by_card_id,
			'id',
			$debug,
			'cards_answered'
		);

		// </editor-fold desc="Answered Cards ">

		// <editor-fold desc="Answered Cards Distinct">

		// Distinct answered card_ids
//		$sql_distinct_answered_cards = "
//			SELECT DISTINCT card_id from {$tb_answered} as a
//			where a.study_id = {$study_id}
//			ORDER BY id DESC
//		";
		$sql_distinct_answered_cards = "
			SELECT card_id from {$tb_answered} as a_answered
			WHERE a_answered.study_id IN(
				{$sql_user_studies}
			)
			AND a_answered.id IN (
				{$sql_last_answer_ids_by_card_id}
			)
		";
		$debug                       = self::maybe_execute_query(
			$sql_distinct_answered_cards,
			'card_id',
			$debug,
			'cards_answered_distinct'
		);

		// </editor-fold desc="Answered Cards ">

		// <editor-fold desc="Cards Not in Answered">

		$sql_not_answered = "
			SELECT id from {$tb_cards} as c_new
			WHERE c_new.id NOT IN (
				{$sql_distinct_answered_cards}
			)
		";
		$debug            = self::maybe_execute_query(
			$sql_not_answered,
			'id',
			$debug,
			'cards_not_answered'
		);

		// </editor-fold desc="Cards Not answered">

		// <editor-fold desc="Cards Due in Revision">
		$sql_cards_due_in_revision = "
			SELECT a_due_rev.card_id from {$tb_answered} as a_due_rev 
			WHERE a_due_rev.id IN (
				{$sql_last_answer_ids_by_card_id}
			) 
			AND 1 = 1
			AND a_due_rev.grade != 'hold'   
			AND a_due_rev.next_due_at < '{$today_datetime}'
		";
		$debug                     = self::maybe_execute_query(
			$sql_cards_due_in_revision,
			'card_id',
			$debug,
			'due_and_in_revision'
		);

		// </editor-fold desc="Cards Due in Revision">

		// <editor-fold desc="Cards Due and on hold">
		$sql_cards_due_and_on_hold = " 
			SELECT a_due_hold.card_id from {$tb_answered} as a_due_hold 
			WHERE a_due_hold.id IN (
				{$sql_last_answer_ids_by_card_id}
			)
			AND a_due_hold.grade = 'hold'
			AND a_due_hold.next_due_at < '{$today_datetime}'
		";
		$debug                     = self::maybe_execute_query(
			$sql_cards_due_and_on_hold,
			'card_id',
			$debug,
			'due_and_on_hold'
		);

		// </editor-fold desc="Cards Due and on Hold">

		// <editor-fold desc="Answered as New Today">
		$sql_cards_answered_as_new_today                               = "
			SELECT a_new.card_id from {$tb_answered} as a_new 
			WHERE a_new.study_id = {$study_id} 
			AND a_new.created_at >= '{$today_date_with_empty_time}' 
			AND a_new.created_at < ('{$today_date_with_empty_time}' + INTERVAL 1 DAY)
			AND a_new.answered_as_new = 1
		";
		$debug                                                         = self::execute_query(
			$sql_cards_answered_as_new_today,
			'card_id',
			$debug,
			'answered_as_new_today'
		);
		$count_answered_as_new_today                                   = count( $debug['answered_as_new_today']['sql_result'] );
		$limit_new                                                     = $study_no_of_new - $count_answered_as_new_today;
		$debug['answered_as_new_today']['study_no_of_new']             = $study_no_of_new;
		$debug['answered_as_new_today']['count_answered_as_new_today'] = $count_answered_as_new_today;
		$debug['answered_as_new_today']['limit_new']                   = $limit_new;

		// </editor-fold desc="Answered As New Today">

		// <editor-fold desc="Answered as Revision Today">
		$sql_cards_answered_as_revision_today                                    = "
			SELECT a_new.card_id from {$tb_answered} as a_new 
			WHERE a_new.study_id = {$study_id} 
			AND a_new.created_at >= '{$today_date_with_empty_time}'  
			AND a_new.created_at < ('{$today_date_with_empty_time}' + INTERVAL 1 DAY)
			AND a_new.answered_as_revised = 1
		";
		$debug                                                                   = self::execute_query(
			$sql_cards_answered_as_revision_today,
			'card_id',
			$debug,
			'answered_as_revision_today'
		);
		$count_answered_as_revision_today                                        = count( $debug['answered_as_revision_today']['sql_result'] );
		$limit_revise                                                            = $study_no_to_revise - $count_answered_as_revision_today;
		$debug['answered_as_revision_today']['study_no_to_revise']               = $study_no_to_revise;
		$debug['answered_as_revision_today']['count_answered_as_revision_today'] = $count_answered_as_revision_today;
		$debug['answered_as_revision_today']['limit_revise']                     = $limit_revise;

		// </editor-fold desc="Answered As Revision Today">

		// <editor-fold desc="Answered as On Hold Today">
		$sql_cards_answered_as_on_hold_today                                   = "
			SELECT a_new.card_id from {$tb_answered} as a_new 
			WHERE a_new.study_id = {$study_id} 
			AND a_new.created_at >= '{$today_date_with_empty_time}'  
			AND a_new.created_at < ('{$today_date_with_empty_time}' + INTERVAL 1 DAY)
			AND a_new.answered_as_on_hold = 1
		";
		$debug                                                                 = self::execute_query(
			$sql_cards_answered_as_on_hold_today,
			'card_id',
			$debug,
			'answered_as_on_hold_today'
		);
		$count_answered_as_on_hold_today                                       = count( $debug['answered_as_on_hold_today']['sql_result'] );
		$limit_on_hold                                                         = $study_no_on_hold - $count_answered_as_on_hold_today;
		$debug['answered_as_on_hold_today']['study_no_on_hold']                = $study_no_on_hold;
		$debug['answered_as_on_hold_today']['count_answered_as_on_hold_today'] = $count_answered_as_on_hold_today;
		$debug['answered_as_on_hold_today']['limit_on_hold']                   = $limit_on_hold;

		// </editor-fold desc="Answered As Oh Hold Today">

		// <editor-fold desc="New Cards">
		$query_sql_new = "
			SELECT c1.id from {$tb_cards} as c1 
			WHERE c1.id IN ($sql_user_cards) 
			  -- Collection
			AND c1.id NOT IN ($sql_exclude_collection) 
			  -- Uncategorized Topic
			AND c1.id NOT IN ($sql_cards_in_uncategorized_topic) 
			  -- Not answered
			AND c1.id IN ($sql_not_answered) 
			";
		$query_sql_new .= sprintf( '
					-- Topic
					%1$s 
					-- Deck
					%2$s 
					-- Included Tags
					%3$s 
					-- Excluded Tags
					%4$s 
					-- Limit
					LIMIT %5$d 
					',
			! empty( $sql_cards_in_topic ) ? "AND c1.id IN ($sql_cards_in_topic)" : '',
			! empty( $sql_cards_in_deck ) ? "AND c1.id IN ($sql_cards_in_deck)" : '',
			! empty( $sql_cards_in_tags_to_include ) ? "AND c1.id IN ($sql_cards_in_tags_to_include)" : '',
			! empty( $sql_cards_in_tags_to_exclude ) ? "AND c1.id NOT IN ($sql_cards_in_tags_to_exclude)" : '',
			$limit_new
		);
		$debug         = self::execute_query(
			$query_sql_new,
			'id',
			$debug,
			'result_cards_new'
		);

		// </editor-fold desc="New Cards">

		// <editor-fold desc="Revision Cards">

		$result_sql_revision = "
			SELECT * from {$tb_cards} as c1 
			WHERE c1.id IN ($sql_user_cards) 
			AND c1.id NOT IN ($sql_exclude_collection) 
			AND c1.id NOT IN ($sql_cards_in_uncategorized_topic) 
			AND c1.id IN ($sql_cards_due_in_revision) 
			 ";
		$result_sql_revision .= sprintf( '
					%1$s 
					%2$s 
					%3$s 
					%4$s 
					LIMIT %5$d
				',
			! empty( $sql_cards_in_topic ) ? "AND c1.id IN ($sql_cards_in_topic)" : '',
			! empty( $sql_cards_in_deck ) ? "AND c1.id IN ($sql_cards_in_deck)" : '',
			! empty( $sql_cards_in_tags_to_include ) ? "AND c1.id IN ($sql_cards_in_tags_to_include)" : '',
			! empty( $sql_cards_in_tags_to_exclude ) ? "AND c1.id NOT IN ($sql_cards_in_tags_to_exclude)" : '',
			$limit_revise
		);
		$debug               = self::execute_query(
			$result_sql_revision,
			'id',
			$debug,
			'result_cards_revision'
		);
		// </editor-fold desc="Revision Cards">

		// <editor-fold desc="On Hold Cards">

		$result_sql_on_hold = "
			SELECT * from {$tb_cards} as c1 
			WHERE c1.id IN ($sql_user_cards) 
			AND c1.id NOT IN ($sql_exclude_collection) 
			AND c1.id NOT IN ($sql_cards_in_uncategorized_topic) 
			AND c1.id IN ($sql_cards_due_and_on_hold) 
			
		";
		$result_sql_on_hold .= sprintf( '
			%1$s 
			%2$s 
			%3$s 
			%4$s 
			LIMIT %5$d
			',
			! empty( $sql_cards_in_topic ) ? "AND c1.id IN ($sql_cards_in_topic)" : '',
			! empty( $sql_cards_in_deck ) ? "AND c1.id IN ($sql_cards_in_deck)" : '',
			! empty( $sql_cards_in_tags_to_include ) ? "AND c1.id IN ($sql_cards_in_tags_to_include)" : '',
			! empty( $sql_cards_in_tags_to_exclude ) ? "AND c1.id NOT IN ($sql_cards_in_tags_to_exclude)" : '',
			$limit_on_hold
		);
		$debug              = self::execute_query(
			$result_sql_on_hold,
			'id',
			$debug,
			'result_cards_on_hold'
		);

		// </editor-fold desc="On Hold Cards">

		$args_new  = array_values( array_column( $debug['result_cards_new']['sql_result'], 'id' ) );
		$new_cards = Card::query()->with( [
			'card_group',
			'answer_log',
		] )->whereIn( 'id', $args_new )->get();
		$new_cards = self::parse_image_and_table_cards( $new_cards );

		$args_revision  = array_values( array_column( $debug['result_cards_revision']['sql_result'], 'id' ) );
		$revision_cards = Card::query()->with( [
			'card_group',
			'answer_log',
		] )->whereIn( 'id', $args_revision )->get();
		$revision_cards = self::parse_image_and_table_cards( $revision_cards );

		$args_on_hold  = array_values( array_column( $debug['result_cards_on_hold']['sql_result'], 'id' ) );
		$on_hold_cards = Card::query()->with( [
			'card_group',
			'answer_log',
		] )->whereIn( 'id', $args_on_hold )->get();
		$on_hold_cards = self::parse_image_and_table_cards( $on_hold_cards );

//		$new_cards_array = $new_cards->toArray();

		$all_cards = $new_cards->merge( $revision_cards );
		$all_cards = $all_cards->shuffle();


		$ret = array(
			'new_cards'      => $new_cards,
			'revision_cards' => $revision_cards,
			'on_hold_cards'  => $on_hold_cards,
			'all_cards'      => $all_cards,
		);

		Initializer::add_debug_with_key( 'user_study_cards', $debug );

		$wpdb->hide_errors();

		return $ret;
	}

	/**
	 * Convert image and table cards' questions and answers to valid json.
	 *
	 * @param Collection $cards
	 *
	 * @return Collection
	 */
	public static function parse_image_and_table_cards( Collection $cards ): Collection {
		$collection = new Collection();
		foreach ( $cards as $card ) {
			$card_type = $card->card_group->card_type;
			if ( in_array( $card_type, array( 'table', 'image' ) ) ) {
				if ( ! is_array( $card->answer ) ) {
					$card->answer = json_decode( $card->answer );
				}
				if ( ! is_array( $card->question ) ) {
					$card->question = json_decode( $card->question );
				}
			}

			$answer_log = $card->answer_log;
			if ( ! empty( $answer_log ) ) {
				if ( in_array( $card_type, array( 'table', 'image' ) ) ) {
					if ( ! is_array( $answer_log->answer ) ) {
						$answer_log->answer = json_decode( $answer_log->answer );
					}
					if ( ! is_array( $answer_log->question ) ) {
						$answer_log->question = json_decode( $answer_log->question );
					}
				}
			}

//			$all_cards[] = $card;
			$collection->push( $card );
		}

		return $collection;
	}

	/**
	 * Get cards to study in study.
	 *
	 * @param Study $study The study.
	 * @param array $interested_card_group_ids_for_topic_or_deck The interested card group ids for topic or deck.
	 *
	 * @return Card[]
	 */
	public
	static function get_cards_to_study_in_study(
		Study $study,
		array $interested_card_group_ids_for_topic_or_deck,
		array $user_cards_not_studied_ids,
		array $user_cards_in_revision_and_due_ids,
		array $user_cards_on_hold_and_due_ids
	): array {
//		$study             = Study::find( $study_id );
		$today_date          = Common::get_date();
		$study_studied_today = self::get_all_last_answered_user_cards(
			array( $study->id ),
			$today_date
		);
		$deck_id             = $study->deck_id;
		$topic_id            = $study->topic_id;
		$tags                = array();
		$tags_excluded       = array();
		$add_all_tags        = $study->all_tags;
		$study_all_new       = $study->study_all_new;
		$revise_all          = $study->revise_all;
		$study_all_on_hold   = $study->study_all_on_hold;
		$no_of_new           = $study_all_new ? 10000 : $study->no_of_new;
		$no_on_hold          = $study_all_on_hold ? 10000 : $study->no_on_hold;
		$no_of_revision      = $revise_all ? 10000 : $study->no_to_revise;
		if ( ! $add_all_tags ) {
			$tags          = $study->tags->pluck( 'id' );
			$tags_excluded = $study->tagsExcluded->pluck( 'id' );
		}
		// Minus those answered today.
		$no_of_new      -= count( $study_studied_today['answered_as_new_ids'] );
		$no_on_hold     -= count( $study_studied_today['on_hold'] );
		$no_of_revision -= count( $study_studied_today['revision'] );

		// No negative numbers, make it 0.
		$no_of_new      = max( $no_of_new, 0 );
		$no_on_hold     = max( $no_on_hold, 0 );
		$no_of_revision = max( $no_of_revision, 0 );

//		if ( 27 === $study->id ) {
//
//			Common::send_error( array(
//				__METHOD__,
//				'study'                      => $study,
//				'no_of_new'                  => $no_of_new,
//				'no_on_hold'                 => $no_on_hold,
//				'no_of_revision'             => $no_of_revision,
//				'user_cards_not_studied_ids' => $user_cards_not_studied_ids,
//			) );
//		}


		// Get card groups in this deck and in the tags and NOT in exclude tags.
		$card_groups = CardGroup
			::query()
			// Include only card not in collections and in users cards.
			->whereIn( 'id', $interested_card_group_ids_for_topic_or_deck );

		if ( $deck_id ) {
			// Include only cards groups in this deck.
			$card_groups = $card_groups->where( 'deck_id', '=', $deck_id );
		} else {
			// Include only cards groups in this topic.
			$card_groups = $card_groups->where( 'topic_id', '=', $topic_id );
		}

		// Only necessary if not all tags are included.
		if ( ! $add_all_tags ) {
			$card_groups = $card_groups
				// Include only cards groups in the tags and exclude cards groups in the exclude tags.
				->whereHas( 'tags', function ( $query ) use ( $tags, $tags_excluded ) {
					$query
						->whereIn( SP_TABLE_TAGS . '.id', $tags )
						->whereNotIn( SP_TABLE_TAGS . '.id', $tags_excluded );
				} );
		}
		$card_groups_ids_in_deck = $card_groups->get()->toArray();
		$card_groups_ids_in_deck = array_map(
			static function ( $card_group ) {
				return $card_group['id'];
			},
			$card_groups_ids_in_deck
		);

		return self::get_cards_to_study_in_card_groups(
			$card_groups_ids_in_deck,
			$no_of_new,
			$no_on_hold,
			$no_of_revision,
			$user_cards_not_studied_ids,// $user_cards_not_studied['card_ids'],
			$user_cards_in_revision_and_due_ids,//$user_cards_answered['revision_and_due_ids'],
			$user_cards_on_hold_and_due_ids,// $user_cards_answered['on_hold_and_due_ids']
		);
	}


	/**
	 * Get cards to study in card groups.
	 *
	 * @param array $card_group_ids
	 * @param int $max_no_of_new_cards
	 * @param int $max_no_on_hold
	 * @param int $max_no_in_revision
	 * @param array $all_new_card_ids
	 * @param array $all_revision_card_ids
	 * @param array $all_on_hold_card_ids
	 *
	 * @return Card[]
	 */
	public
	static function get_cards_to_study_in_card_groups(
		array $card_group_ids,
		int $max_no_of_new_cards,
		int $max_no_on_hold,
		int $max_no_in_revision,
		array $all_new_card_ids,
		array $all_revision_card_ids,
		array $all_on_hold_card_ids
	): array {
		$card_groups_new      = CardGroup
			::query()
			->whereIn( 'id', $card_group_ids )
			->with(
				array(
					'cards' => function ( $query ) use ( $all_new_card_ids, $max_no_of_new_cards ) {
						$query
							->whereIn( 'id', $all_new_card_ids )
							->limit( $max_no_of_new_cards );
					},
				)
			)->get();
		$card_groups_revision = CardGroup
			::query()
			->whereIn( 'id', $card_group_ids )
			->with(
				array(
					'cards' => function ( $query ) use ( $all_revision_card_ids, $max_no_in_revision ) {
						$query
							->whereIn( 'id', $all_revision_card_ids )
							->limit( $max_no_in_revision );
					},
				)
			)->get();
		$card_groups_hold     = CardGroup
			::query()
			->whereIn( 'id', $card_group_ids )
			->with(
				array(
					'cards' => function ( $query ) use ( $all_on_hold_card_ids, $max_no_on_hold ) {
						$query
							->whereIn( 'id', $all_on_hold_card_ids )
							->limit( $max_no_on_hold );
					},
				)
			)->get();

		// Merge all eloquent collections card groups.
//		$card_groups = $card_groups_new
//			->merge( $card_groups_revision )
//			->merge( $card_groups_hold );

		$cards = array();
		foreach ( $card_groups_new as $card_group ) {
			foreach ( $card_group->cards as $card ) {
				// Make sure that the card is not already in the array.
				$existing_card_ids = array_map(
					static function ( $card ) {
						return $card->id;
					},
					$cards
				);
				if ( in_array( $card->id, $existing_card_ids, true ) ) {
					continue;
				}
				$cards[] = $card;
			}
		}
		foreach ( $card_groups_revision as $card_group ) {
			foreach ( $card_group->cards as $card ) {
				// Make sure that the card is not already in the array.
				$existing_card_ids = array_map(
					static function ( $card ) {
						return $card->id;
					},
					$cards
				);
				if ( in_array( $card->id, $existing_card_ids, true ) ) {
					continue;
				}
				$cards[] = $card;
			}
		}
		foreach ( $card_groups_hold as $card_group ) {
			foreach ( $card_group->cards as $card ) {
				// Make sure that the card is not already in the array.
				$existing_card_ids = array_map(
					static function ( $card ) {
						return $card->id;
					},
					$cards
				);
				if ( in_array( $card->id, $existing_card_ids, true ) ) {
					continue;
				}
				$cards[] = $card;
			}
		}

		$all_cards = array();
		foreach ( $cards as $card ) {
			$card_type = $card->card_group->card_type;
			if ( in_array( $card_type, array( 'table', 'image' ) ) ) {
				if ( ! is_array( $card->answer ) ) {
					$card->answer = json_decode( $card->answer );
				}
				if ( ! is_array( $card->question ) ) {
					$card->question = json_decode( $card->question );
				}
			}
			$all_cards[] = $card;
		}

		return $all_cards;
	}

	/**
	 * Get user cards.
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public
	static function get_all_user_cards(
		int $user_id
	): array {
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
			if ( is_array( $card_group->cards ) ) {
				foreach ( $card_group->cards as $card ) {
					$card_ids[] = $card->id;
				}
			}
//			foreach ( $card_group->cards as $card ) {
//				$card_ids[] = $card->id;
//			}
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
	 * @param array $user_study_ids The user's study ids.
	 * @param string|null $date The date. If you want to filter by date answered.
	 *
	 * @return array
	 */
	public
	static function get_all_last_answered_user_cards(
		array $user_study_ids, string $date = null
	): array {
		$card_answered = Answered
			::query()
			->with(
				array(
					'card',
					'study' => function ( $q ) use ( $user_study_ids ) {
						$q->whereIn( 'id', $user_study_ids );
					},
				)
			)
			->whereHas(
				'study',
				function ( $query ) use ( $user_study_ids ) {
					$query->whereIn( 'id', $user_study_ids );
				}
			);
		if ( $date ) {
			$card_answered = $card_answered->whereDate( 'created_at', '=', $date );
		}

		$card_answered = $card_answered
			->orderBy( 'created_at', 'desc' );

		$sql = $card_answered->toSql();

		$card_answered = $card_answered
			->get()->all();

		$card_answered_unique = array();
		foreach ( $card_answered as $answered ) {
			$card_id = $answered->card_id;
			if ( ! isset( $card_answered_unique[ $card_id ] ) ) {
				$card_answered_unique[ $card_id ] = $answered;
			}
		}

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
		$cards_answered_as_new     = array(); // Those that are answered only once.
		$cards_answered_as_new_ids = array(); // Those that are answered only once.

		foreach ( $card_answered_unique as $answered ) {
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
			$is_due = strtotime( $card_due_date ) <= strtotime( date( 'Y-m-d', strtotime( $today ) ) );
			if ( $is_due ) {
				if ( $answered->grade === 'hold' ) {
					$cards_on_hold_and_due[] = $card;
				} else {
					$cards_in_revision_and_due[] = $card;
				}
			}

			Initializer::add_debug_with_key(
				'is_due_' . $answered->card_id,
				array(
					'answer_id'            => $answered->id,
					'card'                 => $card,
					'answered'             => $answered,
					'is_due'               => $is_due,
					'today'                => $today,
					'card_due_date'        => $card_due_date,
					'sql'                  => $sql,
					'card_answered'        => $card_answered,
					'card_answered_unique' => $card_answered_unique,
				)
			);

			// Check if answered as new.
			if ( $answered->answered_as_new ) {
				$cards_answered_as_new[]     = $card;
				$cards_answered_as_new_ids[] = $card->id;
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
			'answered_as_new'      => $cards_answered_as_new,
			'answered_as_new_ids'  => $cards_answered_as_new_ids,
		);
	}

	public
	static function get_all_last_answered_user_cards__(
		array $user_study_ids, string $date = null
	): array {
		// Get distinct card ids.
		$distinct          = Answered
			::query()
			->select( 'card_id' )
			->whereIn( 'study_id', $user_study_ids )
			->groupBy( [ 'card_id' ] )
			->get();
		$card_ids_distinct = $distinct->pluck( 'card_id' )->toArray();


		$card_answered = Answered
			::query()
			->with(
				array(
					'card',
					'study' => function ( $q ) use ( $user_study_ids ) {
						$q->whereIn( 'id', $user_study_ids );
					},
				)
			)
			->whereHas(
				'study',
				function ( $query ) use ( $user_study_ids ) {
					$query->whereIn( 'id', $user_study_ids );
				}
			);
		if ( $date ) {
			$card_answered = $card_answered->whereDate( 'created_at', '=', $date );
		}
//		$card_answered = $card_answered
//			->groupBy( [ 'card_id' ] )
//			->orderBy( 'created_at', 'desc' );

		$card_answered = $card_answered
			->whereIn( 'card_id', $card_ids_distinct )
			->orderBy( 'created_at', 'desc' );

		$sql = $card_answered->toSql();

		$card_answered = $card_answered
			->get()->all();

		$card_answered_unique = array();
		foreach ( $card_answered as $answered ) {
			$card_id = $answered->card_id;
			if ( ! isset( $card_answered_unique[ $card_id ] ) ) {
				$card_answered_unique[ $card_id ] = $answered;
			}
		}

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
		$cards_answered_as_new     = array(); // Those that are answered only once.
		$cards_answered_as_new_ids = array(); // Those that are answered only once.

		foreach ( $card_answered_unique as $answered ) {
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
			$is_due = strtotime( $card_due_date ) <= strtotime( date( 'Y-m-d', strtotime( $today ) ) );
			if ( $is_due ) {
				if ( $answered->grade === 'hold' ) {
					$cards_on_hold_and_due[] = $card;
				} else {
					$cards_in_revision_and_due[] = $card;
				}
			}

			Initializer::add_debug_with_key(
				'is_due_' . $answered->card_id,
				array(
					'answer_id'         => $answered->id,
					'card'              => $card,
					'answered'          => $answered,
					'is_due'            => $is_due,
					'today'             => $today,
					'card_due_date'     => $card_due_date,
					'sql'               => $sql,
					'card_ids_distinct' => $card_ids_distinct,

				)
			);

			// Check if answered as new.
//			$answered = Answered
//				::query()
//				->where( 'card_id', '=', $card->id )
//				->limit( 2 )
//				->get()->all();
//			if ( count( $answered ) === 1 ) {
			if ( $answered->answered_as_new ) {
				$cards_answered_as_new[]     = $card;
				$cards_answered_as_new_ids[] = $card->id;
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
			'answered_as_new'      => $cards_answered_as_new,
			'answered_as_new_ids'  => $cards_answered_as_new_ids,
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
	public
	static function get_new_cards_not_answered_but_added(
		int $user_id,
		array $last_answered_card_ids = array()
	): array {
//		$user_timezone_early_morning_today = get_user_timezone_date_early_morning_today( $user_id );
//		$user_timezone_midnight_today      = get_user_timezone_date_midnight_today( $user_id );
		$all_users_cards = self::get_all_user_cards( $user_id );
		$user_cards      = self
			::query()
			->where( 'user_id', '=', $user_id )
			->with(
				array(
					'card_group.cards' => function ( $query ) use ( $all_users_cards, $last_answered_card_ids ) {
						// Exclude all cards that has been Answered before.
						$query
							->whereIn( 'id', $all_users_cards['card_ids'] )
							->whereNotIn( 'id', $last_answered_card_ids );
					},
				)
			)
			->get()->all();

		// return card ids.
		$card_ids       = array();
		$cards          = array();
		$card_group_ids = array();
		$topic_ids      = array();
		foreach ( $user_cards as $user_card ) {
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
			'card_group_ids' => array_unique( $card_group_ids ),
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
	public
	static function get_cards_on_hold_for_today(
		int $user_id,
		int $user_study_id,
		array $last_answered_card_ids_on_hold_and_due = array()
	): array {
		// $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today( $user_id );
		// $user_timezone_midnight_today      = get_user_timezone_date_midnight_today( $user_id );
		$all_users_card = self::get_all_user_cards( $user_id );

		$card_groups = self::query()
		                   ->where( 'user_id', '=', $user_id )
		                   ->with(
			                   array(
				                   'card_group.cards' => function ( $query ) use (
					                   $all_users_card,
					                   $last_answered_card_ids_on_hold_and_due
				                   ) {
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
	private static function get_cards_in_revision_for_today(
		int $user_id,
		int $user_study_id,
		array $last_answered_card_ids_in_revision_and_due = array()
	): array {
		// $user_timezone_early_morning_today = get_user_timezone_date_early_morning_today( $user_id );
		// $user_timezone_midnight_today      = get_user_timezone_date_midnight_today( $user_id );
		$all_users_card = self::get_all_user_cards( $user_id );

		$card_groups = self::query()
		                   ->where( 'user_id', '=', $user_id )
		                   ->with(
			                   array(
				                   'card_group.cards' => function ( $query ) use (
					                   $all_users_card,
					                   $last_answered_card_ids_in_revision_and_due
				                   ) {
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

	private static function maybe_execute_query( string $sql, string $return_column, array $debug, string $debug_key ): array {
		global $wpdb;
		if ( sp_in_sql_mode() ) {
			return self::execute_query( $sql, $return_column, $debug, $debug_key );
		}

		return array();
	}

	private static function execute_query( string $sql, string $return_column, array $debug, string $debug_key ): array {
		global $wpdb;
		$start_time         = time();
		$sql_result         = $wpdb->get_results( $sql, ARRAY_A );
		$stop_time_result   = time();
		$start_time_mapping = time();
		$result_card_ids    = array_map(
			static function ( $val ) use ( $return_column ) {
				return $val[ $return_column ];
			},
			$sql_result ?? array()
		);
		$stop_time          = time();

		return self::format_debug_data( $debug, $debug_key, array(
			'sql'                                 => $sql,
			'sql_result'                          => $sql_result,
			"sub_result_{$return_column}s"        => $result_card_ids,
			'sql_result_mili_seconds'             => ( $stop_time_result - $start_time ),
			'sql_result_mili_seconds_for_mapping' => ( $stop_time - $start_time_mapping ),
		) );

	}


}

