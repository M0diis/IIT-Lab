<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/contact', name: 'contact')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
            'admin' => $this->requestStack->getSession()->get('admin')
        ]);
    }

    #[Route('/contact/send', name: 'contact_post', methods: ['POST'])]
    public function contactSend(Request $request): Response
    {
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $subject = $request->request->get('subject');
        $message = $request->request->get('message');

        if(empty($name) || empty($email) || empty($subject) || empty($message)) {
            $this->addFlash('error', 'All field should be filled.');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
            'admin' => $this->requestStack->getSession()->get('admin')
        ]);
    }
}
