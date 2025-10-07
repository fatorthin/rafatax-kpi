<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'pic_id',
    ];

    public function pic()
    {
        return $this->belongsTo(Staff::class, 'pic_id');
    }

    public function client()
    {
        return $this->belongsToMany(Client::class, 'team_client', 'team_id', 'client_id');
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'team_staff', 'team_id', 'staff_id');
    }
}
