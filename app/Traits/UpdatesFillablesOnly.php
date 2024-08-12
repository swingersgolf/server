<?php

namespace App\Traits;

trait UpdatesFillablesOnly
{
    public function updateFillableAttributesOnly(array $attributes): void
    {
        $fillables = $this->getFillable();
        $flippedFillables = array_flip($fillables);
        $updateArray = array_intersect_key($attributes, $flippedFillables);
        $this->update($updateArray);
    }
}
