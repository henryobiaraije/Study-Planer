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

class AnswerLog extends Model {
    protected $table = SP_TABLE_ANSWER_LOG;

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'study_id',
        'card_id',
        'last_card_updated_at',
        'accepted_change_comment',
        'question',
        'answer',
    ];

    protected $dates = [
        'last_updated_at' => 'datetime:Y-m-d H:i:s',
    ];



    public function card() {
        return $this->belongsTo(Card::class);
    }

    public function study() {
        return $this->belongsTo(Study::class);
    }


}