<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ToolsController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/tools', name: 'tools')]
    public function index(): Response
    {
        return $this->render('tools/index.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in')
        ]);
    }
}
