<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserDetail extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_user',
        'nomor_induk',
        'jenis_kelamin',
        'profile_picture',
        'alamat',
        'nomor_wa',
        'pendidikan_terakhir',
        'pekerjaan_sampingan',
        'luas_lahan',
        'jabatan',
        'id_user',
    ];

    /**
     * Indicates if the model should be timestamped.
     * The migration did not include timestamps, so we set this to false.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $appends = ['profile_picture_url'];
    
    /**
     * Get the user that owns the detail.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return null;
    }
}