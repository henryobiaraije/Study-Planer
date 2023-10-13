<?php

namespace Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
	);
	protected $appends  = array( 'card_group_edit_url' );

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
			// $card_groups = CardGroup::onlyTrashed();
			// Common::send_error([
			// __METHOD__,
			// '$card_groups tr nl' => $card_groups->toSql(),
			// 'getBindings'        => $card_groups->getBindings(),
			// 'get'                => $card_groups->get(),
			// '$args'              => $args,
			// ]);
		} else {
			$card_groups = self::with( 'tags' );
		}
		$card_groups = $card_groups
			->where( 'name', 'like', "%{$args['search']}%" );

		// Common::send_error([
		// __METHOD__,
		// '$card_groups' => $card_groups->toSql(),
		// 'getBindings' => $card_groups->getBindings(),
		// 'get' => $card_groups->get(),
		// '$args' => $args,
		// ]);
		$total       = $card_groups->count();
		$offset      = ( $args['page'] - 1 );
		$card_groups = $card_groups->offset( $offset )
								   ->withCount( 'cards' )
								   ->with( 'deck' )
								   ->limit( $args['per_page'] )
								   ->orderByDesc( 'id' )->get();

		// Common::send_error( [
		// 'ajax_admin_load_deck_group',
		// '$args'        => $args,
		// '$deck_groups' => $deck_groups->toSql(),
		// 'getQuery'     => $deck_groups->getQuery(),
		// ] );

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

}
