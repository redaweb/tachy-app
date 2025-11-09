<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'iduser';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'motpass',
        'profil',
        'site',
        'envBloque'
    ];

    protected $hidden = [
        'motpass'
    ];

    protected $casts = [
        'envBloque' => 'boolean'
    ];

    public function getAuthPassword()
    {
        return $this->motpass;
    }
}