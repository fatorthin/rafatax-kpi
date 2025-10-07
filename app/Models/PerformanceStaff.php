<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceStaff extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'performance_reference_id',
        'staff_id',
        'supervisor_score',
        'self_score',
    ];

    public function performanceReference()
    {
        return $this->belongsTo(PerformanceReviewReference::class);
    }

    public function staff() 
    {
        return $this->belongsTo(Staff::class);
    }   
}