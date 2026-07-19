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
        $id = $this->getKey();
        // Return a short hash/encrypted string instead of the raw ID
        return urlencode(Crypt::encryptString($id));
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
        try {
            $decryptedId = Crypt::decryptString(urldecode($value));
            return $this->where($field ?? $this->getRouteKeyName(), $decryptedId)->first();
        } catch (DecryptException $e) {
            abort(404);
        }
    }
}
