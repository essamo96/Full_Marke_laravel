<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

trait EncryptsRouteKey
{
    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        // Return the raw ciphertext. Laravel's URL generator applies
        // rawurlencode once when building routes. Pre-encoding here caused
        // double-encoding with route() and broken + → space decoding when
        // keys were concatenated manually in JavaScript.
        return Crypt::encryptString((string) $this->getKey());
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $candidates = array_unique(array_filter([
            $value,
            rawurldecode((string) $value),
            // Legacy keys were built with urlencode(); keep accepting them.
            urldecode((string) $value),
        ], fn ($candidate) => $candidate !== null && $candidate !== ''));

        foreach ($candidates as $candidate) {
            try {
                $decryptedId = Crypt::decryptString($candidate);
                $model = $this->where($field ?? $this->getRouteKeyName(), $decryptedId)->first();
                if ($model) {
                    return $model;
                }
            } catch (DecryptException) {
                continue;
            }
        }

        abort(404);
    }
}
