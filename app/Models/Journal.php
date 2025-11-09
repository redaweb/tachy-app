<?php
// app/Models/Journal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $table = 'journal';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'matricule',
        'nom',
        'ladate',
        'heure',
        'action',
        'detail',
        'site'
    ];

    protected $casts = [
        'ladate' => 'date'
    ];
}