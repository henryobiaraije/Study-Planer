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

		protected $fillable = [
			'study_id',
			'card_id',
			'answer',
			'grade',
			'rejected_at',
		];


		public static function get_(){

		}

	}