<?php
// app/Models/Freinage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Freinage extends Model
{
    use HasFactory;

    protected $table = 'freinage';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'type',
        'vitesse',
        'interstation',
        'details',
        'heure',
        'idcourse'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'idcourse', 'idcourse');
    }
}