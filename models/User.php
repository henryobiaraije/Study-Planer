<?php

	namespace Model;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use StudyPlannerPro\Libs\Common;
	use StudyPlannerPro\Models\Tag;

	class User extends Model {
		protected $table = SP_TABLE_USERS;

		protected $fillable = [
			'ID',
		];

		public function studies() {
			return $this->hasMany( Study::class, 'user_id', 'ID' );
		}
	}