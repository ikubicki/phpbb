<?php

namespace phpbb\utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ramsey\Uuid\Uuid;
use stdClass;
use Throwable;

/**
 * JWT helper
 */
class jwtAuth
{
    /**
     * Generates UUID v4 string
     * 
     * @author ikubicki
     * @return string
     */
    private static function generateKey(): string
    {
        return (string) Uuid::uuid4();
    }

    /**
     * Retrieves a Key instance for a given token
     * 
     * @author ikubicki
     * @param ?string $token
     * @return ?Key
     */
    public static function extractKey(?string $token): ?Key
    {
        if (!$token) {
            return null;
        }
        $headers = explode('.', $token)[0];
        if ($headers) {
            $headers = JWT::urlsafeB64Decode($headers);
            $headers = json_decode($headers);
            return $headers->kid ? self::getKey($headers->kid) : null;
        }
        return null;
    }

    /**
     * Returns a collection of generated key ID and key
     * 
     * @author ikubicki
     * @return array
     */
    public static function getKidAndKey(): array
    {
        $kid = str_replace('-', '', self::generateKey());
        $key = self::generateKey();
        $filename = '/tmp/key-' . $kid;
        file_put_contents($filename, $key);
        return [$kid, $key];
    }

    /**
     * Generates JWT token for provided payload
     * 
     * @author ikubicki
     * @param array $payload
     * @return string
     */
    public static function getJwt(array $payload): string
    {
        list($kid, $key) = self::getKidAndKey();
        return JWT::encode($payload, $key, 'HS512', $kid);
    }

    /**
     * Returns a payload from a given token
     * 
     * @author ikubciki
     * @param ?string $roken
     * @return ?stdClass
     */
    public static function getPayload(?string $token): ?stdClass
    {
        try {
            $key = self::extractKey($token);
            $payload = JWT::decode($token, $key);
            if (($payload->exp ?? 0) < time()) {
                $payload = null;
            }
        }
        catch(Throwable $throwable) {
            $payload = null;
        }
        return $payload;
    }

    /**
     * Returns a Key instance for given key ID
     * 
     * @author ikubicki
     * @param string $kid
     * @return ?Key
     */
    public static function getKey(string $kid): ?Key
    {
        $filename = '/tmp/key-' . $kid;
        if (file_exists($filename)) {
            $key = file_get_contents($filename);
            return new Key($key, 'HS512');
        }
        return false;
    }
}