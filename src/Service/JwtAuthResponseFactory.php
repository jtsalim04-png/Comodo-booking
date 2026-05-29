<?php

namespace App\Service;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class JwtAuthResponseFactory
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
    ) {
    }

    public function create(User $user): JsonResponse
    {
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && !$user->isVerified()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Please verify your email address before logging in',
                'verified' => false,
            ], 403);
        }

        return new JsonResponse([
            'token' => $this->jwtManager->create($user),
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'roles' => $user->getRoles(),
                'verified' => $user->isVerified(),
                'authType' => $user->getAuthType(),
            ],
        ]);
    }
}
