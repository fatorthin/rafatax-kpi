<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseProject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'description',
        'case_date',
        'status',
        'staff_id',
        'client_id',
        'link_dokumen',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
