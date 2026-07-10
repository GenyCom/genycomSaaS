<?php

namespace App\Traits;

use App\Services\IdEncoder;

trait HasEncodedId
{
    /**
     * Convert the model's attributes to an array, replacing integer IDs with UUIDs in JSON.
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        return $this->encodeArrayIdentifiers($array);
    }

    /**
     * Recursively encode 'id' and any keys ending in '_id' to UUIDs in array.
     */
    protected function encodeArrayIdentifiers(array $array): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->encodeArrayIdentifiers($value);
            } elseif (is_numeric($value)) {
                if ($key === 'id' || str_ends_with($key, '_id')) {
                    $value = IdEncoder::encode((int) $value);
                }
            }
        }
        return $array;
    }
}
