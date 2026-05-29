<?php

namespace App\Controller;

use App\Service\Mercure\MercureTokenFactory;
use App\Service\Mercure\RealtimePublisher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/realtime')]
final class ApiRealtimeController extends AbstractController
{
    public function __construct(
        private RealtimePublisher $realtimePublisher,
        private MercureTokenFactory $tokenFactory,
        private string $mercurePublicUrl,
    ) {
    }

    #[Route('/config', name: 'api_realtime_config', methods: ['GET'])]
    public function config(): JsonResponse
    {
        $topic = $this->tokenFactory->adminTicketsTopic();
        $hubUrl = $this->mercurePublicUrl !== ''
            ? rtrim($this->mercurePublicUrl, '/') . '/.well-known/mercure'
            : '';

        return $this->json([
            'enabled' => $this->realtimePublisher->isEnabled(),
            'hubUrl' => $hubUrl,
            'topic' => $topic,
        ]);
    }

    #[Route('/mercure-token', name: 'api_realtime_mercure_token', methods: ['GET'])]
    public function mercureToken(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->realtimePublisher->isEnabled()) {
            return $this->json([
                'enabled' => false,
                'message' => 'Mercure is not configured on the server.',
            ], 503);
        }

        $topic = $this->tokenFactory->adminTicketsTopic();

        return $this->json([
            'enabled' => true,
            'token' => $this->tokenFactory->createSubscriberToken([$topic]),
            'topic' => $topic,
            'hubUrl' => rtrim($this->mercurePublicUrl, '/') . '/.well-known/mercure',
        ]);
    }
}
