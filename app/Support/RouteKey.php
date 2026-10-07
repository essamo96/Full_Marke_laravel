<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Decodes the encrypted route keys (see App\Traits\EncryptsRouteKey) back into primary keys.
 *
 * A key is the Crypt ciphertext of the id and uses a random IV, so the same row has a different
 * key every time it is rendered: never compare two keys for equality, always decode them first.
 */
final class RouteKey
{
    /** The primary key behind a route key, or null when it is not one of ours. */
    public static function decrypt(mixed $value): ?int
    {
        foreach (self::candidates($value) as $candidate) {
            try {
                $id = Crypt::decryptString($candidate);
            } catch (DecryptException) {
                continue;
            }

            if (ctype_digit($id)) {
                return (int) $id;
            }
        }

        return null;
    }

    /**
     * Every spelling a key may arrive in: as sent, url-decoded, and the legacy urlencode() form.
     *
     * @return array<int, string>
     */
    public static function candidates(mixed $value): array
    {
        return array_values(array_unique(array_filter([
            $value,
            rawurldecode((string) $value),
            urldecode((string) $value),
        ], fn ($candidate) => $candidate !== null && $candidate !== '')));
    }
}
