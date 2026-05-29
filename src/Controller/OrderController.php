<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use App\Service\TicketPurchaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/orders')]
class OrderController extends AbstractController
{
    #[Route('', name: 'order_list', methods: ['GET'])]
    public function list(EventRepository $eventRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $events = $eventRepository->findBy([], ['eventDate' => 'ASC']);

        return $this->render('order/list.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/my-tickets', name: 'order_my_tickets', methods: ['GET'])]
    public function myTickets(TicketRepository $ticketRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $tickets = $ticketRepository->findBy(
            ['customer' => $user],
            ['purchaseDate' => 'DESC']
        );

        return $this->render('order/my_tickets.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/{id}', name: 'order_show', methods: ['GET'])]
    public function show(Event $event, TicketRepository $ticketRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $ticket = null;
        $user = $this->getUser();
        if ($user) {
            $ticket = $ticketRepository->findOneBy(
                ['event' => $event, 'customer' => $user],
                ['purchaseDate' => 'DESC']
            );
        }

        return $this->render('order/show.html.twig', [
            'event' => $event,
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}/purchase', name: 'order_purchase', methods: ['POST'])]
    public function purchase(
        Event $event,
        Request $request,
        TicketPurchaseService $ticketPurchaseService,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('purchase' . $event->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid purchase request.');
            return $this->redirectToRoute('order_show', ['id' => $event->getId()]);
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $ticketPurchaseService->purchase($user, $event);

        $this->addFlash('success', 'Ticket purchased successfully! Your payment is marked as completed.');

        return $this->redirectToRoute('order_show', ['id' => $event->getId()]);
    }
}


