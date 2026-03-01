<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use Throwable;

class OrderTrackingToken
{
    public static function encode(int $orderId): string
    {
        $encrypted = Crypt::encryptString((string) $orderId);

        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }

    public static function decode(string $token): ?int
    {
        $normalized = strtr($token, '-_', '+/');
        $padded = str_pad($normalized, strlen($normalized) + ((4 - strlen($normalized) % 4) % 4), '=', STR_PAD_RIGHT);
        $encrypted = base64_decode($padded, true);

        if ($encrypted === false) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($encrypted);

            if (!ctype_digit($decrypted)) {
                return null;
            }

            return (int) $decrypted;
        } catch (Throwable) {
            return null;
        }
    }
}

