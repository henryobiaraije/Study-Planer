<?php

	namespace Model;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use DateInterval;
	use DateTime;
	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\ModelNotFoundException;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\ItemNotFoundException;
	use PDOException;
	use Staudenmeir\EloquentHasManyDeep\HasRelationships;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Libs\Settings;
	use StudyPlanner\Models\Tag;
	use function StudyPlanner\get_user_timezone_date_midnight_today;
	use function StudyPlanner\get_user_timezone_minutes_to_add;

	class Study extends Model {
		protected $table = SP_TABLE_STUDY;

		use SoftDeletes;
		use HasRelationships;

		protected $fillable = [
			'user_id',
			'study_all_on_hold',
			'no_to_revise',
			'no_of_new',
			'no_on_hold',
			'revise_all',
			'study_all_new',
			'study_all_on_hold',
		];

		protected $casts = [
			'revise_all'        => 'boolean',
			'study_all_new'     => 'boolean',
			'study_all_on_hold' => 'boolean',
			'all_tags'          => 'boolean',
		];


		public function tags() {
			return $this->morphToMany( Tag::class, 'taggable', SP_TABLE_TAGGABLES );
		}

		public function deck() {
			return $this->belongsTo( Deck::class );
		}

//		public function cards() {
//			return $this->hasMany( Card::class );
//		}


		public function cards() {
			return $this->hasManyDeep( Card::class, [
				Deck::class,
				CardGroup::class,
			] );
//			] )->where( 'user_id', '=', $user_id );
		}

		public function answers() {
			return $this->hasMany( Answered::class );
		}

		public function cardsGroups() {
			return $this->hasManyThrough( CardGroup::class, Deck::class );
		}

		public static function get_user_studies( $args ) : array {
			$user_id = get_current_user_id();
			$default = [
				'search'       => '',
				'page'         => 1,
				'per_page'     => 5,
				'with_trashed' => false,
				'only_trashed' => false,
			];
			$args    = wp_parse_args( $args, $default );
			if ( $args['with_trashed'] ) {
				$studies = Study::with( 'tags', 'deck' )->withoutTrashed();
			} elseif ( $args['only_trashed'] ) {
				$studies = Study::with( 'tags', 'deck' )->onlyTrashed();
			} else {
				$studies = Study::with( 'tags', 'deck' );
			}

			$total   = $studies->count();
			$offset  = ( $args['page'] - 1 );
			$studies = $studies->offset( $offset )
				->limit( $args['per_page'] )
				->orderByDesc( 'id' );

			$studies = $studies->get();


			return [
				'total'   => $total,
				'studies' => $studies->all(),
			];
		}

		public static function get_user_study_by_id( $study_id ) {
			return Study::with( 'tags', 'deck' )->find( $study_id );
		}


		public static function get_user_card_forecast2( $user_id, $span ) {
			$mature_card_days             = Settings::MATURE_CARD_DAYS;
			$end_date                     = null;
			$user_timezone_today_midnight = get_user_timezone_date_midnight_today( $user_id );
			if ( 'one_month' === $span ) {
				$_date = new DateTime( $user_timezone_today_midnight );
				$_date->add( new DateInterval( 'P30D' ) );
				$end_date = $_date->format( 'Y-m-d H:i:s' );
			}

			/*** Prepare basic query ***/

			$user_studies = User::find( $user_id )->studies()->get();

			foreach ( $user_studies as $study ) {
				$study_id      = $study->id;
				$cards_on_hold = self::get_user_cards_on_hold( $study_id, $user_id );

				$sq = Manager::table( SP_TABLE_ANSWERED )
					->select( 'id', 'created_at', Manager::raw( 'MIN(created_at) as min_create_date' ) )
					->groupBy( 'id' );

				$query_mature = Manager
					::table( SP_TABLE_ANSWERED . ' as a1' )
//					->join( SP_TABLE_ANSWERED . ' as a2', 'a2.id', '=', 'a1.id',function($join){
					->joinSub( $sq, 'a2', function ( $join ) {
						$join->on( 'a2.id', '=', 'a1.id' );
					} )
					->where( 'a1.study_id', '=', $study_id )
//					->having( 'day_interval', '>', 0 )
//					->where( 'a1.card_id', '=', 45 )
					->select(
						'a1.id',
						'a1.next_due_at',
						'a1.created_at',
						'a1.card_id',
//						'a2.created_at',
						Manager::raw( 'NULLIF(DATE(a1.created_at) - DATE(a2.created_at), 0) day_interval' )
					);
//					->where( Manager::raw( 'DATE_SUB(CURDATE(),INTERVAL 1 DAY)' ), '<=', 'DATE(next_due_at)' );

//				$query_mature = Manager::table( SP_TABLE_ANSWERED );

				Common::send_error( [
					'$query_mature'          => $query_mature->toSql(),
					'Manager::getQueryLog()' => Manager::getQueryLog(),
					'$get'                   => $query_mature->get(),
					'getBindings'            => $query_mature->getBindings(),
					'$study_id'              => $study_id,
					'$sq sql'                => $sq->toSql(),
					'$sq get'                => $sq->get(),
				] );

			}
		}


		public static function get_user_card_forecast( $user_id, $span ) {
			$matured_cards = self::get_user_matured_card_ids( $user_id );

			$end_date                     = null;
			$user_timezone_today_midnight = get_user_timezone_date_midnight_today( $user_id );
			$_date                        = new DateTime( $user_timezone_today_midnight );
			if ( 'one_month' === $span ) {
				$_date->add( new DateInterval( 'P30D' ) );
			} elseif ( 'three_month' === $span ) {
				$_date->add( new DateInterval( 'P3M' ) );
			} elseif ( 'one_year' === $span ) {
				$_date->add( new DateInterval( 'P1Y' ) );
			}
			if ( 'all' !== $span ) {
				$end_date = $_date->format( 'Y-m-d H:i:s' );
			}


			Common::send_error( [
				'matured_cards' => $matured_cards,
				'$end_date'     => $end_date,
				'$span'         => $span,
			] );

		}

		public static function get_user_card_forecast3( $user_id, $span ) {
			$matured_cards = self::get_user_matured_card_ids( $user_id );
			Common::send_error( [
				'matured_cards' => $matured_cards,
			] );
			$mature_card_days             = Settings::MATURE_CARD_DAYS;
			$end_date                     = null;
			$user_timezone_today_midnight = get_user_timezone_date_midnight_today( $user_id );
			if ( 'one_month' === $span ) {
				$_date = new DateTime( $user_timezone_today_midnight );
				$_date->add( new DateInterval( 'P30D' ) );
				$end_date = $_date->format( 'Y-m-d H:i:s' );
			}

			/*** Prepare basic query ***/
			$user_studies = User::find( $user_id )->studies()->get();

			foreach ( $user_studies as $study ) {
				$study_id      = $study->id;
				$cards_on_hold = self::get_user_cards_on_hold( $study_id, $user_id );

				$query_mature = Manager
					::table( SP_TABLE_ANSWERED . ' as a1' )
					->where( 'a1.study_id', '=', $study_id )
					->select(
						'a1.id',
						'a1.next_due_at',
						'a1.created_at',
						'a1.card_id',
						Manager::raw( 'DATEDIFF(DATE(a1.next_due_at),DATE(a1.created_at)) next_due_interval' ),
						Manager::raw( 'DATE(a1.created_at)' )
					)
					->groupBy( 'a1.card_id' )
					->having( 'next_due_interval', '>=', 2 )
					->orderBy( 'a1.id', 'desc' );


				Common::send_error( [
					'$query_mature'          => $query_mature->toSql(),
					'Manager::getQueryLog()' => Manager::getQueryLog(),
					'$get'                   => $query_mature->get(),
					'getBindings'            => $query_mature->getBindings(),
					'$study_id'              => $study_id,
				] );

			}
		}

		/**
		 * Returns cards whose next due date of the last answer is >= Settings::MATURE_CARD_DAYS
		 *
		 * @param $user_id
		 */
		public static function get_user_matured_card_ids( $user_id ) {
			$mature_card_days = Settings::MATURE_CARD_DAYS;
			$all              = [];
			$all_card_ids     = [];
			/*** Prepare basic query ***/
			$user_studies = User::find( $user_id )->studies()->get();

			foreach ( $user_studies as $study ) {
				$study_id = $study->id;

				$matured_answers = Answered::with( 'card', 'study' )
					->where( 'study_id', '=', $study_id )
					->select(
						'id',
						'next_due_at',
						'created_at',
						'card_id',
						Manager::raw( 'DATEDIFF(DATE(next_due_at),DATE(created_at)) next_due_interval' ),
						Manager::raw( 'DATE(created_at)' )
					)->groupBy( 'card_id' )
					->having( 'next_due_interval', '>=', $mature_card_days )
					->orderBy( 'id', 'desc' );

//				$query_mature = Manager
//					::table( SP_TABLE_ANSWERED . ' as a1' )
//					->where( 'a1.study_id', '=', $study_id )
//					->select(
//						'a1.id',
//						'a1.next_due_at',
//						'a1.created_at',
//						'a1.card_id',
//						Manager::raw( 'DATEDIFF(DATE(a1.next_due_at),DATE(a1.created_at)) next_due_interval' ),
//						Manager::raw( 'DATE(a1.created_at)' )
//					)
//					->groupBy( 'a1.card_id' )
//					->having( 'next_due_interval', '>=', $mature_card_days )
//					->orderBy( 'a1.id', 'desc' );

				$get = $matured_answers->get();
				foreach ( $get as $answer ) {
					$all_card_ids[] = $answer->card->id;
					$all[]          = [
						'card_id' => $answer->card->id,
						'answer'  => $answer,
						'study'   => $study,
						'deck'    => $study->deck,
					];
				}
//				Common::send_error( [
//					'$matured_answers'       => $matured_answers->toSql(),
//					'$matured_answers get'   => $matured_answers->get(),
////					'$query_mature'          => $query_mature->toSql(),
//					'Manager::getQueryLog()' => Manager::getQueryLog(),
////					'$get'                   => $query_mature->get(),
////					'getBindings'            => $query_mature->getBindings(),
//					'$study_id'              => $study_id,
//				] );

			}

			return [
				'card_ids' => $all_card_ids,
				'all'      => $all,
			];
		}

		public static function get_user_card_forecast____diff_between_last_2( $user_id, $span ) {
			$mature_card_days             = Settings::MATURE_CARD_DAYS;
			$end_date                     = null;
			$user_timezone_today_midnight = get_user_timezone_date_midnight_today( $user_id );
			if ( 'one_month' === $span ) {
				$_date = new DateTime( $user_timezone_today_midnight );
				$_date->add( new DateInterval( 'P30D' ) );
				$end_date = $_date->format( 'Y-m-d H:i:s' );
			}

			/*** Prepare basic query ***/

			$user_studies = User::find( $user_id )->studies()->get();

			foreach ( $user_studies as $study ) {
				$study_id      = $study->id;
				$cards_on_hold = self::get_user_cards_on_hold( $study_id, $user_id );

				$sq = Manager::table( SP_TABLE_ANSWERED )
					->select( 'id', 'created_at', Manager::raw( 'MIN(created_at) as min_create_date' ) )
					->groupBy( 'id' );

				$query_mature = Manager
					::table( SP_TABLE_ANSWERED . ' as a1' )
					->where( 'a1.study_id', '=', $study_id )
					->join( SP_TABLE_ANSWERED . ' as a2', 'a2.id', '=', Manager::raw( '(a1.id + 1)' ) )
//					->having( 'day_interval', '>', 0 )
//					->where( 'a1.card_id', '=', 45 )
					->select(
						'a1.id',
						'a1.next_due_at',
						'a1.created_at',
						'a1.card_id',
						'a2.id as a2_id',
						Manager::raw( 'DATEDIFF(a2.created_at,a1.created_at) ' ),
						Manager::raw( 'a2.id - a1.id' ),
						Manager::raw( '(a1.id + 1) as plus_1' ),
						Manager::raw( 'DATE(a2.created_at)' )
//						'a2.created_at',
//						Manager::raw( 'NULLIF(DATE(a1.created_at) - DATE(a2.created_at), 0) day_interval' )
					);
//					->where( Manager::raw( 'DATE_SUB(CURDATE(),INTERVAL 1 DAY)' ), '<=', 'DATE(next_due_at)' );

//				$query_mature = Manager::table( SP_TABLE_ANSWERED );

				Common::send_error( [
					'$query_mature'          => $query_mature->toSql(),
					'Manager::getQueryLog()' => Manager::getQueryLog(),
					'$get'                   => $query_mature->get(),
					'getBindings'            => $query_mature->getBindings(),
					'$study_id'              => $study_id,
					'$sq sql'                => $sq->toSql(),
					'$sq get'                => $sq->get(),
				] );

			}
		}


		public static function get_user_cards( $study_id, $user_id ) {

			try {
				$user_timezone_minutes_from_now = get_user_timezone_minutes_to_add( $user_id );
				$_date_today                    = Common::getDateTime();
				$_datetime                      = new DateTime( $_date_today );
				$_datetime->modify( "$user_timezone_minutes_from_now minutes" );
				$datetime_from_due = $_datetime->format( 'Y-m-d H:i:s' );

//				Common::send_error( [
//					'$_date_today'                    => $_date_today,
//					'$datetime_from_due'              => $datetime_from_due,
//					'$user_timezone_minutes_from_now' => $user_timezone_minutes_from_now,
//				] );

				$study             = Study::with( 'tags' )->find( $study_id );
				$deck_id           = $study->deck_id;
				$tags              = [];
				$add_all_tags      = $study->all_tags;
				$study_all_new     = $study->study_all_new;
				$revise_all        = $study->revise_all;
				$study_all_on_hold = $study->study_all_on_hold;
				$no_of_new         = $study->no_of_new;
				$no_on_hold        = $study->no_on_hold;

				if ( ! $add_all_tags ) {
					$tags = $study->tags->pluck( 'id' );
				}


				$cards_query = Manager::table( SP_TABLE_CARDS . ' as c' )
					->leftJoin( SP_TABLE_CARD_GROUPS . ' as cg', 'cg.id', '=', 'c.card_group_id' )
					->leftJoin( SP_TABLE_DECKS . ' as d', 'd.id', '=', 'cg.deck_id' )
					->leftJoin( SP_TABLE_TAGGABLES . ' as tg', 'tg.taggable_id', '=', 'cg.id' )
					->leftJoin( SP_TABLE_TAGS . ' as t', 't.id', '=', 'tg.tag_id' )
					->where( 'tg.taggable_type', '=', CardGroup::class )
					->select(
						'c.id as card_id',
						'd.id as deck_id',
						'cg.card_type as card_type',
						'cg.id as card_group_id',
						't.name as tag_name',
						'tg.taggable_type as taggable_type'
					);

				if ( ! $add_all_tags ) {
					$cards_query = $cards_query->whereIn( 't.id', $tags );
				}

				$cards_query = $cards_query->where( 'd.id', '=', $deck_id )
					->groupBy( 'c.id' );
//				->where( 'tb.taggable_type', '=', CardGroup::class )
//				dd(
//					$cards_query->toSql(),
//					$cards_query->getBindings(),
//					$date_today, $user_timezone,
//					$timezones[ $user_timezone ],
//					$timezones
//				);
//				dd( $cards_query->toSql() );
				// In this deck
				// In those tags
				//


//				$study = Study::with( [
//					'deck.cards',
//					'deck.cards.card_group',
//					'answers' => function ( $query ) use ( $date_today ) {
////						$query->where( 'next_due_at', '<', $date_today );
//						$query->where( 'id', '>', 14 );
////						dd( $query->toSql() );
//					},
//				] )
//					->where( 'id', '=', $study_id )
//					->where( 'user_id', '=', $user_id );


//				$study = $study->get()->firstOrFail();
//				$cards = $study->deck->cards;

				Common::send_error( [
					__METHOD__,
					'$study'                 => $study,
					'$tags'                  => $tags,
					'$add_all_tags'          => $add_all_tags,
					'card_get'               => $cards_query->get(),
					'card_query_sql'         => $cards_query->toSql(),
//					'$cards'                 => $cards,
					'Manager::getQueryLog()' => Manager::getQueryLog(),
					'study_id'               => $study_id,
				] );


				return [
					'cards' => $cards,
				];

			} catch ( ItemNotFoundException $e ) {
				//todo handle later
				return [
					'cards' => [],
				];
			}


		}

		public static function get_user_cards_on_hold( $study_id, $user_id, $particular_date = null ) {

			try {
				$user_timezone_today_midnight = get_user_timezone_date_midnight_today( $user_id );

				$study        = Study::with( 'tags' )->findOrFail( $study_id );
				$deck_id      = $study->deck_id;
				$tags         = [];
				$add_all_tags = $study->all_tags;
				$revise_all   = $study->revise_all;
				$no_to_revise = $study->no_to_revise;

				if ( ! $add_all_tags ) {
					$tags = $study->tags->pluck( 'id' );
				}

				/**
				 * Get all cards
				 * In "card groups" in the "deck" in the "study"
				 * Next due date is <= today midnight + timezone
				 * Distinct by card_id
				 * Only cards that have been answered before (not in cards revised today , except "agiain")
				 * Grade is hold
				 */

				/*** Get all cards revised today answered today (To exclude them later if "false === $study->no_to_revise") ***/
				$query_revised_today                 = Answered
					::where( 'study_id', '=', $study_id )
					->where( 'created_at', '>', $user_timezone_today_midnight )
//					->whereNotIn( 'grade', [ 'again' ] )
					->where( 'answered_as_revised', '=', true );
				$card_ids_revised_today              = $query_revised_today->pluck( 'card_id' );
				$count_revised_today                 = $card_ids_revised_today->count();
				$no_of_new_remaining_to_revise_today = $no_to_revise - $count_revised_today;

//				Common::send_error( [
//					'sql'                                  => $query_revised_today->toSql(),
//					'getBindings'                          => $query_revised_today->getBindings(),
//					'$card_ids_revised_today'              => $card_ids_revised_today,
//					'$no_of_new_remaining_to_revise_today' => $no_of_new_remaining_to_revise_today,
//					'$user_timezone_today_midnight'        => $user_timezone_today_midnight,
//				] );

				/*** Prepare basic query ***/
				$cards_query = Manager::table( SP_TABLE_CARDS . ' as c' )
					->leftJoin( SP_TABLE_CARD_GROUPS . ' as cg', 'cg.id', '=', 'c.card_group_id' )
					->leftJoin( SP_TABLE_DECKS . ' as d', 'd.id', '=', 'cg.deck_id' )
					->leftJoin( SP_TABLE_TAGGABLES . ' as tg', 'tg.taggable_id', '=', 'cg.id' )
					->leftJoin( SP_TABLE_TAGS . ' as t', 't.id', '=', 'tg.tag_id' )
					->where( 'tg.taggable_type', '=', CardGroup::class )
					->select(
						'c.id as card_id'
					);

				/*** Add just a few tags? ***/
				if ( ! $add_all_tags ) {
					$cards_query = $cards_query->whereIn( 't.id', $tags );
				}

				/*** Revise a few cards? ***/
				if ( ! $revise_all ) {
					$cards_query = $cards_query->limit( $no_of_new_remaining_to_revise_today );
				}

				/*** Return only those answered before (Not in cards revised today) and grade = hold ***/
				$cards_query = $cards_query
					->whereIn( 'c.id', function ( $q ) use (
						$user_timezone_today_midnight,
						$card_ids_revised_today,
						$study_id
					) {
						$q->select( 'card_id' )->from( SP_TABLE_ANSWERED )
							->whereNotIn( 'card_id', $card_ids_revised_today )
//							->whereIn( 'grade', [ 'hold' ] )
							->where( 'study_id', $study_id )
							->where( 'next_due_at', '<=', $user_timezone_today_midnight )
							->distinct();
//						dd( $q->toSql(), $q->getBindings(), $q->get() );
					} );
//				dd( $cards_query->toSql(), $cards_query->getBindings(),$cards_query->get() );

				/*** Group by c.id "To prevent duplicate results being returned" **/
				$cards_query = $cards_query->where( 'd.id', '=', $deck_id )
					->groupBy( 'c.id' );
//				dd( $cards_query->toSql(), $cards_query->getBindings(),$cards_query->get() );

				$card_ids = $cards_query->pluck( 'card_id' );

				/*** Get the cards ***/
				$all_cards = Card::with( 'card_group', 'card_group.deck' )
					->whereIn( 'id', $card_ids );
//				dd(
//					$card_ids,
//					$all_cards->toSql(),
//					$all_cards->getBindings(),
//					$all_cards->get(),
//					$cards_query->toSql(),
//					$cards_query->getBindings(),
//					$cards_query->get()
//				);


//				Common::send_error( [
//					__METHOD__,
//					'$all_cards toSql'       => $all_cards->toSql(),
//					'$all_cards'             => $all_cards->get(),
//					'$study'                 => $study,
//					'$card_ids'                 => $card_ids,
//					'$tags'                  => $tags,
//					'$add_all_tags'          => $add_all_tags,
//					'card_get'               => $cards_query->get(),
//					'card_query_sql'         => $cards_query->toSql(),
////					'$cards'                 => $cards,
//					'Manager::getQueryLog()' => Manager::getQueryLog(),
//					'study_id'               => $study_id,
//				] );


				return [
					'cards' => $all_cards->get(),
				];

			} catch ( ItemNotFoundException $e ) {
				//todo handle later
				return [
					'cards' => [],
				];
			} catch ( ModelNotFoundException $e ) {
				//todo handle later
				return [
					'cards' => [],
				];
			}


		}

		public static function get_user_cards_to_revise( $study_id, $user_id ) {

			try {
				$user_timezone_today_midnight = get_user_timezone_date_midnight_today( $user_id );

				$study        = Study::with( 'tags' )->findOrFail( $study_id );
				$deck_id      = $study->deck_id;
				$tags         = [];
				$add_all_tags = $study->all_tags;
				$revise_all   = $study->revise_all;
				$no_to_revise = $study->no_to_revise;

				if ( ! $add_all_tags ) {
					$tags = $study->tags->pluck( 'id' );
				}

				/**
				 * Get all cards
				 * In "card groups" in the "deck" in the "study"
				 * Next due date is <= today midnight + timezone
				 * Distinct by card_id
				 * Only cards that have been answered before (not in cards revised today , except "agiain")
				 *
				 */

				/*** Get all cards revised today answered today (To exclude them later if "false === $study->no_to_revise") ***/
				$query_revised_today                 = Answered::where( 'study_id', '=', $study_id )
					->where( 'created_at', '>', $user_timezone_today_midnight )
					->whereNotIn( 'grade', [ 'again' ] )
					->where( 'study_id', '=', $study_id )
					->where( 'answered_as_revised', '=', true );
				$card_ids_revised_today              = $query_revised_today->pluck( 'card_id' );
				$count_revised_today                 = $card_ids_revised_today->count();
				$no_of_new_remaining_to_revise_today = $no_to_revise - $count_revised_today;

//				Common::send_error( [
//					'sql'                                  => $query_revised_today->toSql(),
//					'getBindings'                          => $query_revised_today->getBindings(),
//					'$card_ids_revised_today'              => $card_ids_revised_today,
//					'$no_of_new_remaining_to_revise_today' => $no_of_new_remaining_to_revise_today,
//				] );

				/*** Prepare basic query ***/
				$cards_query = Manager::table( SP_TABLE_CARDS . ' as c' )
					->leftJoin( SP_TABLE_CARD_GROUPS . ' as cg', 'cg.id', '=', 'c.card_group_id' )
					->leftJoin( SP_TABLE_DECKS . ' as d', 'd.id', '=', 'cg.deck_id' )
					->leftJoin( SP_TABLE_TAGGABLES . ' as tg', 'tg.taggable_id', '=', 'cg.id' )
					->leftJoin( SP_TABLE_TAGS . ' as t', 't.id', '=', 'tg.tag_id' )
					->where( 'tg.taggable_type', '=', CardGroup::class )
					->select(
						'c.id as card_id'
					);

				/*** Add just a few tags? ***/
				if ( ! $add_all_tags ) {
					$cards_query = $cards_query->whereIn( 't.id', $tags );
				}

				/*** Revise a few cards? ***/
				if ( ! $revise_all ) {
					$cards_query = $cards_query->limit( $no_of_new_remaining_to_revise_today );
				}

				/*** Filter out cards revised today today "Except those with grade as 'again' and 'hold' " ***/
//				$cards_query = $cards_query
//					->whereNotIn( 'c.id', $card_ids_revised_today );

				/*** Filter out cards answered today with grade not "again" ***/
//				$cards_query = $cards_query
//					->whereNotIn( 'c.id', function ( $q ) use ( $user_timezone_today_midnight ) {
//						$q->select( 'card_id' )->from( SP_TABLE_ANSWERED )
//							->where( 'grade', '!=', 'again' );
//					} );

				/*** Return only those answered before (Not in cards revised today) ***/
				$cards_query = $cards_query
					->whereIn( 'c.id', function ( $q ) use (
						$user_timezone_today_midnight,
						$card_ids_revised_today,
						$study_id
					) {
						$q->select( 'card_id' )->from( SP_TABLE_ANSWERED )
							->whereNotIn( 'card_id', $card_ids_revised_today )
							->whereNotIn( 'grade', [ 'hold' ] )
							->where( 'study_id', $study_id )
							->where( 'next_due_at', '<=', $user_timezone_today_midnight )
							->distinct();
//						dd( $q->toSql(), $q->getBindings(),$q->get() );
					} );
//				dd( $cards_query->toSql(), $cards_query->getBindings(),$cards_query->get() );

				/*** Group by c.id "To prevent duplicate results being returned" **/
				$cards_query = $cards_query->where( 'd.id', '=', $deck_id )
					->groupBy( 'c.id' );
//				dd( $cards_query->toSql(), $cards_query->getBindings(),$cards_query->get() );

				$card_ids = $cards_query->pluck( 'card_id' );

				/*** Get the cards ***/
				$all_cards = Card::with( 'card_group', 'card_group.deck' )
					->whereIn( 'id', $card_ids );
//				dd(
//					$card_ids,
//					$all_cards->toSql(),
//					$all_cards->getBindings(),
//					$all_cards->get(),
//					$cards_query->toSql(),
//					$cards_query->getBindings(),
//					$cards_query->get()
//				);


//				Common::send_error( [
//					__METHOD__,
//					'$all_cards toSql'       => $all_cards->toSql(),
//					'$all_cards'             => $all_cards->get(),
//					'$study'                 => $study,
//					'$card_ids'                 => $card_ids,
//					'$tags'                  => $tags,
//					'$add_all_tags'          => $add_all_tags,
//					'card_get'               => $cards_query->get(),
//					'card_query_sql'         => $cards_query->toSql(),
////					'$cards'                 => $cards,
//					'Manager::getQueryLog()' => Manager::getQueryLog(),
//					'study_id'               => $study_id,
//				] );


				return [
					'cards' => $all_cards->get(),
				];

			} catch ( ItemNotFoundException $e ) {
				//todo handle later
				return [
					'cards' => [],
				];
			} catch ( ModelNotFoundException $e ) {
				//todo handle later
				return [
					'cards' => [],
				];
			}


		}

		public static function get_user_cards_new( $study_id, $user_id ) {

			try {
				$user_timezone_today_midnight = get_user_timezone_date_midnight_today( $user_id );

				$study         = Study::with( 'tags' )->findOrFail( $study_id );
				$deck_id       = $study->deck_id;
				$tags          = [];
				$add_all_tags  = $study->all_tags;
				$study_all_new = $study->study_all_new;
				$no_of_new     = $study->no_of_new;

				if ( ! $add_all_tags ) {
					$tags = $study->tags->pluck( 'id' );
				}

				/*** Get all new cards answered today "Only those answered once and today are truly new" ***/
				$query_new_answered_today     = Answered::where( 'study_id', '=', $study_id )
					->where( 'created_at', '>', $user_timezone_today_midnight )
					->where( 'grade', '!=', 'again' )
					->where( 'answered_as_new', '=', true );
				$new_card_ids_answered_today  = $query_new_answered_today->pluck( 'card_id' );
				$count_new_studied_today      = $new_card_ids_answered_today->count();
				$no_of_new_remaining_to_study = $no_of_new - $count_new_studied_today;

//				Common::send_error( [
//					'sql'                           => $query_new_answered_today->toSql(),
//					'getBindings'                   => $query_new_answered_today->getBindings(),
//					'$count_new_studied_today'      => $count_new_studied_today,
//					'$no_of_new_remaining_to_study' => $no_of_new_remaining_to_study,
//					'$new_card_ids_answered_today'  => $new_card_ids_answered_today,
//				] );

				/*** Prepare basic query ***/
				$cards_query = Manager::table( SP_TABLE_CARDS . ' as c' )
					->leftJoin( SP_TABLE_CARD_GROUPS . ' as cg', 'cg.id', '=', 'c.card_group_id' )
					->leftJoin( SP_TABLE_DECKS . ' as d', 'd.id', '=', 'cg.deck_id' )
					->leftJoin( SP_TABLE_TAGGABLES . ' as tg', 'tg.taggable_id', '=', 'cg.id' )
					->leftJoin( SP_TABLE_TAGS . ' as t', 't.id', '=', 'tg.tag_id' )
					->where( 'tg.taggable_type', '=', CardGroup::class )
//					->whereNotIn( 'c.id', function ( $q ) use ( $study_id ) {
//						$q->select( 'card_id' )->from( SP_TABLE_ANSWERED . ' as a' )
//							->where( 'study_id', '=', $study_id )
//							->distinct();
//					} )
					->select(
						'c.id as card_id'
					);

				/*** Add just a few tags? ***/
				if ( ! $add_all_tags ) {
					$cards_query = $cards_query->whereIn( 't.id', $tags );
				}

				/*** Study just a few new cards? ***/
				if ( ! $study_all_new ) {
					$cards_query = $cards_query->limit( $no_of_new_remaining_to_study );
				}

				/*** Filter out new cards answered today "Except those with grade as 'again' " ***/
				$cards_query = $cards_query
					->whereNotIn( 'c.id', $new_card_ids_answered_today );

				/*** Filter out cards answerd today with grade not "again" ***/
				$cards_query = $cards_query
					->whereNotIn( 'c.id', function ( $q ) use ( $user_timezone_today_midnight ) {
						$q->select( 'card_id' )->from( SP_TABLE_ANSWERED )
							->where( 'grade', '!=', 'again' );
					} );

				/*** Filter out cards answerd before today ***/
				$cards_query = $cards_query
					->whereNotIn( 'c.id', function ( $q ) use ( $user_timezone_today_midnight ) {
						$q->select( 'card_id' )->from( SP_TABLE_ANSWERED )
							->where( 'created_at', '<', $user_timezone_today_midnight );
					} );

				/*** Group by c.id "To prevent duplicate results being returned" **/
				$cards_query = $cards_query->where( 'd.id', '=', $deck_id )
					->groupBy( 'c.id' );

				$card_ids = $cards_query->pluck( 'card_id' );

				/*** Get the cards ***/
				$all_cards = Card::with( 'card_group', 'card_group.deck' )
					->whereIn( 'id', $card_ids );
//				dd(
//					$card_ids,
//					$all_cards->toSql(),
//					$all_cards->getBindings(),
//					$all_cards->get(),
//					$cards_query->toSql(),
//					$cards_query->getBindings(),
//					$cards_query->get()
//				);


//				Common::send_error( [
//					__METHOD__,
//					'$all_cards toSql'       => $all_cards->toSql(),
//					'$all_cards'             => $all_cards->get(),
//					'$study'                 => $study,
//					'$tags'                  => $tags,
//					'$add_all_tags'          => $add_all_tags,
//					'card_get'               => $cards_query->get(),
//					'card_query_sql'         => $cards_query->toSql(),
////					'$cards'                 => $cards,
//					'Manager::getQueryLog()' => Manager::getQueryLog(),
//					'study_id'               => $study_id,
//				] );


				return [
					'cards' => $all_cards->get(),
				];

			} catch ( ItemNotFoundException $e ) {
				//todo handle later
				return [
					'cards' => [],
				];
			} catch ( ModelNotFoundException $e ) {
				//todo handle later
				return [
					'cards' => [],
				];
			}


		}

		public static function get_user_cards_to_study( $study_id, $user_id ) {
			$all_cards = [];

			$user_cards_new     = Study::get_user_cards_new( $study_id, $user_id );
			$user_cards_revise  = Study::get_user_cards_to_revise( $study_id, $user_id );
			$user_cards_on_hold = Study::get_user_cards_on_hold( $study_id, $user_id );

			foreach ( $user_cards_new['cards'] as $one ) {
				$one->answering_type = 'New Card';
				$all_cards[]         = $one;
			}
			foreach ( $user_cards_on_hold['cards'] as $one ) {
				$one->answering_type = 'Previously On hold';
				$all_cards[]         = $one;
			}
			foreach ( $user_cards_revise['cards'] as $one ) {
				$one->answering_type = 'Revising Card';
				$all_cards[]         = $one;
			}
//			foreach ( $all_cards as $card ) {
//				if ( 'table' === $card->card_group->card_type ) {
////					$card->question = json_decode( $card->question );
//					$card->answer   = json_decode( $card->answer );
//				} elseif ( 'image' === $card->card_group->card_type ) {
//					$card->question = json_decode( $card->question );
//					$card->answer   = json_decode( $card->answer );
//				}
//			}

			return $all_cards;
		}

		public static function get_study_due_summary( $study_id, $user_id ) {
			$new_cards       = self::get_user_cards_new( $study_id, $user_id )['cards'];
			$cards_to_revise = self::get_user_cards_to_revise( $study_id, $user_id )['cards'];
			$cards_on_hold   = self::get_user_cards_on_hold( $study_id, $user_id )['cards'];

			return [
				'new'              => count( $new_cards ),
				'revision'         => count( $cards_to_revise ),
				'previously_false' => count( $cards_on_hold ), // todo on hold is used instead of previously false. Clarify later from client
				'new_cards'        => $new_cards, //todo remove after testing
			];
		}


	}