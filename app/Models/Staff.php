<?php

namespace App\Models;

use App\Models\DepartmentReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'birth_place',
        'birth_date',
        'address',
        'no_ktp',
        'no_spk',
        'phone',
        'jenjang',
        'jurusan',
        'university',
        'no_ijazah',
        'tmt_training',
        'periode',
        'selesai_training',
        'department_reference_id',
        'position_reference_id',
        'is_active',
    ];

    public function departmentReference()
    {
        return $this->belongsTo(DepartmentReference::class);
    }

    public function positionReference()
    {
        return $this->belongsTo(PositionReference::class);
    }

    public function training()
    {
        return $this->belongsToMany(Training::class, 'training_staff', 'staff_id', 'training_id');
    }

    public function client()
    {
        return $this->belongsToMany(Client::class, 'client_staff', 'staff_id', 'client_id');
    }

    public function team()
    {
        return $this->belongsToMany(Team::class, 'team_staff', 'staff_id', 'team_id');
    }
}
