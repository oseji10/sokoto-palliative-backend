<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    public $table = 'staff';
    protected $fillable = [
        'staffId',
        'effectiveFrom',
        'effectiveUntil',
        'userId',
        'staffType',
        'lga',
        'supervisor',
        'isActive',
    ];
    protected $primaryKey = 'staffId';

    public function staff_type()
    {
        return $this->belongsTo(StaffType::class, 'staffType', 'typeId');
    } 

    public function lga_info()
    {
        return $this->belongsTo(Lgas::class, 'lga', 'lgaId');
    }

    public function supervisor_info()
    {
        return $this->belongsTo(User::class, 'supervisor', 'id');
    }
    
}
