<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'role';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nama_role',
        'slug',
    ];

    public function user(): HasMany
    {
        return $this->hasMany(User::class, 'id_role', 'id');
    }
}
