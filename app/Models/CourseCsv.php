<?php
// app/Models/CourseCsv.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCsv extends Model
{
    use HasFactory;

    protected $table = 'coursecsv';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'km',
        'temps',
        'vitesse',
        'dsstop',
        'FU',
        'M1',
        'M2',
        'gong',
        'freinage',
        'traction',
        'patin',
        'klaxon',
        'idcourse'
    ];

    protected $casts = [
        'temps' => 'datetime',
        'dsstop' => 'boolean',
        'FU' => 'boolean',
        'M1' => 'boolean',
        'M2' => 'boolean',
        'gong' => 'boolean',
        'freinage' => 'boolean',
        'traction' => 'boolean',
        'patin' => 'boolean',
        'klaxon' => 'boolean'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'idcourse', 'idcourse');
    }
}