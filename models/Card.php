<?php

namespace Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Events\Dispatcher;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Models\Tag;

class Card extends Model {
	protected $table = SP_TABLE_CARDS;
//	use HasRelationships;
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
		'updated_at',
		'created_at',
	];

	protected $casts = [
		'question' => 'array',
		'answer'   => 'array',
	];

	protected $dateFormat = 'Y-m-d H:i:s';

	protected $dates = [
		'created_at' => 'datetime:Y-m-d H:i:s',
		'updated_at' => 'datetime:Y-m-d H:i:s',
	];

	protected static function boot() {
		static::setEventDispatcher( new Dispatcher() );
		parent::boot();

		static::deleting( function ( $invoice ) {
			$invoice->answered()->delete();
		} );
	}

	public function answered() {
		return $this->hasMany( Answered::class );
	}

	public function card_group() {
		return $this->belongsTo( CardGroup::class );
	}

	public function answer_log() {
		return $this->hasOne( AnswerLog::class, 'card_id' );
	}


	//    public function studies()
	//    {
	//        return $this->hasManyThrough(Study::class, Answered::class);
	//    }
	//public function studies() {
	//			return $this->hasManyThrough( Study::class,Answered::class );
	//		}

	protected function getCastType( $key ) {
		if ( empty( $this->card_group ) ) {
			return $this->type;
		}
		$card_type             = $this->card_group->card_type;
		$is_question_or_answer = in_array( $key, [ 'question', 'answer' ] );
		$is_table_or_image     = in_array( $card_type, [ 'image', 'table' ] );
		$make_array            = $is_question_or_answer && $is_table_or_image;

		if ( $make_array ) {
//            				dd($key,$card_type,parent::getCastType( $key ));
			//				$this->type;
			return parent::getCastType( $key );
		} else {
			return $this->type;
		}
	}

}