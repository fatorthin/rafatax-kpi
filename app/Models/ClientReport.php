<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientReport extends Model
{
    use SoftDeletes;

    protected $table = 'client_report';

    protected $fillable = [
        'client_id',
        'staff_id',
        'report_month',
        'report_date',
        'report_content',
        'score',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class)->withTrashed();
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Staff::class, 'verified_by')->withTrashed();
    }
}
