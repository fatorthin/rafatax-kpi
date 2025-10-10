<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Staff;
use App\Models\ClientReport;
use App\Models\JobDescription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogBook extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'staff_id',
        'job_description_id',
        'date',
        'description',
        'count_task',
        'comment',
        'status',
        'client_report_id',
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

    public function clientReport()
    {
        return $this->belongsTo(ClientReport::class);
    }
}
