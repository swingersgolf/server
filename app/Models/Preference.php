<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
    use HasFactory;

    public const STATUS_DISLIKED = 'disliked';

    public const STATUS_PREFERRED = 'preferred';

    public const STATUS_INDIFFERENT = 'indifferent';

    public static function getAllowedStatuses(): array
    {
        return [
            self::STATUS_DISLIKED,
            self::STATUS_PREFERRED,
            self::STATUS_INDIFFERENT,
        ];
    }
}
