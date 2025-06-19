<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollees extends Model
{
    use HasFactory;

    public $table = 'enrollees';
    protected $fillable = [
        'enrolleeId',
        'firstName',
        'lastName',
        'otherNames',
        'phoneNumber',
        'email',
        'enrolleeType',
        'enrolledBy',
        'lga',
        'isActive',
    ];
    protected $primaryKey = 'enrolleeId';

    public function enrollee_type()
    {
        return $this->belongsTo(EnrolleeType::class, 'enrolleeType', 'typeId');
    } 

    public function enrolled_by()
    {
        return $this->belongsTo(User::class, 'enrolledBy', 'id');
    }   
    
    public function lga_info()
    {
        return $this->belongsTo(Lgas::class, 'lga', 'lgaId');
    }
}
