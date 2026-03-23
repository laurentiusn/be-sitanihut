<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Class Komoditas Model ORM
 * @package App\Models
 */
class Komoditas extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'komoditas';
    
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'kode',
        'nama_komoditas',
        'jenis_komoditas',
        'nte',
        'komoditas_kehutanan',
    ];

    /** Relasi ke entitas MasaPanen
     * @return HasMany
     */
    public function masaPanen(): HasMany
    {
        return $this->hasMany(MasaPanen::class, 'id_komoditas');
    }

    /** Relasi ke entitas MasaTanam
     * @return HasMany
     */
    public function masaTanam(): HasMany
    {
        return $this->hasMany(MasaTanam::class, 'id_komoditas');
    }
}