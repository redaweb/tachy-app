<?php
// app/Models/Exces.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exces extends Model
{
    use HasFactory;

    protected $table = 'exces';
    protected $primaryKey = 'idexce';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'aire',
        'maxx',
        'autorise',
        'categorie',
        'interstation',
        'debut',
        'fin',
        'idcourse',
        'detail'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'idcourse', 'idcourse');
    }
}