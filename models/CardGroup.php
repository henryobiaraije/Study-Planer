<?php

namespace Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Events\Dispatcher;
use PHPMailer\PHPMailer\Exception;
use StudyPlanner\Initializer;
use StudyPlanner\Libs\Common;
use StudyPlanner\Libs\Settings;
use StudyPlanner\Models\Collections;
use StudyPlanner\Models\Tag;

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
		$card_groups = $card_groups->offset( $offset )
		                           ->withCount( 'cards' )
		                           ->with( 'deck', 'topic', 'collection', 'deck.deck_group' )
		                           ->limit( $args['per_page'] )
		                           ->orderByDesc( 'id' )->get();

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
			$card_group   = self::query()
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
			$topics = Topic::query()
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
			$card_group   = self::query()
			                    ->whereNotIn( 'id', function ( Builder $query ) use ( $args, $topics ) {
				                    $query->select( 'card_group_id' )
				                          ->from( SP_TABLE_USER_CARDS )
				                          ->where( 'user_id', $args['user_id'] );
			                    } )
			                    ->whereIn( 'topic_id', array_column( $topics, 'id' ) );
			$card_group_2 = self::query()
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

		if ( $args['card_types'] ) {
			$card_group   = $card_group->whereIn( 'card_type', $args['card_types'] );
			$card_group_2 = $card_group_2->whereIn( 'card_type', $args['card_types'] );
		}

		if ( $args['search'] ) {
			$card_group   = $card_group
				->where( 'name', 'like', "%{$args['search']}%" );
			$card_group_2 = $card_group_2
				->where( 'name', 'like', "%{$args['search']}%" );
		}

		$offset     = ( $args['page'] - 1 );
		$all_object = $card_group
			->offset( $offset )
			->with( 'deck', 'topic', 'collection', 'cards' )
			->limit( $args['per_page'] )
			->orderByDesc( 'id' );

		$total = $card_group_2
			->orderByDesc( 'id' )
			->get()->count();

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

		return [
			'items' => $all->all(),
			'total' => $total,
		];
	}

}
