<?php

namespace App\Services;

class IdEncoder
{
    /**
     * Encode integer ID to UUID string.
     */
    public static function encode(int $id): string
    {
        $secretKey = self::getSecretKey();
        
        // Pad the 64-bit integer ID with a HMAC-based salt (8 bytes) to ensure authenticity and uniqueness
        $hash = hash_hmac('sha256', (string)$id, $secretKey, true);
        $salt = substr($hash, 0, 8);
        
        $block = pack('J', $id) . $salt; // 16 bytes
        
        $ciphertext = openssl_encrypt($block, 'aes-128-ecb', $secretKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        
        $hex = bin2hex($ciphertext);
        
        return sprintf('%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    /**
     * Decode UUID string to integer ID.
     */
    public static function decode(string $uuid): ?int
    {
        $hex = str_replace('-', '', $uuid);
        if (strlen($hex) !== 32 || !ctype_xdigit($hex)) {
            return null;
        }
        
        $ciphertext = hex2bin($hex);
        $secretKey = self::getSecretKey();
        
        $decrypted = openssl_decrypt($ciphertext, 'aes-128-ecb', $secretKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        if ($decrypted === false || strlen($decrypted) !== 16) {
            return null;
        }
        
        $id = unpack('J', substr($decrypted, 0, 8))[1];
        $salt = substr($decrypted, 8, 8);
        
        // Verify the salt/HMAC to prevent tampering
        $expectedHash = hash_hmac('sha256', (string)$id, $secretKey, true);
        $expectedSalt = substr($expectedHash, 0, 8);
        
        if (hash_equals($expectedSalt, $salt)) {
            return $id;
        }
        
        return null;
    }

    /**
     * Determine if a string is a valid UUID pattern.
     */
    public static function isUuid(string $val): bool
    {
        return preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $val) === 1;
    }

    /**
     * Get the secret key derived from APP_KEY.
     */
    protected static function getSecretKey(): string
    {
        $key = config('app.key') ?: 'genycom-fallback-secret-key-12345';
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        return substr(hash('sha256', $key, true), 0, 16);
    }
}
