<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Class Kth Model ORM
 * @package App\Models
 */
class Kth extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'kth';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'nama_kth',
        'desa',
        'kecamatan',
        'kabupaten',
        'koordinator',
        'nomor_wa',
        'id_kph',
        'id_penyuluh',
    ];

    /** Relasi ke entitas Kph
     * @return BelongsTo
     */
    public function kph(): BelongsTo
    {
        return $this->belongsTo(Kph::class, 'id_kph', 'id');
    }

    /** Relasi ke entitas Petani
     * @return HasMany
     */
    public function petani(): HasMany
    {
       return $this->hasMany(Petani::class, 'id_kth', 'id');
    }

    /** Relasi ke entitas User (Penyuluh)
     * @return BelongsTo
     */
    public function penyuluh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_penyuluh', 'id');
    }
}
