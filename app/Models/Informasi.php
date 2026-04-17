<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Class Informasi Model ORM
 * @package App\Models
 */
class Informasi extends Model
{
    use HasUuids, HasFactory;
    protected $table = 'informasi';

    /** Mass assignable attributes
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'desc',
        'date',
        'loc',
        'organizer',
        'nama_image',
        'path_image'
    ];
}