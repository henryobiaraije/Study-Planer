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

	class Answered extends Model {
		protected $table = SP_TABLE_ANSWERED;

		use SoftDeletes;

		protected $dateFormat = 'Y-m-d H:i:s';

		protected $fillable = [
			'study_id',
			'card_id',
			'answer',
			'grade',
			'ease_factor',
			'next_interval',
			'next_due_at',
			'rejected_at',
		];

		protected $casts = [
			'next_due_at' => 'datetime:Y-m-d H:i:s',
			'rejected_at' => 'datetime:Y-m-d H:i:s',
			'created_at'  => 'datetime:Y-m-d H:i:s',
			'updated_at'  => 'datetime:Y-m-d H:i:s',
		];


		public static function get_() {

		}

	}