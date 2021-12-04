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

		protected $casts = [
			'question' => 'array',
			'answer'   => 'array',
		];

		public function answered() {
			return $this->hasMany( Answered::class );
		}

		public function card_group() {
			return $this->belongsTo( CardGroup::class );
		}

		protected function getCastType( $key ) {
			$card_type             = $this->card_group->card_type;
			$is_question_or_answer = in_array( $key, [ 'question', 'answer' ] );
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