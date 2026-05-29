<?php

namespace App\Controller;

use App\Service\GoogleAuthService;
use App\Service\JwtAuthResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ApiGoogleAuthController extends AbstractController
{
    public function __construct(
        private GoogleAuthService $googleAuthService,
        private JwtAuthResponseFactory $jwtAuthResponseFactory,
    ) {
    }

    #[Route('/api/auth/google', name: 'api_auth_google', methods: ['POST'])]
    public function googleAuth(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['success' => false, 'message' => 'Invalid JSON body'], 400);
        }

        $idToken = $data['idToken'] ?? $data['id_token'] ?? null;
        if (!is_string($idToken) || $idToken === '') {
            return $this->json([
                'success' => false,
                'message' => 'idToken is required',
            ], 400);
        }

        try {
            $user = $this->googleAuthService->authenticateWithIdToken($idToken);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        } catch (\RuntimeException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        }

        return $this->jwtAuthResponseFactory->create($user);
    }
}
