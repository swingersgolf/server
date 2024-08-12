<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'handicap',
    ];

    public function updateFillableAttributes(array $attributes): void
    {
        $fillables = $this->getFillable();
        $flippedFillables = array_flip($fillables);
        $updateArray = array_intersect_key($attributes, $flippedFillables);
        $this->update($updateArray);
    }

}
