<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectoryAccessRule extends Model
{
    protected $fillable = ['path','access','trusted_subnets','user_id'];
    protected $casts = [
        'trusted_subnets' => 'array',
    ];
}
