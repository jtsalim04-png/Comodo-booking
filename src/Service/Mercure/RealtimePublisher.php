<?php

namespace App\Service\Mercure;

use App\Entity\Ticket;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Publishes ticket events to a Mercure hub (SSE) for admin mobile/web clients.
 */
final class RealtimePublisher
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private MercureTokenFactory $tokenFactory,
        private string $mercureUrl,
        private LoggerInterface $logger,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->mercureUrl !== '';
    }

    public function publishTicketPurchased(Ticket $ticket): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $event = $ticket->getEvent();
        $customer = $ticket->getCustomer();
        $topic = $this->tokenFactory->adminTicketsTopic();

        $payload = [
            'type' => 'ticket.purchased',
            'ticketId' => $ticket->getId(),
            'eventId' => $event?->getId(),
            'eventTitle' => $event?->getTitle(),
            'customerEmail' => $customer?->getEmail(),
            'price' => $ticket->getPrice(),
            'status' => $ticket->getStatus(),
            'purchaseDate' => $ticket->getPurchaseDate()?->format(\DateTimeInterface::ATOM),
        ];

        try {
            $publisherJwt = $this->tokenFactory->createPublisherToken([$topic]);
            $hub = rtrim($this->mercureUrl, '/') . '/.well-known/mercure';

            $this->httpClient->request('POST', $hub, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $publisherJwt,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'topic' => $topic,
                    'data' => json_encode($payload, JSON_THROW_ON_ERROR),
                ],
            ]);
        } catch (\Throwable $e) {
            $this->logger->warning('Mercure publish failed: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
