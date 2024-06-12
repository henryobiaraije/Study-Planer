<?php

namespace Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\Exception;
use StudyPlannerPro\Initializer;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Libs\Settings;
use StudyPlannerPro\Models\Collections;
use StudyPlannerPro\Models\Tag;
use StudyPlannerPro\Models\UserCard;

use function StudyPlannerPro\sp_get_user_ignored_card_group_ids;
use function StudyPlannerPro\sp_get_user_study;

class CardGroup extends Model {
	protected $table = SP_TABLE_CARD_GROUPS;

	use SoftDeletes;

	protected $fillable = array(
		'deck_id',
		'whole_question',
		'card_type',
		'scheduled_at',
		'name',
		'reverse',
		'topic_id'
	);
	protected $appends = array( 'card_group_edit_url', 'bg_image_url' );

	protected $dates = array( 'deleted_at' );

	protected $casts = array(
		'whole_question' => 'array',
	);

	protected static function boot() {
		static::setEventDispatcher( new Dispatcher() );
		parent::boot();

		static::deleting(
			function ( $invoice ) {
				$invoice->cards()->delete();
			}
		);
	}

	public function cards(): \Illuminate\Database\Eloquent\Relations\HasMany {
		return $this->hasMany( Card::class );
	}

	public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany {
		return $this->hasMany( Answered::class );
	}

	public function deck() {
		return $this->belongsTo( Deck::class );
	}

	public function topic() {
		return $this->belongsTo( Topic::class, 'topic_id' );
	}

	public function collection() {
		return $this->belongsTo( Collections::class );
	}

	public function tags() {
		return $this->morphToMany( Tag::class, 'taggable', SP_TABLE_TAGGABLES );
	}


