<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceReviewReference extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'name',
        'description',
        'group',
        'type',
        'period_id',
    ];

    public function period()
    {
        return $this->belongsTo(PeriodPerformanceReview::class);
    }
}
