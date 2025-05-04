<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s.u'
    ];

    public function getCreatedAtAttribute($value): Carbon
    {
        return Carbon::parse($value);
    }
}
