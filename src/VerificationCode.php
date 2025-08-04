<?php

namespace BoilingSoup\Sneeze;

use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'code',
        'type',
        'expires_at',
    ];
}
