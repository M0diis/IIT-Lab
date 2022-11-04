<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/admin', name: 'admin')]
    public function index(ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('admin')) {
            return $this->redirectToRoute('home');
        }

        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/tickets', name: 'admin_tickets')]
    public function adminTickets(ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('admin')) {
            return $this->redirectToRoute('home');
        }

        $ticketRepository = $doctrine->getRepository(Ticket::class);
        $userRepository = $doctrine->getRepository(User::class);

        $ticketsWithUser = [];

        foreach($ticketRepository->findBy([], ['created_timestamp' => 'DESC']) as $ticket) {
            $user = $userRepository->find($ticket->getFkUserId());

            $ticket->setUser($user);

            $ticketsWithUser[] = $ticket;
        }

        return $this->render('admin/tickets.html.twig', [
            'tickets' => $ticketsWithUser
        ]);
    }
}
