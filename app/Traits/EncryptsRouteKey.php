<?php

namespace App\Traits;

use App\Support\RouteKey;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

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
        return $this->resolveEncryptedBinding($value, $field, false);
    }

    /**
     * Same as resolveRouteBinding() but soft-deleted rows are found too. Laravel calls
     * this for routes declared with ->withTrashed() (restore endpoints of the library).
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveSoftDeletableRouteBinding($value, $field = null)
    {
        return $this->resolveEncryptedBinding($value, $field, true);
    }

    private function resolveEncryptedBinding($value, $field, bool $withTrashed)
    {
        foreach (RouteKey::candidates($value) as $candidate) {
            try {
                $decryptedId = Crypt::decryptString($candidate);
                $query = $this->newQuery();

                if ($withTrashed && in_array(SoftDeletes::class, class_uses_recursive($this), true)) {
                    $query->withTrashed();
                }

                $model = $query->where($field ?? $this->getRouteKeyName(), $decryptedId)->first();
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
