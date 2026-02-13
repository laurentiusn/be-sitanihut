<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penyuluh extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'penyuluh';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nama_penyuluh',
        'nip',
        'nomor_wa',
        'jenis_kelamin',
        'jabatan',
        'id_kph',
    ];

    public function kph(): BelongsTo
    {
        return $this->belongsTo(Kph::class, 'id_kph', 'id');
    }
}
