<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Class RefreshToken Model ORM
 * @package App\Models
 */
class RefreshToken extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'refresh_token';

    protected $guarded = ['id'];

    /** Mass assignable attributes
     * @var list<string>
     */
    protected $fillable = [
        'id_user',
        'device_unq',
        'device_name',
        'token',
        'expires_at',
    ];

    /** Relasi ke entitas User
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}