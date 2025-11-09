<?php
// app/Models/Enveloppe.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enveloppe extends Model
{
    use HasFactory;

    protected $table = 'enveloppe';
    protected $primaryKey = 'idenveloppe';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'importation',
        'lieudebut',
        'lieufin',
        'voie',
        'dis_com',
        'site',
        'figer'
    ];

    public function courses()
    {
        return $this->hasMany(Course::class, 'idenveloppe', 'idenveloppe');
    }
}