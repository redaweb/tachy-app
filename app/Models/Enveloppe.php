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
    protected $casts = [
        'importation' => 'datetime',
        'figer' => 'boolean',
    ];
    public function courses()
    {
        return $this->hasMany(Course::class, 'idenveloppe', 'idenveloppe');
    }

    /**
     * Scope pour filtrer par site
     */
    public function scopeForSite($query, $site)
    {
        return $query->where('site', $site);
    }

    /**
     * Scope pour filtrer les enveloppes figées
     */
    public function scopeFrozen($query)
    {
        return $query->where('figer', true);
    }

    /**
     * Scope pour filtrer les enveloppes non figées
     */
    public function scopeNotFrozen($query)
    {
        return $query->where('figer', false);
    }

    /**
     * Scope pour filtrer selon le statut de figement
     */
    public function scopeByFreezeStatus($query, $status)
    {
        if ($status === 'figer') {
            return $query->frozen();
        } elseif ($status === 'nonfiger') {
            return $query->notFrozen();
        }
        return $query;
    }
}
