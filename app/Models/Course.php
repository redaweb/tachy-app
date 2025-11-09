<?php
// app/Models/Course.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';
    protected $primaryKey = 'idcourse';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fichier',
        'debut',
        'fin',
        'distance',
        'ladate',
        'heure',
        'idenveloppe',
        'matricule',
        'SV',
        'SA',
        'importation',
        'RAME',
        'code',
        'FU',
        'klaxon',
        'patin',
        'gong',
        'valide',
        'site',
        'source',
        'commentaire'
    ];

    protected $casts = [
        'ladate' => 'date',
        'importation' => 'date',
        'valide' => 'boolean'
    ];

    public function conducteur()
    {
        return $this->belongsTo(Conducteur::class, 'matricule', 'matricule');
    }

    public function enveloppe()
    {
        return $this->belongsTo(Enveloppe::class, 'idenveloppe', 'idenveloppe');
    }

    public function courseCsv()
    {
        return $this->hasMany(CourseCsv::class, 'idcourse', 'idcourse');
    }

    public function exces()
    {
        return $this->hasMany(Exces::class, 'idcourse', 'idcourse');
    }

    public function freinages()
    {
        return $this->hasMany(Freinage::class, 'idcourse', 'idcourse');
    }
}