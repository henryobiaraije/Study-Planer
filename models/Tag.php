<?php

	/**
	 * Tag Model
	 */

	namespace StudyPlanner\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;

	if ( ! defined( 'ABSPATH' ) ) {
		exit();// exit if accessed directly
	}

	class Tag extends Model {

		use SoftDeletes;
		protected $table = SP_DB_PREFIX . 'tags';

		protected $fillable = [
			'name',
		];


	}
