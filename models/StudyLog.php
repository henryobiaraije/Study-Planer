<?php

namespace Model;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use StudyPlanner\Libs\Common;
use StudyPlanner\Models\Tag;

class StudyLog extends Model
{
    protected $table = SP_TABLE_STUDY_LOG;
    public $timestamps = ['created_at'];
    const UPDATED_AT = null;
    protected $fillable = [
        'study_id',
        'card_id',
        'action',
    ];

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }


}