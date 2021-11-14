<?php

	namespace Model;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use StudyPlanner\Libs\Common;
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


		public function cards() : \Illuminate\Database\Eloquent\Relations\HasMany {
			return $this->hasMany( Card::class );
		}

		public function deck() {
			return $this->belongsTo( Deck::class );
		}

		public function tags() {
			return $this->morphToMany( Tag::class, 'taggable', SP_TABLE_TAGGABLES );
		}


	}