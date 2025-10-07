<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Staff;
use App\Models\Role;
use App\Models\JobDescription;

class LogBook extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'staff_id',
        'job_description_id',
        'date',
        'description',
        'count_task',   
    ];  

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }   

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function jobDescription()
    {
        return $this->belongsTo(JobDescription::class);
    }
}
