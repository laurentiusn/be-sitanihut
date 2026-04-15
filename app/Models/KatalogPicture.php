<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Class KatalogPicture Model ORM
 * @package App\Models
 */
class KatalogPicture extends Model
{
    use HasUuids;
    
    protected $table = 'catalog_picts';

    /** Mass assignable attributes
     * @var list<string>
     */
    protected $fillable = [
        'id_catalog',
        'nama_file',
        'path'
    ];

    /** Relasi ke entitas Katalog
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function katalog()
    {
        return $this->belongsTo(Katalog::class, 'id_catalog');
    }
}