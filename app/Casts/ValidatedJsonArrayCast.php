<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ValidatedJsonArrayCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): array
    {
        return json_decode($value, true) ?? [];
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        if (is_string($value)) {
            $value = array_filter(array_map('trim', explode(',', $value)));
        }
        $value = array_values(array_unique(array_slice($value, 0, 7)));
        foreach ($value as $item) {
            if (! preg_match('/^[A-Z]{6}\d{3}$/', $item)) {
                throw new \InvalidArgumentException("Formato inválido: $item");
            }
        }

        return json_encode($value);
    }
}
