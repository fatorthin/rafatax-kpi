<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id', // Allow manual ID assignment untuk sync
        'code',
        'company_name',
        'phone',
        'address',
        'owner_name',
        'owner_role',
        'contact_person',
        'npwp',
        'jenis_wp',
        'grade',
        'pph_25_reporting',
        'pph_23_reporting',
        'pph_21_reporting',
        'pph_4_reporting',
        'ppn_reporting',
        'spt_reporting',
        'status',
        'type',
    ];

    public function staff()
    {
        return $this->belongsToMany(Staff::class);
    }

    public function clientReports()
    {
        return $this->hasMany(ClientReport::class)->withTrashed();
    }

    public function team()
    {
        return $this->belongsToMany(Team::class, 'team_client', 'client_id', 'team_id');
    }
}
