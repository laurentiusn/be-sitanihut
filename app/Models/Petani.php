<?php

namespace App\Models;


use App\Models\Kth; 
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Petani extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'petani';


    protected $primaryKey = 'id';


    protected $fillable = [
        'nama_petani',
        'nik',
        'jenis_kelamin',
        'alamat',
        'nomor_wa',
        'pendidikan_terakhir',
        'pekerjaan_sampingan',
        'luas_lahan',
        'id_kth', 
    ];


    protected $casts = [
        'id' => 'string',
    ];
    

    public $incrementing = false;

    /**
     * Get the KTH that this petani belongs to.
     */

    public function kth(): BelongsTo
    {   
        return $this->belongsTo(Kth::class, 'id_kth', 'id');
    }
}