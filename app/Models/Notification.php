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

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function markAsRead()
    {
        $this->read_at = now();
        $this->save();
    }

    public function markAsUnread()
    {
        $this->read_at = null;
        $this->save();
    }

    public function isRead()
    {
        return $this->read_at !== null;
    }

    public function isUnread()
    {
        return $this->read_at === null;
    }

    public function isType($type)
    {
        return $this->type === $type;
    }

    public function isForUser($user)
    {
        return $this->user_id === $user->id;
    }

    public function scopeOfType($query, NotificationType $type)
    {
        return $query->where('type', $type->value);
    }
}
