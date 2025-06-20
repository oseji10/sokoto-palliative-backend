<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeneficiaryType extends Model
{
    use HasFactory;

    public $table = 'beneficiary_type';
    protected $fillable = [
        'typeId',
        'typeName',
        
    ];
    protected $primaryKey = 'typeId';

    protected $hidden = ['created_at', 'updated_at'];
}
