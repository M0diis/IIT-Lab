<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/about', name: 'about')]
    public function index(): Response
    {
        return $this->render('about/index.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
            'admin' => $this->requestStack->getSession()->get('admin')
        ]);
    }
}
