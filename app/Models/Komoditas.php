<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Komoditas extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'komoditas';
    
    protected $primaryKey = 'id';

    protected $fillable = [
        'kode',
        'nama_komoditas',
        'jenis_komoditas',
        'nte'
    ];

    public function masaPanen(): HasMany
    {
        return $this->hasMany(MasaPanen::class, 'id_komoditas');
    }

    public function masaTanam(): HasMany
    {
        return $this->hasMany(MasaTanam::class, 'id_komoditas');
    }
}