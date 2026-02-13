<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LampiranLaporan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'lampiran_laporan';

    protected $fillable = [
        'id_laporan', 
        'nama_file', 
        'path',
    ];

    protected $appends =['url'];

    // protected function url(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn () => $this->path ? Storage::url($this->path) : null
    //     );
    // }

    public function getUrlAttribute(): ?string
    {
        // Use the asset() helper, which is proven to work in your environment.
        if ($this->path) {
            return asset('storage/' . $this->path);
        }
        return null;
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'id_laporan');
    }
}