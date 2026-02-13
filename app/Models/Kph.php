<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kph extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kph';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nama_kph',
        'wilayah',
        'alamat',
        'nomor_wa',
        'jenis'
    ];

    public function kth():HasMany{
        return $this->hasMany(Kth::class, 'id_kph', 'id');
    }

    public function penyuluh(): HasMany
    {
        return $this->hasMany(Penyuluh::class, 'id_kph', 'id');
    }
}
