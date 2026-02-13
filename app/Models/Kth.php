<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kth extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kth';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nama_kth',
        'desa',
        'kecamatan',
        'kabupaten',
        'koordinator',
        'nomor_wa',
        'id_kph',
    ];

    public function kph(): BelongsTo
    {
        return $this->belongsTo(Kph::class, 'id_kph', 'id');
    }

    public function petani(): HasMany
    {
       return $this->hasMany(Petani::class, 'id_kth', 'id');
    }
}
