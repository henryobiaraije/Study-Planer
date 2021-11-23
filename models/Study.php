<?php

	namespace Model;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use Illuminate\Support\ItemNotFoundException;
	use PDOException;
	use Staudenmeir\EloquentHasManyDeep\HasRelationships;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Libs\Settings;
	use StudyPlanner\Models\Tag;

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

		public function cardsGroups() {

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

		public static function get_user_cards( $study_id, $user_id ) {

			try {
				$date_today    = Common::getDateTime();
				$user_timezone = get_option( Settings::UM_USER_TIMEZONE, null );
				if(empty($user_timezone)){

				}
				$study         = Study::with( 'deck.cards', 'deck.cards.card_group' )
					->where( 'id', '=', $study_id )
					->where( 'user_id', '=', $user_id )->get()->firstOrFail();
				$cards         = $study->deck->cards;

				Common::send_error( [
					__METHOD__,
					'$study'                 => $study,
					'$cards'                 => $cards,
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


	}