	public static function get_card_groups( $args ): array {
		$default = array(
			'search'       => '',
			'page'         => 1,
			'per_page'     => 5,
			'with_trashed' => false,
			'only_trashed' => false,
		);
		$args    = wp_parse_args( $args, $default );
		if ( $args['with_trashed'] ) {
			$card_groups = self::with( 'tags' )->withoutTrashed();
		} elseif ( $args['only_trashed'] ) {
			$card_groups = self::onlyTrashed()->with( 'tags' );
		} else {
			$card_groups = self::with( 'tags' );
		}
		$card_groups = $card_groups
			->where( 'name', 'like', "%{$args['search']}%" );

		$total       = $card_groups->count();
		$offset      = ( $args['page'] - 1 );
		$card_groups = $card_groups
			->offset( $offset )
			->withCount( 'cards' )
			->with( 'deck', 'topic', 'collection', 'deck.deck_group', 'cards' )
			->limit( $args['per_page'] )
			->orderByDesc( 'id' )->get();

		foreach ( $card_groups as $card_group ) {
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

		return array(
			'total'       => $total,
			'card_groups' => $card_groups->all(),
		);
	}

	public function getCardGroupEditUrlAttribute() {
		$card_type = $this->card_type;
		$slug      = Settings::SLUG_BASIC_CARD;
		if ( 'gap' === $card_type ) {
			$slug = Settings::SLUG_GAP_CARD;
		} elseif ( 'table' === $card_type ) {
			$slug = Settings::SLUG_TABLE_CARD;
		} elseif ( 'image' === $card_type ) {
			$slug = Settings::SLUG_IMAGE_CARD;
		}
		$card_url = Initializer::get_admin_url( $slug )
		            . '&card-group=' . $this->id;

		return $card_url;
	}

	public function getBgImageUrlAttribute() {
		$bg_image_url = '';
		if ( $this->bg_image_id ) {
			$bg_image_url = wp_get_attachment_image_url( $this->bg_image_id, 'full' );
		}

		return $bg_image_url;
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

	protected function getCastType( $key ) {
		$card_type             = $this->card_type;
		$is_question_or_answer = in_array( $key, array( 'whole_question' ) );
		$is_table_or_image     = in_array( $card_type, array( 'image', 'table' ) );
		$make_array            = $is_question_or_answer && $is_table_or_image;
		$is_date               = in_array( $key, array( 'created_at', 'updated_at', 'deleted_at' ) );

		if ( $make_array || $is_date ) {
			// dd($key, $card_type, parent::getCastType($key));
			// $this->type;
			return parent::getCastType( $key );
		} else {
			return $this->type;
		}
	}

	public static function get_card_groups_simple( $args ): array {
		$default = [
			'search'                     => '',
			'page'                       => 1,
			'per_page'                   => 5,
			'with_trashed'               => false,
			'only_trashed'               => false,
			//
			'deck_group_id'              => null,
			'deck_id'                    => null,
			'topic_id'                   => null,
			'card_types'                 => null,
			'tags_ids'                   => array(),
			'from_front_end'             => false,
			'for_add_to_study_deck'      => false,
			'for_remove_from_study_deck' => false,
			'for_new_cards'              => false,
			'user_id'                    => null,
			'order_by_deck_group_name'   => false,
			'order_by_deck_name'         => false,
			'order_by_topic'             => false,
		];

		$args = wp_parse_args( $args, $default );

		// if from front end, then either deck_group_id or deck_id or topic_id must be provided.
		if (
			true === $args['from_front_end']
			&& null === $args['deck_group_id']
			&& null === $args['deck_id']
			&& null === $args['topic_id']
			&& false === $args['for_add_to_study_deck']
			&& false === $args['for_remove_from_study_deck']
			&& false === $args['for_new_cards']
			&& false === $args['user_id']
		) {
			return [
				'items' => [],
				'total' => 0,
			];
		}


		if ( true === $args['for_add_to_study_deck'] ) {
			// If for_add_to_study_deck, then return only the cards the user is not studying currently.
			$card_group   = self
				::query()
				->whereNotIn( 'id', function ( Builder $query ) use ( $args ) {
					$query->select( 'card_group_id' )
					      ->from( SP_TABLE_USER_CARDS )
					      ->where( 'user_id', $args['user_id'] );
				} );
			$card_group_2 = self::query()
			                    ->whereNotIn( 'id', function ( Builder $query ) use ( $args ) {
				                    $query->select( 'card_group_id' )
				                          ->from( SP_TABLE_USER_CARDS )
				                          ->where( 'user_id', $args['user_id'] );
			                    } );
		} elseif ( true === $args['for_remove_from_study_deck'] ) {
			// If for_remove_from_study_deck, then return only the cards the user is studying currently.
			$card_group   = self::query()
			                    ->whereIn( 'id', function ( Builder $query ) use ( $args ) {
				                    $query->select( 'card_group_id' )
				                          ->from( SP_TABLE_USER_CARDS )
				                          ->where( 'user_id', $args['user_id'] );
			                    } );
			$card_group_2 = self
				::query()
				->whereIn( 'id', function ( Builder $query ) use ( $args ) {
					$query->select( 'card_group_id' )
					      ->from( SP_TABLE_USER_CARDS )
					      ->where( 'user_id', $args['user_id'] );
				} );
		} elseif ( true === $args['for_new_cards'] ) {
			// Get the topics of the cards the user is studying.
			$topics = Topic
				::query()
				->whereIn( 'id', function ( Builder $query ) use ( $args ) {
					$query->whereIn( 'id', function ( Builder $query ) use ( $args ) {
						$query->select( 'topic_id' )
						      ->from( SP_TABLE_CARD_GROUPS )
						      ->whereIn( 'id', function ( Builder $query ) use ( $args ) {
							      $query->select( 'card_group_id' )
							            ->from( SP_TABLE_USER_CARDS )
							            ->where( 'user_id', $args['user_id'] );
						      } );
					} );
				} )
				->get()->all();

			// Then, We need only card groups that belong to the topic but are not in the user_cards table.
			$card_group   = self
				::query()
				->whereNotIn( 'id', function ( Builder $query ) use ( $args, $topics ) {
					$query->select( 'card_group_id' )
					      ->from( SP_TABLE_USER_CARDS )
					      ->where( 'user_id', $args['user_id'] );
				} )
				->whereIn( 'topic_id', array_column( $topics, 'id' ) );
			$card_group_2 = self
				::query()
				->whereNotIn( 'id', function ( Builder $query ) use ( $args, $topics ) {
					$query->select( 'card_group_id' )
					      ->from( SP_TABLE_USER_CARDS )
					      ->where( 'user_id', $args['user_id'] );
				} )
				->whereIn( 'topic_id', array_column( $topics, 'id' ) );
		} else {
			$card_group   = self::query();
			$card_group_2 = self::query();
		}

		if ( $args['with_trashed'] ) {
			$card_group   = $card_group->withoutTrashed()::with( 'tags' );
			$card_group_2 = $card_group_2->withoutTrashed()::with( 'tags' );
		} elseif ( $args['only_trashed'] ) {
			$card_group   = $card_group->onlyTrashed();
			$card_group_2 = $card_group_2->onlyTrashed();
		} else {
			$card_group   = $card_group->with( 'tags' );
			$card_group_2 = $card_group_2->with( 'tags' );
		}

		$only_deck_group = $args['deck_group_id'] && ! $args['deck_id'] && ! $args['topic_id'];
		$only_deck       = $args['deck_group_id'] && $args['deck_id'] && ! $args['topic_id'];
		$only_topic      = $args['deck_group_id'] && $args['deck_id'] && $args['topic_id'];

		if ( $only_deck_group ) {
			$card_group   = $card_group->where( function ( $query ) use ( $args ) {
				// sub query, card_group.deck_id in (select id from decks where deck_group_id = 1)
				$query->whereIn( 'deck_id', function ( Builder $query ) use ( $args ) {
					$query->select( 'id' )
					      ->from( SP_TABLE_DECKS )
					      ->where( 'deck_group_id', $args['deck_group_id'] );
				} );
			} );
			$card_group_2 = $card_group_2->where( function ( $query ) use ( $args ) {
				// sub query, card_group.deck_id in (select id from decks where deck_group_id = 1)
				$query->whereIn( 'deck_id', function ( Builder $query ) use ( $args ) {
					$query->select( 'id' )
					      ->from( SP_TABLE_DECKS )
					      ->where( 'deck_group_id', $args['deck_group_id'] );
				} );
			} );
		} elseif ( $only_deck ) {
			$card_group   = $card_group->where( 'deck_id', $args['deck_id'] );
			$card_group_2 = $card_group_2->where( 'deck_id', $args['deck_id'] );
		} elseif ( $only_topic ) {
			$card_group   = $card_group->where( 'topic_id', $args['topic_id'] );
			$card_group_2 = $card_group_2->where( 'topic_id', $args['topic_id'] );
		}

		// Card types.
		if ( $args['card_types'] ) {
			$card_group   = $card_group->whereIn( 'card_type', $args['card_types'] );
			$card_group_2 = $card_group_2->whereIn( 'card_type', $args['card_types'] );
		}

		// Search.
		if ( $args['search'] ) {
			$card_group   = $card_group
				->where( 'name', 'like', "%{$args['search']}%" );
			$card_group_2 = $card_group_2
				->where( 'name', 'like', "%{$args['search']}%" );
		}

		// Pagination.
		$offset = ( $args['page'] - 1 );

		$all_object   = $card_group
			->with( 'deck.deck_group', 'topic', 'collection', 'cards' );
		$total_object = $card_group_2;

		$all_object->offset( $offset )
		           ->limit( $args['per_page'] )
		           ->orderByDesc( 'id' );

		$all = $all_object->get();

		// Convert table and image card questions and anwers to array.
		foreach ( $all as $card_group ) {
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

		$ret = [
			'items' => $all->all(),
			'total' => $total_object->get()->count()
		];

		return $ret;
	}


	/** @noinspection UnknownColumnInspection */
	public static function get_card_groups_simple_with_ordering__( $args ): array {
		$default = [
			'search'                     => '',
			'page'                       => 1,
			'per_page'                   => 5,
			'with_trashed'               => false,
			'only_trashed'               => false,
			'deck_group_id'              => null,
			'deck_id'                    => null,
			'topic_id'                   => null,
			'card_types'                 => null,
			'tags_ids'                   => [],
			'from_front_end'             => false,
			'for_add_to_study_deck'      => false,
			'for_remove_from_study_deck' => false,
			'for_new_cards'              => false,
			'user_id'                    => null,
			'order_by_deck_group_name'   => false,
			'order_by_deck_name'         => false,
			'order_by_topic'             => false,
		];

		$args = wp_parse_args( $args, $default );

		if (
			$args['from_front_end']
			&& $args['deck_group_id'] === null
			&& $args['deck_id'] === null
			&& $args['topic_id'] === null
			&& ! $args['for_add_to_study_deck']
			&& ! $args['for_remove_from_study_deck']
			&& ! $args['for_new_cards']
			&& $args['user_id'] === null
		) {
			return [
				'items' => [],
				'total' => 0,
			];
		}

		$query = '
			SELECT cg.*, 
				   COUNT(c.id) as cards_count, 
				   d.name as deck_name, 
				   dg.name as deck_group_name, 
				   t.name as topic_name, 
				   cl.name as collection_name, 
				   c.id as card_id, 
				   uc.id as user_card_id
			FROM ' . SP_TABLE_CARD_GROUPS . ' as cg
			LEFT JOIN ' . SP_TABLE_DECKS . ' AS d ON d.id = cg.deck_id
			LEFT JOIN ' . SP_TABLE_DECK_GROUPS . ' AS dg ON dg.id = d.deck_group_id
			LEFT JOIN ' . SP_TABLE_TOPICS . ' AS t ON t.id = cg.topic_id
			LEFT JOIN ' . SP_TABLE_COLLECTIONS . ' AS cl ON cl.id = cg.collection_id
			LEFT JOIN ' . SP_TABLE_CARDS . ' AS c ON c.card_group_id = cg.id
			LEFT JOIN ' . SP_TABLE_USER_CARDS . ' AS uc ON uc.card_group_id = cg.id
			WHERE cg.deck_id IN (SELECT id FROM ' . SP_TABLE_DECKS . ' WHERE deck_group_id = ' . $args['deck_group_id'] . ') 
			AND 1 = 1
			GROUP BY cg.id
		';

		if ( $args['for_add_to_study_deck'] ) {
			$query .= '
				AND cg.id NOT IN (
					SELECT card_group_id
					FROM ' . SP_TABLE_USER_CARDS . '
					WHERE user_id = ' . $args['user_id'] . '
				)
			';
		} elseif ( $args['for_remove_from_study_deck'] ) {
			$query .= '
				AND cg.id IN (
					SELECT card_group_id
					FROM ' . SP_TABLE_USER_CARDS . '
					WHERE user_id = ' . $args['user_id'] . '
				)
			';
		} elseif ( $args['for_new_cards'] ) {
			$user_study    = sp_get_user_study( $args['user_id'] );
			$user_study_id = $user_study->id;
//			$last_answered_card_ids = UserCard::get_all_last_answered_user_cards(
//				$args['user_id'],
//				$user_study_id );
//			$new_cards_not_answered_but_added = UserCard::get_new_cards_not_answered_but_added( $args['user_id'],
//				$user_study_id,
//				$last_answered_card_ids['card_ids'] );
			$ignored_card_group_ids = sp_get_user_ignored_card_group_ids( $args['user_id'] );

			$_card_groups_ids_being_studied =
				UserCard
					::query()
					->where( 'user_id', $args['user_id'] )
					->get()->pluck( 'card_group_id' )->all();

			$topic_ids_being_studied =
				CardGroup
					::query()
					->whereIn( 'id', $_card_groups_ids_being_studied )
					->get()->pluck( 'topic_id' )
					->unique()->all();

			$query .= '
				AND t.id IN (' . implode( ',', $topic_ids_being_studied ) . ')
				AND cg.id NOT IN (' . implode( ',', $ignored_card_group_ids ) . ')
				AND cg.id NOT IN (' . implode( ',', $_card_groups_ids_being_studied ) . ')
			';
		}

		if ( $args['with_trashed'] ) {
			// Handle with_trashed condition
		} elseif ( $args['only_trashed'] ) {
			$query .= ' AND cg.deleted_at IS NOT NULL';
		} else {
			// Handle other conditions
		}

		$only_deck_group = $args['deck_group_id'] && ! $args['deck_id'] && ! $args['topic_id'];
		$only_deck       = $args['deck_group_id'] && $args['deck_id'] && ! $args['topic_id'];
		$only_topic      = $args['deck_group_id'] && $args['deck_id'] && $args['topic_id'];

		if ( $only_deck_group ) {
			$query .= '
				AND cg.deck_id IN (
					SELECT id
					FROM ' . SP_TABLE_DECKS . '
					WHERE deck_group_id = ' . $args['deck_group_id'] . '
				)
			';
		} elseif ( $only_deck ) {
			$query .= ' AND cg.deck_id = ' . $args['deck_id'];
		} elseif ( $only_topic ) {
			$query .= ' AND cg.topic_id = ' . $args['topic_id'];
		}

		if ( $args['card_types'] ) {
			$query .= ' AND cg.card_type IN (' . implode( ',', $args['card_types'] ) . ')';
		}

		if ( $args['search'] ) {
			$query .= " AND cg.name LIKE '%" . $args['search'] . "%'";
		}

		$card_groups_in_collection = self::get_card_groups_in_any_collections();
		if ( ! empty( $card_groups_in_collection['card_group_ids'] ) ) {
			$query .= ' AND cg.id NOT IN (' . implode( ',', $card_groups_in_collection['card_group_ids'] ) . ')';
		}

		$offset = ( $args['page'] - 1 ) * $args['per_page'];
		$query  .= ' ORDER BY cg.id LIMIT ' . $args['per_page'] . ' OFFSET ' . $offset;

//		$totalQuery = clone $query;
//		$total      = count( DB::select( $totalQuery ) );

		$results = DB::select( $query );

		$card_group_ids = array_column( $results, 'id' );

		$card_groups = self
			::query()
			->whereIn( 'id', $card_group_ids )
			->with( 'cards', 'tags', 'deck.deck_group', 'topic', 'collection' )
			->orderBy( 'id' )
			->get();

		$total = count( $results );

		foreach ( $card_groups as $card_group ) {
			foreach ( $card_group->cards as $card ) {
				$card_type = $card->card_group->card_type;
				if ( in_array( $card_type, [ 'table', 'image' ] ) ) {
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

		return [
			'items' => $card_groups->all(),
			'total' => $total,
		];
	}


	/** @noinspection UnknownColumnInspection */
	public static function get_card_groups_simple_with_ordering_save( $args ): array {
		$default = array(
			'search'                     => '',
			'page'                       => 1,
			'per_page'                   => 5,
			'with_trashed'               => false,
			'only_trashed'               => false,
			'deck_group_id'              => null,
			'deck_id'                    => null,
			'topic_id'                   => null,
			'card_types'                 => null,
			'tags_ids'                   => array(),
			'from_front_end'             => false,
			'for_add_to_study_deck'      => false,
			'for_remove_from_study_deck' => false,
			'for_new_cards'              => false,
			'user_id'                    => null,
			'order_by_deck_group_name'   => false,
			'order_by_deck_name'         => false,
			'order_by_topic'             => false,
		);

		$args = wp_parse_args( $args, $default );

		// if from front end, then either deck_group_id or deck_id or topic_id must be provided.
		if (
			true === $args['from_front_end']
			&& null === $args['deck_group_id']
			&& null === $args['deck_id']
			&& null === $args['topic_id']
			&& false === $args['for_add_to_study_deck']
			&& false === $args['for_remove_from_study_deck']
			&& false === $args['for_new_cards']
			&& false === $args['user_id']
		) {
			return [
				'items' => [],
				'total' => 0,
			];
		}

		// Join tables and apply aliases
		$query = Manager
			::table( SP_TABLE_CARD_GROUPS . ' as cg' )
			->select( 'cg.*',
				Manager::raw( 'COUNT(c.id) as cards_count' ),
				'd.name as deck_name',
				'dg.name as deck_group_name',
				't.name as topic_name',
				'cl.name as collection_name',
				'c.id as card_id',
				'uc.id as user_card_id' )
			->leftJoin( SP_TABLE_DECKS . ' AS d', 'd.id', '=', 'cg.deck_id' )
			->leftJoin( SP_TABLE_DECK_GROUPS . ' AS dg', 'dg.id', '=', 'd.deck_group_id' )
			->leftJoin( SP_TABLE_TOPICS . ' AS t', 't.id', '=', 'cg.topic_id' )
			->leftJoin( SP_TABLE_COLLECTIONS . ' AS cl', 'cl.id', '=', 'cg.collection_id' )
			->leftJoin( SP_TABLE_CARDS . ' AS c', 'c.card_group_id', '=', 'cg.id' )
			->leftJoin( SP_TABLE_USER_CARDS . ' AS uc', 'uc.card_group_id', '=', 'cg.id' );

		// Apply conditions based on parameters
//		if ( $args['from_front_end'] ) {
//			$query->where( function ( $query ) use ( $args ) {
//				$query->whereNotNull( 'cg.deck_group_id' )
//				      ->orWhereNotNull( 'cg.deck_id' )
//				      ->orWhereNotNull( 'cg.topic_id' )
//				      ->orWhere( 'uc.for_add_to_study_deck', true )
//				      ->orWhere( 'uc.for_remove_from_study_deck', true )
//				      ->orWhere( 'uc.for_new_cards', true )
//				      ->orWhereNotNull( 'uc.user_id' );
//			} );
//		}

		if ( $args['for_add_to_study_deck'] ) {
			// If for_add_to_study_deck, then return only the cards the user is not studying currently.
			$query->whereNotIn( 'cg.id', function ( $query ) use ( $args ) {
				$query->select( 'card_group_id' )
				      ->from( SP_TABLE_USER_CARDS )
				      ->where( 'user_id', $args['user_id'] );
			} );
		} elseif ( $args['for_remove_from_study_deck'] ) {
			// If for_remove_from_study_deck, then return only the cards the user is studying currently.
			$query->whereIn( 'cg.id', function ( $query ) use ( $args ) {
				$query->select( 'card_group_id' )
				      ->from( SP_TABLE_USER_CARDS )
				      ->where( 'user_id', $args['user_id'] );
			} );
		} elseif ( $args['for_new_cards'] ) {
			// Get the topics of the cards the user is studying.

//			$user_study             = sp_get_user_study( $args['user_id'] );
//			$user_study_id          = $user_study->id;
//			$last_answered_card_ids = UserCard::get_all_last_answered_user_cards( $args['user_id'], $user_study_id );
//			$new_cards_not_answered_but_added = UserCard::get_new_cards_not_answered_but_added( $args['user_id'], $user_study_id, $last_answered_card_ids['card_ids'] );
			$ignored_card_group_ids = sp_get_user_ignored_card_group_ids( $args['user_id'] );

			// Get group ids being studied.
			$_card_groups_ids_being_studied =
				UserCard
					::query()
					->where( 'user_id', $args['user_id'] )
					->get()->pluck( 'card_group_id' )->all();

			// With the group ids, get their topic ids.
			$topic_ids_being_studied =
				CardGroup
					::query()
					->whereIn( 'id', $_card_groups_ids_being_studied )
					->get()->pluck( 'topic_id' )
					->unique()->all();

			$query
				// Then limit the topics to card groups that belong to the topics the user is studying.
				->whereIn( 't.id', $topic_ids_being_studied )
				// Also exclude the card groups the user ignored.
				->whereNotIn( 'cg.id', $ignored_card_group_ids )
				// Also exclude the card groups ids already being studied.
				->whereNotIn( 'cg.id', $_card_groups_ids_being_studied );
		}

		if ( $args['with_trashed'] ) {
//			$query->whereNotNull( 'cg.deleted_at' )->with( 'tags' );
		} elseif ( $args['only_trashed'] ) {
			$query->whereNotNull( 'cg.deleted_at' );
		} else {
//			$query->with( 'tags' );
		}

		$only_deck_group = $args['deck_group_id'] && ! $args['deck_id'] && ! $args['topic_id'];
		$only_deck       = $args['deck_group_id'] && $args['deck_id'] && ! $args['topic_id'];
		$only_topic      = $args['deck_group_id'] && $args['deck_id'] && $args['topic_id'];

		if ( $only_deck_group ) {
			// Add conditions for 'only_deck_group'
			$query->whereIn( 'cg.deck_id', function ( Builder $query ) use ( $args ) {
				$query->select( 'id' )
				      ->from( SP_TABLE_DECKS )
				      ->where( 'deck_group_id', $args['deck_group_id'] );
			} );
		} elseif ( $only_deck ) {
			$query->where( 'cg.deck_id', $args['deck_id'] );
		} elseif ( $only_topic ) {
			$query->where( 'cg.topic_id', $args['topic_id'] );
		}

		// Card types.
		if ( $args['card_types'] ) {
			$query->whereIn( 'cg.card_type', $args['card_types'] );
		}

		// Search.
		if ( $args['search'] ) {
			$query->where( 'cg.name', 'like', '%' . $args['search'] . '%' );
		}

		// Remove all card groups in any collection.
		$card_groups_in_collection = self::get_card_groups_in_any_collections();
		$query->whereNotIn( 'cg.id', $card_groups_in_collection['card_group_ids'] );

		$offset = ( $args['page'] - 1 ) * $args['per_page'];

		$totalQuery = clone $query; // Clone the query for total count
		$total      = $totalQuery->count();

		$result = $query
			->offset( $offset )
			->limit( $args['per_page'] )
//			->groupBy( 'deck_group_name', 'deck_name', 'topic_name', 'cg.id' )
//			->orderBy( 'deck_group_name', 'asc' )
//			->orderBy( 'dg.name', 'asc' )
//			->orderBy( 'd.name', 'asc' )
			->groupBy( [ 'cg.id' ] );

//		$sql = $result->toSql();

		$total_2        = $result->count();
		$all            = $result->get()->all();
		$card_group_ids = array_column( $all, 'id' );

		$card_groups = self
			::query()
			->whereIn( 'id', $card_group_ids )
			->with( 'cards', 'tags', 'deck.deck_group', 'topic', 'collection' )
			->orderBy( 'id' )
			->get();

		// For new cards, or remove cards,  we are reading all at once. So the total is the total of the card groups.
		if ( $args['for_new_cards'] || $args['for_remove_from_study_deck'] ) {
//			$total = $card_groups->count();
		}
		$total = $total_2;

		// Convert table and image card questions and anwers to array.
		foreach ( $card_groups as $card_group ) {
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

		// Process card data as needed

		return [
			'items' => $card_groups->all(),
			'total' => $total,
		];
	}


	/** @noinspection UnknownColumnInspection */
	public static function get_card_groups_simple_with_ordering( $args ): array {
		$default = array(
			'search'                     => '',
			'page'                       => 1,
			'per_page'                   => 5,
			'with_trashed'               => false,
			'only_trashed'               => false,
			'collection_id'              => null,
			'deck_group_id'              => null,
			'deck_id'                    => null,
			'topic_id'                   => null,
			'card_types'                 => null,
			'tags_ids'                   => array(),
			'from_front_end'             => false,
			'for_add_to_study_deck'      => false,
			'for_remove_from_study_deck' => false,
			'for_new_cards'              => false,
			'user_id'                    => null,
			'order_by_deck_group_name'   => false,
			'order_by_deck_name'         => false,
			'order_by_topic'             => false,
			'topic_ids_to_exclude'       => [],
		);

		$args = wp_parse_args( $args, $default );

//		Common::send_error( 'test', array(
//			'args' => $args
//		) );

		// if from front end, then either deck_group_id or deck_id or topic_id must be provided.
		if (
			true === $args['from_front_end']
			&& null === $args['collection_id']
			&& null === $args['deck_group_id']
			&& null === $args['deck_id']
			&& null === $args['topic_id']
			&& false === $args['for_add_to_study_deck']
			&& false === $args['for_remove_from_study_deck']
			&& false === $args['for_new_cards']
			&& false === $args['user_id']
		) {
			return [
				'items' => [],
				'total' => 0,
			];
		}

		$card_groups     = self
			::get_card_groups_simple_with_ordering_query( $args );
		$cards_group_sql = $card_groups->toSql();
		$card_groups     = $card_groups->get();

		$total     = self
			::get_card_groups_simple_with_ordering_query( $args, true );
		$total_sql = $total->toSql();
		$total     = $total->get();

		if ( count( $total ) > 0 ) {
			$total = $total[0]->all_count;
		} else {
			$total = 0;
		}

		// For new cards, or remove cards,  we are reading all at once. So the total is the total of the card groups.
//		if ( $args['for_new_cards'] || $args['for_remove_from_study_deck'] ) {
//			$total = $card_groups->count();
//		}
//		$total = $total_2;

		// Convert table and image card questions and anwers to array.
		foreach ( $card_groups as $card_group ) {
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

		// Process card data as needed

		return [
			'items' => $card_groups->all(),
			'total' => $total,
		];
	}

	/**
	 *
	 * @param array $args
	 * @param array $for_total Set to true to return query that is usable to get query.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
	 */
	public static function get_card_groups_simple_with_ordering_query( $args, bool $for_total = false ) {
		// Join tables and apply aliases
		$query = Manager
			::table( SP_TABLE_CARD_GROUPS . ' as cg' )
//			->select( 'cg.*',
//				Manager::raw( 'COUNT(c.id) as cards_count' ),
//				Manager::raw( 'COUNT(*) as cards_groups_count' ),
//				'd.name as deck_name',
//				'dg.name as deck_group_name',
//				't.name as topic_name',
//				'cl.name as collection_name',
//				'c.id as card_id',
//				'uc.id as user_card_id' )
			->leftJoin( SP_TABLE_DECKS . ' AS d', 'd.id', '=', 'cg.deck_id' )
			->leftJoin( SP_TABLE_DECK_GROUPS . ' AS dg', 'dg.id', '=', 'd.deck_group_id' )
			->leftJoin( SP_TABLE_TOPICS . ' AS t', 't.id', '=', 'cg.topic_id' )
			->leftJoin( SP_TABLE_COLLECTIONS . ' AS cl', 'cl.id', '=', 'cg.collection_id' )
			->leftJoin( SP_TABLE_CARDS . ' AS c', 'c.card_group_id', '=', 'cg.id' )
			->leftJoin( SP_TABLE_USER_CARDS . ' AS uc', 'uc.card_group_id', '=', 'cg.id' );

		if ( $args['for_add_to_study_deck'] ) {
			// If for_add_to_study_deck, then return only the cards the user is not studying currently.
			$query->whereNotIn( 'cg.id', function ( $query ) use ( $args ) {
				$query->select( 'card_group_id' )
				      ->from( SP_TABLE_USER_CARDS )
				      ->where( 'user_id', $args['user_id'] );
			} );
		} elseif ( $args['for_remove_from_study_deck'] ) {
			// If for_remove_from_study_deck, then return only the cards the user is studying currently.
			$query->whereIn( 'cg.id', function ( $query ) use ( $args ) {
				$query->select( 'card_group_id' )
				      ->from( SP_TABLE_USER_CARDS )
				      ->where( 'user_id', $args['user_id'] );
			} );
		} elseif ( $args['for_new_cards'] ) {
			// Filter by the topics of the cards the user is studying.
			$ignored_card_group_ids = sp_get_user_ignored_card_group_ids( $args['user_id'] );

			// Get group ids being studied.
			$_card_groups_ids_being_studied =
				UserCard
					::query()
					->where( 'user_id', $args['user_id'] )
					->get()->pluck( 'card_group_id' )->all();

			// With the group ids, get their topic ids.
			$topic_ids_being_studied =
				CardGroup
					::query()
					->whereIn( 'id', $_card_groups_ids_being_studied )
					->get()->pluck( 'topic_id' )
					->unique()->all();

			$query
				// Then limit the topics to card groups that belong to the topics the user is studying.
				->whereIn( 't.id', $topic_ids_being_studied )
				// Also exclude the card groups the user ignored.
				->whereNotIn( 'cg.id', $ignored_card_group_ids )
				// Also exclude the card groups ids already being studied.
				->whereNotIn( 'cg.id', $_card_groups_ids_being_studied );
		}

		if ( $args['with_trashed'] ) {
//			$query->whereNotNull( 'cg.deleted_at' )->with( 'tags' );
		} elseif ( $args['only_trashed'] ) {
			$query->whereNotNull( 'cg.deleted_at' );
		} else {
//			$query->with( 'tags' );
		}

		$only_deck_group = $args['deck_group_id'] && ! $args['deck_id'] && ! $args['topic_id'];
		$only_deck       = $args['deck_id'] && ! $args['topic_id'];
		$only_topic      = $args['topic_id'];

		if ( $only_deck_group ) {
			// Add conditions for 'only_deck_group'
			// Get all cg that has a deck that is under the deck group.
			$query
				->whereIn( 'cg.deck_id', function ( Builder $query ) use ( $args ) {
					$query->select( 'id' )
					      ->from( SP_TABLE_DECKS )
					      ->where( 'deck_group_id', $args['deck_group_id'] );
				} )
				// sub group.
				->orWhere( function ( Builder $query ) use ( $args ) {
					// where cg.deck_id is null
					$query
						->where( 'cg.deck_id', '=', 0 )
						// and cg.topic_id is in the topics that are under decks that are under the deck group
						->whereIn( 'cg.topic_id', function ( Builder $query ) use ( $args ) {
							$query->select( 'id' )
							      ->from( SP_TABLE_TOPICS )
							      ->whereIn( 'deck_id', function ( Builder $query ) use ( $args ) {
								      $query->select( 'id' )
								            ->from( SP_TABLE_DECKS )
								            ->where( 'deck_group_id', $args['deck_group_id'] );
							      } );
						} );
				} );
		} elseif ( $only_deck ) {
			//(cg has no deck and cg has a topic that is under a deck that is under the deck group)
			$query
				->where( function ( Builder $query ) use ( $args ) {
					$query
						// cgs that have this deck.
						->where( 'cg.deck_id', '=', $args['deck_id'] )
						// and cg's topic belongs to this deck.
						->orWhereIn( 'cg.topic_id', function ( Builder $query ) use ( $args ) {
							$query->select( 'id' )
							      ->from( SP_TABLE_TOPICS )
							      ->where( 'deck_id', $args['deck_id'] );
						} );
				} );
		} elseif ( $only_topic ) {
			$query->where( 'cg.topic_id', $args['topic_id'] );
		}

		// Card types.
		if ( $args['card_types'] ) {
			$query->whereIn( 'cg.card_type', $args['card_types'] );
		}


		// Search.
		if ( $args['search'] ) {
			$query->where( 'cg.name', 'like', '%' . $args['search'] . '%' );
		}

		// Remove all card groups in any collection.
		$card_groups_in_collection = self::get_card_groups_in_any_collections();
		$query->whereNotIn( 'cg.id', $card_groups_in_collection['card_group_ids'] );

		$offset = ( $args['page'] - 1 ) * $args['per_page'];

		// Exclude card groups that belong to the topics to exclude.
		if ( $args['topic_ids_to_exclude'] ) {
			$query->whereNotIn( 'cg.topic_id', $args['topic_ids_to_exclude'] );
		}

		// Group by cd.id
//		$query = $query
//			->groupBy( [ 'cg.id' ] );

		// Collections.
		if ( $args['collection_id'] ) {
			$query->where( 'cg.collection_id', $args['collection_id'] );
		}

		if ( $for_total ) {
			return $query
				->select(
					Manager::raw( 'COUNT(DISTINCT cg.id) as all_count' ),
				);
//			return $query
//				->select( "cg.id" )
//				->distinct();
		} else {
			$query = $query
				->select( 'cg.*',
					Manager::raw( 'COUNT(c.id) as cards_count' ),
					Manager::raw( 'COUNT(*) as cards_groups_count' ),
					'd.name as deck_name',
					'dg.name as deck_group_name',
					't.name as topic_name',
					'cl.name as collection_name',
					'c.id as card_id',
					'uc.id as user_card_id' )
				->offset( $offset )
				->groupBy( [ 'cg.id' ] )
				->limit( $args['per_page'] );
		}

//		wp_send_json_error( array(
//			'args'  => $args,
//			'query' => $query->toSql(),
//			"all"   => $query->get()->all()
//		) );

		$all            = $query->get()->all();
		$card_group_ids = array_column( $all, 'id' );


//		Common::send_error( 'test', array(
//			'args'                => $args,
//			'offset'              => $offset,
//			'card_group_ids'      => $card_group_ids,
//			'cards_in_collection' => $card_groups_in_collection,
//		) );

		return self
			::query()
			->whereIn( 'id', $card_group_ids )
			->with( 'cards', 'tags', 'deck.deck_group', 'topic', 'collection' )
			->orderBy( 'id' );
	}


	/**
	 * Get all card groups for in any collection.
	 *
	 * @param bool $empty_if_admin This will allow only admins to see all card groups EVEN if they are in a collection.
	 *
	 * @return array
	 */
	public static function get_card_groups_in_any_collections( bool $empty_if_admin = true ): array {
		$in_admin_page = ( ! wp_doing_ajax() && is_admin() )
		                 || ( wp_doing_ajax() && current_user_can( 'manage_options' ) );


		// This will allow only admins to see all card groups EVEN if they are in a collection.
		// The floor is this is that it will not work when an admin is on the frontend.
		if ( $empty_if_admin ) {
			if ( $in_admin_page ) {
				return [
					'card_groups'    => [],
					'card_group_ids' => [],
				];
			}
		}

		$card_groups = self
			::query()
			// collection must not be null
			->whereNotNull( 'collection_id' )
			->where( 'collection_id', '!=', 0 )
			->get();


		return array(
			'card_groups'    => $card_groups->all(),
			'card_group_ids' => $card_groups->pluck( 'id' )->all(),
		);
	}

}
