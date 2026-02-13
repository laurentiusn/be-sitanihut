<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogLaporan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'log_laporan';

    const UPDATED_AT = null;

    protected $fillable = [
        'id_laporan',
        'id_pengaju',
        'id_pemeriksa',
        'status',
        'periode',
        'bulan',
    ];

    /**
     * Get the original Laporan that this log belongs to.
     */
    public function laporan()
    {
        return $this->belongsTo(Laporan::class, 'id_laporan');
    }

    /**
     * Get the User who submitted the report (the Petani).
     */
     public function pengaju()
    {
        return $this->belongsTo(User::class, 'id_pengaju');
    }

    /**
     * Get the User who reviewed the report (the Penyuluh/Sekretariat).
     */
    public function pemeriksa()
    {
        return $this->belongsTo(User::class, 'id_pemeriksa');
    }
}