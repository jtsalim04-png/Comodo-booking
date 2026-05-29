<?php

namespace App\Service\Mercure;

/**
 * Builds Mercure publisher/subscriber JWTs (HS256) without requiring the Mercure bundle.
 */
final class MercureTokenFactory
{
    public function __construct(
        private string $mercureJwtSecret,
        private string $appUrl,
    ) {
    }

    public function adminTicketsTopic(): string
    {
        return rtrim($this->appUrl, '/') . '/topics/admin/tickets';
    }

    /** @param list<string> $topics */
    public function createSubscriberToken(array $topics, int $ttlSeconds = 3600): string
    {
        return $this->encode([
            'mercure' => ['subscribe' => array_values($topics)],
            'exp' => time() + $ttlSeconds,
        ]);
    }

    /** @param list<string> $topics */
    public function createPublisherToken(array $topics, int $ttlSeconds = 3600): string
    {
        return $this->encode([
            'mercure' => ['publish' => array_values($topics)],
            'exp' => time() + $ttlSeconds,
        ]);
    }

    private function encode(array $payload): string
    {
        if ($this->mercureJwtSecret === '') {
            throw new \RuntimeException('MERCURE_JWT_SECRET is not configured.');
        }

        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $body = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', $header . '.' . $body, $this->mercureJwtSecret, true)
        );

        return $header . '.' . $body . '.' . $signature;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
