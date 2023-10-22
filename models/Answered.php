<?php

namespace Model;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Models\Tag;

class Answered extends Model
{
    protected $table = SP_TABLE_ANSWERED;

    use SoftDeletes;

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'study_id',
        'card_id',
        'answer',
        'grade',
        'ease_factor',
        'answered_as_new',
        'answered_as_revised',
        'next_due_answered',
        'next_interval',
        'next_due_at',
        'rejected_at',
        'started_at',
        'stopped_at',
    ];

    protected $dates = [
        'next_due_at' => 'datetime:Y-m-d H:i:s',
        'rejected_at' => 'datetime:Y-m-d H:i:s',
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at'  => 'datetime:Y-m-d H:i:s',
    ];

    protected $casts = [
        'next_due_answered'   => 'boolean',
        'answered_as_revised' => 'boolean',
        'answered_as_new'     => 'boolean',
        'answer'              => 'array',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public static function get_()
    {

    }

    protected function getCastType($key)
    {
        $card_type             = $this->card->card_group->card_type;
        $is_question_or_answer = in_array($key, ['answer']);
        $is_table_or_image     = in_array($card_type, ['image', 'table']);
        $make_array            = $is_question_or_answer && $is_table_or_image;
        if ($make_array) {
//				dd( 'answered', $make_array, $key, $card_type, parent::getCastType( $key ) );
//				dd( 'answered', $key, $card_type, parent::getCastType( $key ) );

            return parent::getCastType($key);
        } else {
            return $this->type;
        }
    }

}