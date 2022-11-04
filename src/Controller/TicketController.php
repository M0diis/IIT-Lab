<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Review;
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

        return $this->renderTickets($doctrine);
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

        if(empty($content)) {
            $this->addFlash('error', 'Message cannot be empty.');

            return $this->redirect('/tickets/view/' . $request->request->get('ticket_id'));
        }

        $message
            ->setContent($content)
            ->setFkUserId($userID)
            ->setCreatedTimestamp(date('Y-m-d H:i:s'));

        $entityManager = $doctrine->getManager();

        $entityManager->persist($message);
        $entityManager->flush();

        $ticketMessage = new TicketMessage();

        $ticketMessage
            ->setFkMessageId($message->getId())
            ->setFkTicketId($request->request->get('ticket_id'));

        $entityManager->persist($ticketMessage);
        $entityManager->flush();

        $this->addFlash('info', 'Message created successfully.');

        return $this->redirect('/tickets/view/' . $request->request->get('ticket_id'));
    }

    #[Route('/tickets/view/{id}', name: 'ticket_view', methods: ['GET', 'HEAD'])]
    public function ticketView($id, ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('login');
        }

        $userRepository = $doctrine->getRepository(User::class);

        $ticketRepository = $doctrine->getRepository(Ticket::class);

        $ticket = $ticketRepository->find($id);

        $userID = $this->requestStack->getSession()->get('user_id');

        if(!$this->requestStack->getSession()->get('admin') && $ticket->getFkUserId() != $userID) {
            return $this->redirectToRoute('tickets');
        }

        $ticketMessageRepository = $doctrine->getRepository(TicketMessage::class);

        $ticketMessages = $ticketMessageRepository->findBy(
            ['fk_ticketId' => $id]
        );

        $messageRepository = $doctrine->getRepository(Message::class);

        $messages = [];

        foreach($ticketMessages as $ticketMessage) {
            $message = $messageRepository->find($ticketMessage->getFkMessageId());
            $user = $userRepository->find($message->getFkUserId());
            $message->setUser($user);
            $messages[] = $message;
        }

        usort($messages, function($a, $b) {
            return strtotime($b->getCreatedTimestamp()) - strtotime($a->getCreatedTimestamp());
        });

        return $this->render('ticket/view.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
            'admin' => $this->requestStack->getSession()->get('admin'),
            'user' => $this->requestStack->getSession()->get('user'),
            'ticket' => $ticket,
            'messages' => $messages
        ]);
    }


    #[Route('/tickets/create', name: 'tickets_create', methods: ['POST'])]
    public function ticketsCreatePost(Request $request, ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('login');
        }

        $userID = $this->requestStack->getSession()->get('user_id');

        $ticket = new Ticket();

        $title = $request->request->get('ticket_title');
        $description = $request->request->get('ticket_description');

        if(empty($title)) {
            $this->addFlash('error', 'Subject field should be filled.');

            return $this->redirectToRoute('tickets');
        }

        if(empty($description)) {
            $this->addFlash('error', 'Description field should be filled.');

            return $this->redirectToRoute('tickets');
        }

        $ticket->setFkUserId($userID)
            ->setTitle($title)
            ->setDescription($description)
            ->setClosed(false)
            ->setCreatedTimestamp(date('Y-m-d H:i:s'));

        $entityManager = $doctrine->getManager();

        $entityManager->persist($ticket);

        $entityManager->flush();

        $this->addFlash('info', 'Ticket created successfully.');

        return $this->renderTickets($doctrine);
    }

    #[Route('/ticket/message/delete/{id}', name: 'ticket_message_delete', methods: ['POST'])]
    public function reviewDelete($id, Request $request, ManagerRegistry $doctrine): Response
    {
        $ticketID = $request->request->get('ticket_id');

        if(!$this->requestStack->getSession()->get('admin')) {
            return $this->redirectToRoute('ticket_view', [
                'ticketId' => $ticketID
            ]);
        }

        $ticketMessageRepository = $doctrine->getRepository(TicketMessage::class);

        $ticketMessage = $ticketMessageRepository->findByMessageId($id);

        $entityManager = $doctrine->getManager();

        $entityManager->remove($ticketMessage[0]);

        $entityManager->flush();

        $messageRepository = $doctrine->getRepository(Message::class);

        $message = $messageRepository->find($id);

        $entityManager->remove($message);

        $entityManager->flush();

        return $this->redirectToRoute('ticket_view', [
            'ticketId' => $ticketID
        ]);
    }


    #[Route('/ticket/delete/{id}', name: 'ticket_delete', methods: ['POST'])]
    public function ticketDelete($id, Request $request, ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('admin')) {
            return $this->redirectToRoute('tickets');
        }

        $ticketRepository = $doctrine->getRepository(Ticket::class);

        $ticketMessageRepository = $doctrine->getRepository(TicketMessage::class);

        $messageRepository = $doctrine->getRepository(Message::class);

        $ticket = $ticketRepository->find($id);

        if(is_null($ticket)) {
            return $this->redirectToRoute('admin_tickets');
        }

        $ticketMessages = $ticketMessageRepository->findBy(
            ['fk_ticketId' => $id]
        );

        $entityManager = $doctrine->getManager();

        foreach($ticketMessages as $ticketMessage) {
            $message = $messageRepository->find($ticketMessage->getFkMessageId());
            $entityManager->remove($message);
            $entityManager->remove($ticketMessage);

            $entityManager->flush();
        }

        $entityManager->remove($ticket);

        $entityManager->flush();

        return $this->redirectToRoute('tickets');
    }

    #[Route('/ticket/close/{id}', name: 'ticket_close', methods: ['POST'])]
    public function ticketClose($id, ManagerRegistry $doctrine): Response
    {
        $ticketRepository = $doctrine->getRepository(Ticket::class);

        $ticket = $ticketRepository->find($id);

        if(is_null($ticket)) {
            return $this->redirectToRoute('ticket_view', [
                'ticketId' => $id
            ]);
        }

        $ticket->setClosed(true);

        $entityManager = $doctrine->getManager();

        $entityManager->persist($ticket);

        $entityManager->flush();

        return $this->redirectToRoute('tickets');
    }

    /**
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    public function renderTickets(ManagerRegistry $doctrine): Response
    {
        $ticketRepository = $doctrine->getRepository(Ticket::class);

        $userID = $this->requestStack->getSession()->get('user_id');

        $tickets = $ticketRepository->findBy(
            ['fk_userId' => $userID],
            ['created_timestamp' => 'DESC']
        );

        return $this->render('ticket/index.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
            'user' => $this->requestStack->getSession()->get('user'),
            'admin' => $this->requestStack->getSession()->get('admin'),
            'tickets' => $tickets
        ]);
    }
}
