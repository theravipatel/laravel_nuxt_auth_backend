<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'email',
        'code',
        'created_at',
    ];
}
