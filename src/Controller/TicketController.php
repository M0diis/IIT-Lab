<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/tickets', name: 'tickets')]
    public function index(): Response
    {
        if(!$this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('login');
        }

        return $this->render('ticket/index.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
        ]);
    }
}
