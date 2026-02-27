<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/** Class Laporan Model ORM
 * @package App\Models
 */
class Laporan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'laporan';

    protected $fillable = [
        'id',
        'periode',
        'bulan',
        'tanggal',
        'modal',
        'nte',
        'status',
        'tanggal_tindakan',
        'tanggal_keputusan',
        'tanggal_pengajuan',
        'catatan_petani',
        'catatan_penyuluh',
        'id_user',
    ];

    protected $casts = [
        'modal'             => 'float',
        'nte'               => 'float',
        'periode'           => 'integer',
    ];


    /** Relasi ke entitas User
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /** Relasi ke entitas MasaTanam
     * @return HasMany
     */
    public function masaTanam(): HasMany
    {
        return $this->hasMany(MasaTanam::class, 'id_laporan');
    }

    /** Relasi ke entitas MasaPanen
     * @return HasMany
     */
    public function masaPanen(): HasMany
    {
        return $this->hasMany(MasaPanen::class, 'id_laporan');
    }

    /** Relasi ke entitas LampiranLaporan
     * @return HasMany
     */
    public function lampiran(): HasMany
    {
        return $this->hasMany(LampiranLaporan::class, 'id_laporan');
    }

    /** Lifecycle Events booting
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function (Laporan $laporan) {
            $laporan->lampiran()->each(function ($attachment) {
                $attachment->delete();
            });
        });

        LampiranLaporan::deleted(function (LampiranLaporan $attachment) {
            Storage::disk('public')->delete($attachment->path);
        });
    }
}
