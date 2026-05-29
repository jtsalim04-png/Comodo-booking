<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Verifies Google ID tokens via Google's tokeninfo endpoint.
 * Mobile apps must use the same OAuth Web Client ID as GOOGLE_CLIENT_ID.
 */
final class GoogleIdTokenVerifier
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $googleClientId,
    ) {
    }

    /**
     * @return array{email: string, name?: string, given_name?: string, family_name?: string, sub?: string}
     */
    public function verify(string $idToken): array
    {
        $response = $this->httpClient->request('GET', 'https://oauth2.googleapis.com/tokeninfo', [
            'query' => ['id_token' => $idToken],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \InvalidArgumentException('Invalid Google ID token.');
        }

        $data = $response->toArray(false);

        if (($data['aud'] ?? '') !== $this->googleClientId) {
            throw new \InvalidArgumentException('Google token audience mismatch.');
        }

        if (($data['email_verified'] ?? 'false') !== 'true') {
            throw new \InvalidArgumentException('Google account email is not verified.');
        }

        $email = $data['email'] ?? null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Google token did not include a valid email.');
        }

        return $data;
    }
}
