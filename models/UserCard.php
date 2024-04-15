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
use Model\Study;
use Model\Topic;
use Model\User;
use StudyPlannerPro\Initializer;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Models\Tag;

use function StudyPlannerPro\get_uncategorized_deck_group_id;
use function StudyPlannerPro\get_uncategorized_deck_id;
use function StudyPlannerPro\get_uncategorized_topic_id;
use function StudyPlannerPro\get_user_timezone_date_early_morning_today;
use function StudyPlannerPro\get_user_timezone_date_midnight_today;
use function StudyPlannerPro\sp_get_user_studies;
use function StudyPlannerPro\sp_get_user_study;
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

	protected static function get_table_names() {
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
			  # List of card groups in uncategorized topics
			  SELECT cg_uc.id from {$tb_card_groups} as cg_uc
			  WHERE cg_uc.topic_id = {$topic_uncategorized_id}
			)	 
		";

		$result_cards_in_uncategorized_topics = $wpdb->get_results( $sql_cards_in_uncategorized_topic, ARRAY_A );

		// Exclude all cards in collections.
		$sql_exclude_collection = " 
			SELECT id from {$tb_cards} as c_cl
			WHERE c_cl.card_group_id IN (
				 # List of card groups in a collection
				 SELECT id from {$tb_card_groups} 
				 WHERE collection_id > 0
			)
		";

		$result_cards_in_collections = $wpdb->get_results( $sql_exclude_collection, ARRAY_A );

		// Get new cards.
		$sql_new_cards = "
			SELECT id from {$tb_cards} as c
			WHERE c.card_group_id IN (
				# List of card_groups in user cards.
				SELECT id from {$tb_card_groups} as cg
				WHERE id IN (
						# List of cg ids in user_cards.
						SELECT card_group_id from {$tb_user_cards} as uc
						WHERE uc.user_id = {$user_id}
				) 
			)
			AND c.id NOT IN (
				# List of card_ids that have been answred before.
				SELECT DISTINCT card_id from {$tb_answered} as a
				WHERE a.study_id IN (
					# List of user study ids.                        
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
		$last_count = count( $debug[ $key ] );
		$new_data   = array();
		if ( ! empty( $debug[ $key ] ) ) {
			$new_data = $data;
		} else {
			$new_data = array_merge( $data );
		}
		$debug[ $key ][ $last_count + 1 . '_log' ] = $new_data;

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

		$study_id       = $study->id;
		$study_to_array = $study->toArray();

		$debug = array(
			'variables'                    => array(),
			'uncategorized_topic'          => array(),
			'collections'                  => array(),
			'study_to_array'               => $study_to_array,
			'study_id'                     => $study_id,
			'user_cards'                   => array(),
			'cards_answered'               => array(),
			'cards_not_answered'           => array(),
			'included_tags'                => array(
				'included' => false,
			),
			'excluded_tags'                => array(
				'excluded' => false,
			),
			'topic'                        => array(),
			'deck'                         => array(),
			'new'                          => array(),
			'revise'                       => array(),
			'on_hold'                      => array(),
			'cards_new'                    => array(),
			'cards_revision'               => array(),
			'cards_on_hold'                => array(),
			'count_studied_today_new'      => array(),
			'count_studied_today_revision' => array(),
			'count_studied_today_on_hold'  => array(),
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
			  # List of card groups in uncategorized topics
			  SELECT cg_uc.id from {$tb_card_groups} as cg_uc
			  WHERE cg_uc.topic_id = {$topic_uncategorized_id}
			)	 
		";
		if ( sp_in_sql_mode() ) {
			$start_time_uncategorized                  = time();
			$sub_result_cards_in_uncategorized_topics_ = $wpdb->get_results( $sql_cards_in_uncategorized_topic, ARRAY_A );
			$_stop_time_uncategorized_query            = time();
			$sub_result_cards_in_uncategorized_topics  = array_map(
				static function ( $val ) {
					return $val['id'];
				},
				$sub_result_cards_in_uncategorized_topics_
			);
			$_stop_time_uncategorized_query_loop       = time();
			$debug                                     = self::format_debug_data( $debug, 'uncategorized_topic', array(
				'sql_cards_in_uncategorized_topic'          => $sql_cards_in_uncategorized_topic,
				'sub_result_cards_in_uncategorized_topics_' => $sub_result_cards_in_uncategorized_topics_,
				'sub_result_cards_in_uncategorized_topics'  => $sub_result_cards_in_uncategorized_topics,
				'mill_seconds_for_query'                    => ( $_stop_time_uncategorized_query - $start_time_uncategorized ),
				'mill_seconds_with'                         => $_stop_time_uncategorized_query_loop - $start_time_uncategorized,
			) );
		}


		// </editor-fold desc="Uncategorized Topics" >

		// <editor-fold desc="Collections" >
		// Exclude all cards in collections.
		$sql_exclude_collection = " 
			SELECT id from {$tb_cards} as c_cl
			WHERE c_cl.card_group_id IN (
				 # List of card groups in a collection
				 SELECT id from {$tb_card_groups} 
				 WHERE collection_id > 0
			)
		";
		if ( sp_in_sql_mode() ) {
			$_start_time_collections              = time();
			$sub_result_cards_in_collections_     = $wpdb->get_results( $sql_exclude_collection, ARRAY_A );
			$_stop_time_collections               = time();
			$sub_result_cards_in_collections      = array_map(
				static function ( $val ) {
					return $val['id'];
				},
				$sub_result_cards_in_collections_
			);
			$_stop_time_and_query_loop_collection = time();
			$debug                                = self::format_debug_data( $debug, 'collections', array(
				'sql_exclude_collection'           => $sql_exclude_collection,
				'sub_result_cards_in_collections_' => $sub_result_cards_in_collections_,
				'sub_result_cards_in_collections'  => $sub_result_cards_in_collections,
				'mill_seconds_for_query'           => ( $_stop_time_collections - $_start_time_collections ),
				'mill_seconds_with'                => ( $_stop_time_and_query_loop_collection - $_start_time_collections ),
			) );
		}


		// </editor-fold desc="Collections" >

		// <editor-fold desc="User Cards " >

		// Get cards in user cards.
		$sql_user_cards = "
			SELECT id from {$tb_cards} as c
			WHERE c.card_group_id IN (
				# List of card_groups in user cards.
				SELECT id from {$tb_card_groups} as cg
				WHERE id IN (
						# List of cg ids in user_cards.
						SELECT card_group_id from {$tb_user_cards} as uc
						WHERE uc.user_id = $study_user_id 
				)
			)
		";
		if ( sp_in_sql_mode() ) {
			$_start_time_user_cards               = time();
			$sub_result_cards_in_user_cards_      = $wpdb->get_results( $sql_user_cards, ARRAY_A );
			$_stop_time_user_cards                = time();
			$sub_result_cards_in_user_cards       = array_map(
				static function ( $val ) {
					return $val['id'];
				},
				$sub_result_cards_in_user_cards_
			);
			$_stop_time_and_query_loop_user_cards = time();
			$debug                                = self::format_debug_data( $debug, 'user_cards', array(
				'sql_user_cards'                  => $sql_exclude_collection,
				'sub_result_cards_in_user_cards_' => $sub_result_cards_in_user_cards_,
				'sub_result_cards_in_user_cards'  => $sub_result_cards_in_user_cards,
				'mill_seconds_for_query'          => ( $_stop_time_user_cards - $_start_time_user_cards ),
				'mill_seconds_with'               => ( $_stop_time_and_query_loop_user_cards - $_start_time_user_cards ),
			) );
		}


		// </editor-fold desc="User Cards " >

		// <editor-fold desc="Topic Cards" >

		// Get cards in topic.
		if ( ! empty( $study->topic_id ) ) {
			$sql_cards_in_topic = "
				SELECT id FROM $tb_cards as c_t
				WHERE c_t.card_group_id IN (
					# List of card_groups in topic
					SELECT id from {$tb_card_groups} as cg_t 
					WHERE cg_t.topic_id = $topic_id	
				) 
		";
			if ( sp_in_sql_mode() ) {
				$_start_time_topic               = time();
				$sub_result_cards_in_topic_      = $wpdb->get_results( $sql_cards_in_topic, ARRAY_A );
				$_stop_time_topics               = time();
				$sub_result_cards_in_topic       = array_map(
					static function ( $val ) {
						return $val['id'];
					},
					$sub_result_cards_in_topic_
				);
				$_stop_time_and_query_loop_topic = time();
				$debug                           = self::format_debug_data( $debug, 'topic', array(
					'sql_cards_in_topic'         => $sql_cards_in_topic,
					'sub_result_cards_in_topic_' => $sub_result_cards_in_topic_ ?? array(),
					'sub_result_cards_in_topic'  => $sub_result_cards_in_topic ?? array(),
					'mill_seconds_for_query'     => ( $_stop_time_topics - $_start_time_topic ),
					'mill_seconds_with'          => ( $_stop_time_and_query_loop_topic - $_start_time_topic ),
				) );
			}

		}

		// </editor-fold desc="Topic Cards" >

		// <editor-fold desc="Deck Cards" >

		// Get cards in decks. (In all topics in a deck)
		if ( ! empty( $study->deck_id ) ) {
			$sql_cards_in_deck = "
				SELECT id FROM $tb_cards as c_t
				WHERE c_t.card_group_id IN (
					# List of card_groups in topic
				    SELECT id from {$tb_card_groups} as cg_t
				    WHERE cg_t.topic_id IN (
						# List of topics in deck
						SELECT id from {$tb_topics} as t_d
						WHERE t_d.deck_id = $deck_id
					)
			   )
		";
			if ( sp_in_sql_mode() ) {
				$_start_time_deck               = time();
				$sub_result_cards_in_deck_      = $wpdb->get_results( $sql_cards_in_deck, ARRAY_A );
				$_stop_time_deck                = time();
				$sub_result_cards_in_deck       = array_map(
					static function ( $val ) {
						return $val['id'];
					},
					$sub_result_cards_in_deck_
				);
				$_stop_time_and_query_loop_deck = time();
				$debug                          = self::format_debug_data( $debug, 'deck', array(
					'sql_cards_in_deck'         => $sql_cards_in_deck,
					'sub_result_cards_in_deck_' => $sub_result_cards_in_deck_ ?? array(),
					'sub_result_cards_in_deck'  => $sub_result_cards_in_deck ?? array(),
					'mill_seconds_for_query'    => ( $_stop_time_deck - $_start_time_deck ),
					'mill_seconds_with'         => ( $_stop_time_and_query_loop_deck - $_start_time_deck ),
				) );
			}
		}
		// </editor-fold desc="Deck Cards" >

		// <editor-fold desc="Tags Decisions" >

		// Tags to include.
//		$sql_tags_to_include = "";
//		$sql_tags_to_exclude = "";

//		$use_tags_included = false;
//		$use_tags_excluded = false;

		$_exclude = false;
//		if ( empty( $tags_to_include ) && empty( $tags_to_exclude ) ) {
//			// If none is set, just avoid using tags or excluded tags at all.
//		} else {
//			if ( ! empty( $tags_to_include ) ) {
//				// Included tags set? Return only cards in these tags.
//				$use_tags_included = true;
//			} else {
//				if ( ! empty( $tags_to_exclude ) ) {
//					// Considered only when $included_tags is empty.
//					// Return only cards not in these tags.
//					$use_tags_excluded = true;
//				}
//			}
//		}

		// </editor-fold desc="Tags Decisions" >

		// <editor-fold desc="Included Tags">

		if ( ! empty( $tags_to_include ) ) {
			$debug['included_tags']['included'] = true;
			// Get card groups in Included_tags.
			$implode_tags                     = '(' . implode( ',', $tags_to_include ) . ')';
			$sql_cards_groups_in_include_tags = "
				 SELECT taggable_id from {$tb_taggable} as tga_tint
				 WHERE tga_tint.tag_id IN {$implode_tags} 	
				 AND tga_tint.taggable_type = '{$taggable_type_card_groups}'
			";
			if ( sp_in_sql_mode() ) {
				$_start_time_card_groups_in_include           = time();
				$sub_result_cards_groups_in_include_tags_     = $wpdb->get_results( $sql_cards_groups_in_include_tags, ARRAY_A );
				$_stop_time_card_groups_in_include            = time();
				$sub_result_cards_groups_in_include_tags      = array_map(
					static function ( $val ) {
						return $val['taggable_id'];
					},
					$sub_result_cards_groups_in_include_tags_
				);
				$_stop_time_query_loop_card_groups_in_include = time();
				$debug                                        = self::format_debug_data( $debug, 'included_tags', array(
					'tags_to_include'                          => $tags_to_include,
					'implode_tags'                             => $implode_tags,
					//
					'sql_cards_groups_in_include_tags'         => $sql_cards_groups_in_include_tags,
					'sub_result_cards_groups_in_include_tags_' => $sub_result_cards_groups_in_include_tags_,
					'sub_result_cards_groups_in_include_tags'  => $sub_result_cards_groups_in_include_tags,
					'mill_seconds_for_query'                   => ( $_stop_time_card_groups_in_include - $_start_time_card_groups_in_include ),
					'mill_seconds_with'                        => $_stop_time_query_loop_card_groups_in_include - $_start_time_card_groups_in_include,
				) );
			}
			$sql_tags_to_include = "
				SELECT c_in.id from $tb_cards  as c_in
				WHERE c_in.card_group_id IN (
					# List of card groups in tags.
					 {$sql_cards_groups_in_include_tags}
				)
			";
			if ( sp_in_sql_mode() ) {
				$_start_time_cards_in_include_tags  = time();
				$sub_result_cards_in_included_tags_ = $wpdb->get_results( $sql_tags_to_include, ARRAY_A );
				$_stop_time_card_in_include         = time();
				$sub_result_cards_in_included_tags  = array_map(
					static function ( $val ) {
						return $val['id'];
					},
					$sub_result_cards_in_included_tags_ ?? array()
				);
				$_stop_time_query_loop_card_include = time();
				$debug                              = self::format_debug_data( $debug, 'included_tags', array(
					'sql_tags_to_include'                => $sql_tags_to_include,
					'sub_result_cards_in_included_tags_' => $sub_result_cards_in_included_tags_,
					'sub_result_cards_in_included_tags'  => $sub_result_cards_in_included_tags,
					'mill_seconds_for_query'             => ( $_stop_time_card_in_include - $_start_time_cards_in_include_tags ),
					'mill_seconds_with'                  => ( $_stop_time_query_loop_card_include - $_start_time_cards_in_include_tags ),
				) );
			}

		}

		// </editor-fold desc="Included Tags">

		// <editor-fold desc="Excluded Tags">
		// Excluded Tags.
		if ( empty( $tags_to_include ) && ! empty( $tags_to_exclude ) ) {
			$debug['excluded_tags']['excluded'] = true;
			// Get card groups in Included_tags.
			$implode_excluded_tags            = '(' . implode( ',', $tags_to_exclude ) . ')';
			$sql_cards_groups_in_exclude_tags = "
				 SELECT taggable_id from {$tb_taggable_excluded} as tgae_tint
				 WHERE tgae_tint.tag_id IN {$implode_excluded_tags} 	
				 AND tgae_tint.taggable_type = {$taggable_type_card_groups}
			";
			if ( sp_in_sql_mode() ) {
				$_start_time_card_groups_in_excluded_tags              = time();
				$sub_result_cards_groups_in_excluded_tags_             = $wpdb->get_results( $sql_cards_groups_in_exclude_tags, ARRAY_A );
				$_stop_time_card_groups_in_excluded_tab                = time();
				$sub_result_cards_groups_in_excluded_tags              = array_map(
					static function ( $val ) {
						return $val['taggable_id'];
					},
					$sub_result_cards_groups_in_excluded_tags_
				);
				$_stop_time_and_query_loop_card_groups_in_excluded_tab = time();
				$debug                                                 = self::format_debug_data( $debug, 'excluded_tags', array(
					'tags_to_exlude'                            => $tags_to_exclude,
					'implode_excluded_tags'                     => $implode_excluded_tags,
					//
					'sql_cards_groups_in_exclude_tags'          => $sql_cards_groups_in_exclude_tags,
					'sub_result_cards_groups_in_excluded_tags_' => $sub_result_cards_groups_in_excluded_tags_,
					'sub_result_cards_groups_in_excluded_tags'  => $sub_result_cards_groups_in_excluded_tags,
					'mill_seconds_for_query'                    => ( $_stop_time_card_groups_in_excluded_tab - $_start_time_card_groups_in_excluded_tags ),
					'mill_seconds_with'                         => ( $_stop_time_and_query_loop_card_groups_in_excluded_tab - $_start_time_card_groups_in_excluded_tags ),
				) );
			}


			$sql_cards_in_tags_to_exclude = "
				SELECT id from $tb_cards  as c_in 
				WHERE c_in.card_group_id IN (
					# List of card groups in tags.
					SELECT cg_in.id from {$tb_card_groups} as cg_in 
					WHERE cg_in.id IN (
						# List of tags joined to card group in taggable.
						{$sql_cards_groups_in_exclude_tags}
					)
				)
			";
			if ( sp_in_sql_mode() ) {
				$_start_time_cards_in_excluded_tags               = time();
				$sub_result_cards_in_excluded_tags_               = $wpdb->get_results( $sql_cards_in_tags_to_exclude, ARRAY_A );
				$_stop_time_cards_in_excluded_tags                = time();
				$sub_result_cards_in_excluded_tags                = array_map(
					static function ( $val ) {
						return $val['id'];
					},
					$sub_result_cards_in_excluded_tags_ ?? array()
				);
				$_stop_time_and_query_loop_cards__in_excluded_tab = time();
				$debug                                            = self::format_debug_data( $debug, 'excluded_tags', array(
					'sql_cards_in_tags_to_exclude'       => $sql_cards_in_tags_to_exclude,
					'sub_result_cards_in_excluded_tags_' => $sub_result_cards_in_excluded_tags_,
					'sub_result_cards_in_excluded_tags'  => $sub_result_cards_in_excluded_tags,
					'mill_seconds_for_query'             => ( $_stop_time_cards_in_excluded_tags - $_start_time_cards_in_excluded_tags ),
					'mill_seconds_with'                  => ( $_stop_time_and_query_loop_cards__in_excluded_tab - $_start_time_cards_in_excluded_tags ),
				) );
			}
		}

		// </editor-fold desc="Excluded Tags">

		// <editor-fold desc="Answered Cards Distinct">

		// Distinct answered card_ids
		$sql_distinct_answered_cards = "
			SELECT DISTINCT card_id from {$tb_answered} as a 
			WHERE a.study_id = {$study_id}
		";
		if ( sp_in_sql_mode() ) {
			$_start_time_answered_cards               = time();
			$sub_result_distinct_answered_cards_      = $wpdb->get_results( $sql_distinct_answered_cards, ARRAY_A );
			$_stop_time_answered_cards                = time();
			$sub_result_distinct_answered_cards       = array_map(
				static function ( $val ) {
					return $val['card_id'];
				},
				$sub_result_distinct_answered_cards_ ?? array()
			);
			$_stop_time_and_query_loop_answered_cards = time();
			$debug                                    = self::format_debug_data( $debug, 'new_cards', array(
				'sql_distinct_answered_cards'         => $sql_distinct_answered_cards,
				'sub_result_distinct_answered_cards_' => $sub_result_distinct_answered_cards_,
				'sub_result_distinct_answered_cards'  => $sub_result_distinct_answered_cards,
				'mill_seconds_for_query'              => ( $_stop_time_answered_cards - $_start_time_answered_cards ),
				'mill_seconds_with'                   => ( $_stop_time_and_query_loop_answered_cards - $_start_time_answered_cards ),
			) );
		}

		// </editor-fold desc="Answered Cards ">

		// <editor-fold desc="Cards Not in Answered">
		// Get new cards.
		$sql_not_answered = "
			SELECT id from {$tb_cards} as c_new
			WHERE c_new.id NOT IN (
				{$sql_distinct_answered_cards}
			)
		";
		if ( sp_in_sql_mode() ) {
			$_start_time_not_answered_cards               = time();
			$sub_not_answered_                            = $wpdb->get_results( $sql_not_answered, ARRAY_A );
			$_stop_time_not_answered_cards                = time();
			$sub_not_answered                             = array_map(
				static function ( $val ) {
					return $val['id'];
				},
				$sub_not_answered_ ?? array()
			);
			$_stop_time_and_query_loop_not_answered_cards = time();
			$debug                                        = self::format_debug_data( $debug, 'new_cards', array(
				'sql_not_answered'       => $sql_not_answered,
				'sub_not_answered_'      => $sub_not_answered_ ?? array(),
				'sub_not_answered'       => $sub_not_answered ?? array(),
				'mill_seconds_for_query' => ( $_stop_time_not_answered_cards - $_start_time_not_answered_cards ),
				'mill_seconds_with'      => ( $_stop_time_and_query_loop_not_answered_cards - $_start_time_not_answered_cards ),
			) );
		}

		// </editor-fold desc="Cards Not answered">

		// Get new cards.
		$result_sql_new = sprintf( "
			SELECT * from {$tb_cards} as c1 
			WHERE c1.id IN ($sql_user_cards) 
			AND c1.id NOT IN ($sql_exclude_collection) 
			AND c1.id NOT IN ($sql_cards_in_uncategorized_topic) 
			AND c1.id IN ($sql_not_answered) 
			%1$s 
			%2$s 
			%3$s 
			%4$s 
		",
			! empty( $sql_cards_in_topic ) ? "AND c1.id IN ($sql_cards_in_topic)" : '',
			! empty( $sql_cards_in_deck ) ? "AND c1.id IN ($sql_cards_in_deck)" : '',
			! empty( $sql_tags_to_include ) ? "AND c1.id IN ($sql_tags_to_include)" : '',
			! empty( $sql_cards_in_tags_to_exclude ) ? "AND c1.id NOT IN ($sql_cards_in_tags_to_exclude)" : '',
		);
		// todo add limits later.
		$_start_time_cards_new               = time();
		$result_new_cards__                  = $wpdb->get_results( $result_sql_new, ARRAY_A );
		$_stop_time_cards_new                = time();
		$result_new_cards_ids_               = array_map(
			static function ( $val ) {
				return $val['id'];
			},
			$result_new_cards__
		);
		$_stop_time_and_query_loop_cards_new = time();
		if ( sp_in_sql_mode() ) {
			$debug = self::format_debug_data( $debug, 'cards_new', array(
				'result_sql_new'            => $result_sql_new,
				'sub_result_cards_in_deck_' => $sub_result_cards_in_deck_,
				'result_new_cards__'        => $result_new_cards__,
				'result_new_cards_ids_'     => $result_new_cards_ids_,
				'mill_seconds_for_query'    => ( $_stop_time_cards_new - $_start_time_cards_new ),
				'mill_seconds_with'         => ( $_stop_time_and_query_loop_cards_new - $_start_time_cards_new ),
			) );
		}

//		$results_new_ = $wpdb->get_results( $sql_new, ARRAY_A );
//		$results_new  = array_map(
//			static function ( $val ) {
//				return $val['id'];
//			},
//			$results_new_
//		);

		$ret = array(
//			'new_cards'         => Card::find( array_column( $results_new, 'id' ) ),
			'new_card_ids'      => array(),
			'revision_cards'    => array(),
			'revision_card_ids' => array(),
			'on_hold_cards'     => array(),
			'on_hold_card_ids'  => array(),
		);

		$wpdb->hide_errors();

		return $ret;
	}

	/**
	 * Get cards to study in study.
	 *
	 * @param Study $study The study.
	 * @param array $interested_card_group_ids_for_topic_or_deck The interested card group ids for topic or deck.
	 *
	 * @return Card[]
	 */
	public static function get_cards_to_study_in_study(
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
	public static function get_cards_to_study_in_card_groups(
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
	public static function get_all_last_answered_user_cards( array $user_study_ids, string $date = null ): array {
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

	public static function get_all_last_answered_user_cards__( array $user_study_ids, string $date = null ): array {
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
	public static function get_new_cards_not_answered_but_added(
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
	public static function get_cards_on_hold_for_today(
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
	public static function get_cards_in_revision_for_today(
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


}

