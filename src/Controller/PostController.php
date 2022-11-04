<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Post;
use App\Entity\Ticket;
use App\Entity\TicketMessage;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/post/create', name: 'post_create')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('admin')) {
            return $this->redirectToRoute('home');
        }

        $title = $request->request->get('post_title');
        $content = $request->request->get('post_content');

        if(empty($title) || empty($content)) {
            $this->addFlash('error', 'Title and content are required fields.');

            return $this->redirectToRoute('admin');
        }

        $post = new Post();

        $post
            ->setTitle($title)
            ->setContent($content)
            ->setCreatedTimestamp(date('Y-m-d H:i:s'))
            ->setFkUserId($this->requestStack->getSession()->get('user_id'));

        $entityManager = $doctrine->getManager();

        $entityManager->persist($post);

        $entityManager->flush();

        $this->addFlash('info', 'Post successfully created!');

        return $this->redirectToRoute('admin');
    }

    #[Route('/post/delete/{id}', name: 'post_delete', methods: ['POST'])]
    public function ticketDelete($id, Request $request, ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('admin')) {
            return $this->redirectToRoute('tickets');
        }

        $postRepository = $doctrine->getRepository(Post::class);

        $post = $postRepository->find($id);

        if(!$post) {
            $this->addFlash('error', 'Post not found.');

            return $this->redirectToRoute('home');
        }

        $entityManager = $doctrine->getManager();

        $entityManager->remove($post);

        $entityManager->flush();

        $this->addFlash('info', 'Post successfully deleted!');

        return $this->redirectToRoute('home');
    }
}
