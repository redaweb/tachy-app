<?php
// app/Models/Conducteur.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conducteur extends Model
{
    use HasFactory;

    protected $table = 'conducteurs';
    protected $primaryKey = 'matricule';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenom',
        'site'
    ];

    public function courses()
    {
        return $this->hasMany(Course::class, 'matricule', 'matricule');
    }
}