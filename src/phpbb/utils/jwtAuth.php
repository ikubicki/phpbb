<?php

namespace phpbb\utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ramsey\Uuid\Uuid;
use stdclass;
use Throwable;

class jwtAuth
{
    private static function generateKey(): string
    {
        return (string) Uuid::uuid4();
    }

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

    public static function getKidAndKey(): array
    {
        $kid = str_replace('-', '', self::generateKey());
        $key = self::generateKey();
        $filename = '/tmp/key-' . $kid;
        file_put_contents($filename, $key);
        return [$kid, $key];
    }

    public static function getJwt(array $payload): string
    {
        list($kid, $key) = self::getKidAndKey();
        return JWT::encode($payload, $key, 'HS512', $kid);
    }

    public static function getPayload(string $token): ?stdclass
    {
        try {
            $key = self::extractKey($token);
            $payload = JWT::decode($token, $key);
        }
        catch(Throwable $throwable) {
            $payload = null;
        }
        return $payload;
    }

    public static function getKey($kid): ?Key
    {
        $filename = '/tmp/key-' . $kid;
        if (file_exists($filename)) {
            $key = file_get_contents($filename);
            return new Key($key, 'HS512');
        }
        return false;
    }
}