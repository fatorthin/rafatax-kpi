<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PositionReference;

class JobDescription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'position_id',
        'job_description',
        'point',
    ];

    public function position()
    {
        return $this->belongsTo(PositionReference::class, 'position_id');
    }
}
