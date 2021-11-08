<?php

	namespace Model;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Illuminate\Database\Eloquent\Model;

	class DeckGroup extends Model {

		protected $fillable = [ 'name' ];
		protected $table    = SP_DB_PREFIX.'deck_groups';



	}