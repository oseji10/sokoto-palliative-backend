<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    public $table = 'hospitals';
    protected $fillable = [
        'hospitalId',
        'hospitalName',
        'acronym',
        'status',
        'contactPerson',
        'location'
    ];
    protected $primaryKey = 'hospitalId';

    public function patientsHospital()
    {
        return $this->hasMany(Patient::class, 'hospital', 'hospitalId');
    } 

    public function contact_person()
    {
        return $this->belongsTo(User::class, 'contactPerson', 'id');
    } 

    public function hospital_location()
    {
        return $this->belongsTo(State::class, 'location', 'stateId');
    } 
}
