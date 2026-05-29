<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class GoogleAuthService
{
    public const AUTH_TYPE_GOOGLE = 'google';
    public const AUTH_TYPE_LOCAL = 'local';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private GoogleIdTokenVerifier $idTokenVerifier,
    ) {
    }

    public function authenticateWithIdToken(string $idToken): User
    {
        $googleData = $this->idTokenVerifier->verify($idToken);
        $email = (string) $googleData['email'];

        $user = $this->userRepository->findOneBy([
            'email' => $email,
            'authType' => self::AUTH_TYPE_GOOGLE,
        ]);

        if ($user instanceof User) {
            $user->setIsVerified(true);
            $user->setVerificationToken(null);
            $this->entityManager->flush();

            return $user;
        }

        $existing = $this->userRepository->findOneBy(['email' => $email]);
        if ($existing instanceof User && $existing->getAuthType() !== self::AUTH_TYPE_GOOGLE) {
            throw new \RuntimeException(
                'This email is already registered with email/password. Sign in with your password instead.'
            );
        }

        return $this->createGoogleUser($googleData);
    }

    /**
     * @param array<string, mixed> $googleData
     */
    private function createGoogleUser(array $googleData): User
    {
        $email = (string) $googleData['email'];
        $name = (string) ($googleData['name'] ?? $email);
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $firstName = (string) ($googleData['given_name'] ?? ($parts[0] ?? 'Google'));
        $lastName = (string) ($googleData['family_name'] ?? (count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'User'));

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRole('ROLE_USER');
        $user->setAuthType(self::AUTH_TYPE_GOOGLE);
        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, bin2hex(random_bytes(32)))
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
