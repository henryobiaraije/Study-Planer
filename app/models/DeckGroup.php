<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Illuminate\Database\Eloquent\Model;

	class DeckGroup extends Model {

		protected $fillable = [ 'name' ];



	}