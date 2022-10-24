<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Ticket;
use App\Entity\TicketMessage;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/tickets', name: 'tickets', methods: ['GET', 'HEAD'])]
    public function index(ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('login');
        }

        $ticketRepository = $doctrine->getRepository(Ticket::class);

        $userID = $this->requestStack->getSession()->get('user_id');

        $tickets = $ticketRepository->findBy(
            ['fk_userId' => $userID]
        );

        return $this->render('ticket/index.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
            'tickets' => $tickets
        ]);
    }

    #[Route('/ticket/message/create', name: 'ticket_message_create', methods: ['POST'])]
    public function ticketMessageCreate(Request $request, ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('login');
        }

        $userID = $this->requestStack->getSession()->get('user_id');

        $message = new Message();

        $content = $request->request->get('content');

        $message->setContent($content)
            ->setCreatedTimestamp(time())
            ->setFkUserId($userID);

        $entityManager = $doctrine->getManager();

        $entityManager->persist($message);
        $entityManager->flush();

        $ticketMessage = new TicketMessage();

        $ticketMessage->setFkMessageId($message->getId())
            ->setFkTicketId($request->request->get('ticket_id'));

        $entityManager->persist($ticketMessage);
        $entityManager->flush();

        return $this->redirect('/tickets/view/' . $request->request->get('ticket_id'));
    }

    #[Route('/tickets/view/{ticketId}', name: 'ticket_view', methods: ['GET', 'HEAD'])]
    public function ticketView(ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('login');
        }

        $userRepository = $doctrine->getRepository(User::class);

        $ticketID = $this->requestStack->getCurrentRequest()->get('ticketId');

        $ticketRepository = $doctrine->getRepository(Ticket::class);

        $ticket = $ticketRepository->find($ticketID);

        $userID = $this->requestStack->getSession()->get('user_id');

        if($ticket->getFkUserId() != $userID) {
            return $this->redirectToRoute('tickets');
        }

        $ticketMessageRepository = $doctrine->getRepository(TicketMessage::class);

        $ticketMessages = $ticketMessageRepository->findBy(
            ['fk_ticketId' => $ticketID]
        );

        $messageRepository = $doctrine->getRepository(Message::class);

        $messages = [];

        foreach($ticketMessages as $ticketMessage) {
            $message = $messageRepository->find($ticketMessage->getFkMessageId());
            $user = $userRepository->find($message->getFkUserId());
            $message->setUser($user);
            $messages[] = $message;
        }

        return $this->render('ticket/view.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
            'ticket' => $ticket,
            'messages' => $messages
        ]);
    }


    #[Route('/tickets', name: 'tickets_create', methods: ['POST'])]
    public function ticketsCreatePost(Request $request, ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('login');
        }

        $userID = $this->requestStack->getSession()->get('user_id');

        $ticket = new Ticket();

        $ticket->setFkUserId($userID)
            ->setTitle($request->request->get('ticket_title'))
            ->setDescription($request->request->get('ticket_description'))
            ->setCreatedTimestamp(time())
            ->setClosed(false);

        $entityManager = $doctrine->getManager();

        $entityManager->persist($ticket);

        $entityManager->flush();

        $ticketRepository = $doctrine->getRepository(Ticket::class);

        $userID = $this->requestStack->getSession()->get('user_id');

        $tickets = $ticketRepository->findBy(
            ['fk_userId' => $userID]
        );

        return $this->render('ticket/index.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
            'tickets' => $tickets
        ]);
    }
}
