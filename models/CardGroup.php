<?php

	namespace Model;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use StudyPlanner\Initializer;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Libs\Settings;
	use StudyPlanner\Models\Tag;

	class CardGroup extends Model {
		protected $table = SP_TABLE_CARD_GROUPS;

		use SoftDeletes;

		protected $fillable = [
			'deck_id',
			'whole_question',
			'card_type',
			'scheduled_at',
			'name',
			'reverse',
		];
		protected $appends  = [ 'card_group_edit_url' ];

		protected $casts = [
			'whole_question' => 'array',
		];


		public function cards() : \Illuminate\Database\Eloquent\Relations\HasMany {
			return $this->hasMany( Card::class );
		}

		public function deck() {
			return $this->belongsTo( Deck::class );
		}

		public function tags() {
			return $this->morphToMany( Tag::class, 'taggable', SP_TABLE_TAGGABLES );
		}

		public static function get_card_groups( $args ) : array {
			$default = [
				'search'       => '',
				'page'         => 1,
				'per_page'     => 5,
				'with_trashed' => false,
				'only_trashed' => false,
			];
			$args    = wp_parse_args( $args, $default );
			if ( $args['with_trashed'] ) {
				$card_groups = CardGroup::with( 'tags' )->withoutTrashed();
			} elseif ( $args['only_trashed'] ) {
				$card_groups = CardGroup::with( 'tags' )->onlyTrashed();
			} else {
				$card_groups = CardGroup::with( 'tags' );
			}
			$card_groups = $card_groups
				->where( 'name', 'like', "%{$args['search']}%" );

			$total       = $card_groups->count();
			$offset      = ( $args['page'] - 1 );
			$card_groups = $card_groups->offset( $offset )
				->withCount( 'cards' )
				->with( 'deck' )
				->limit( $args['per_page'] )
				->orderByDesc( 'id' )->get();

//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'$args'        => $args,
//				'$deck_groups' => $deck_groups->toSql(),
//				'getQuery'     => $deck_groups->getQuery(),
//			] );

			return [
				'total'       => $total,
				'card_groups' => $card_groups->all(),
			];
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

		public static function get_totals() : array {
			$all     = [
				'active'  => 0,
				'trashed' => 0,
			];
			$active  = CardGroup::query()
				->selectRaw( Manager::raw( 'count(*) as count' ) )
				->get();
			$trashed = CardGroup::onlyTrashed()
				->selectRaw( Manager::raw( 'count(*) as count' ) )->get();

			$all['active']  = $active[0]['count'];
			$all['trashed'] = $trashed[0]['count'];

//			Common::send_error( [
//				'query log' => Manager::getQueryLog(),
////				'active query' => $active->toSql(),
//				'$active'  => $active,
//				'$trashed' => $trashed,
//				'count'    => $active[0]['count'],
//			] );

			return $all;
		}

		protected function getCastType( $key ) {
			$card_type             = $this->card_type;
			$is_question_or_answer = in_array( $key, [ 'whole_question' ] );
			$is_table_or_image     = in_array( $card_type, [ 'image', 'table' ] );
			$make_array            = $is_question_or_answer && $is_table_or_image;

			if ( $make_array ) {
//				dd($key,$card_type,parent::getCastType( $key ));
//				$this->type;
				return parent::getCastType( $key );
			} else {
				return $this->type;
			}
		}

	}