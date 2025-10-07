<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrustedSubnet extends Model
{
    protected $fillable = ['cidr', 'label', 'is_enabled', 'user_id'];
    protected $casts = [
        'is_enabled' => 'bool',
    ];
}