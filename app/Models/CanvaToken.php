<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/CanvaToken.php
class CanvaToken extends Model
{
    protected $fillable = ['access_token', 'refresh_token', 'expires_at'];
    public $timestamps = false;

    public static function current(): ?self
    {
        return self::first();       // one-row table is fine
    }
}
