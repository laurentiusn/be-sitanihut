<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasaPanen extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'masa_panen';

    protected $fillable = [
        'id_laporan', 
        'tanggal',
        'id_komoditas', 
        'harga_satuan',
        'jumlah',
        'harga_total',
    ];

    protected $casts = [
        'jumlah'   => 'float',
    ];

    public function komoditas(): BelongsTo
    {
        return $this->belongsTo(Komoditas::class, 'id_komoditas');
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'id_laporan');
    }
}
