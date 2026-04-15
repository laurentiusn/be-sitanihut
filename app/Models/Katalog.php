<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Katalog Model ORM
 * @package App\Models
 */
class Katalog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'catalog';

    /** Mass Assignable Attributes 
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'location',
        'kth',
        'cp1',
        'cp2',
        'price',
        'is_active',
        'id_user',
    ];

    /** Relasi ke entitas User
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /** Relasi *One-to-Many* ke entitas KatalogPicture
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pictures()
    {
        return $this->hasMany(KatalogPicture::class, 'id_catalog', 'id');
    }
}