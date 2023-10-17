<?php

namespace StudyPlanner\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Model\Card;
use Model\CardGroup;
use Model\Deck;
use Model\User;
use StudyPlanner\Libs\Common;
use StudyPlanner\Models\Tag;

class UserCard extends Model {
	protected $table = SP_TABLE_USER_CARDS;

	use SoftDeletes;

	protected $dates = array( 'deleted_at' );
	protected $fillable = array( 'user_id', 'card_group_id' );

	public function user(): BelongsTo {
		return $this->belongsTo( User::class, 'user_id' );
	}
	public function card_group(): BelongsTo {
		return $this->belongsTo( CardGroup::class, 'card_group_id' );
	}

}
