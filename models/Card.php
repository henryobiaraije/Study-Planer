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

	class Card extends Model {
		protected $table = SP_TABLE_CARDS;

		use SoftDeletes;

		protected $fillable = [
			'bg_image_id',
			'card_group_id',
			'question',
			'c_number',
			'hash',
			'answer',
			'x_position',
			'y_position',
		];

		public function card_group() {
			return $this->belongsTo( CardGroup::class );
		}

	}