<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\NotificationType;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'user_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'type' => NotificationType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function scopeOfType($query, NotificationType $type)
    {
        return $query->where('type', $type->value);
    }
}
