<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessRight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'module_name',
        'read_access',
        'write_access',
        'update_access',
        'delete_access',
    ];

    // Define relationship to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
