<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/home', name: 'home')]
    public function homePage(ManagerRegistry $doctrine): Response
    {
        $postRepository = $doctrine->getRepository(Post::class);

        $userRepository = $doctrine->getRepository(User::class);

        $posts = $postRepository->findBy([],
            ['created_timestamp' => 'DESC']
        );

        $postsWithUser = [];

        foreach($posts as $post) {
            $user = $userRepository->find($post->getFkUserId());

            $post->setUser($user);

            $postsWithUser[] = $post;
        }

        return $this->render('home/index.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
            'admin' => $this->requestStack->getSession()->get('admin'),
            'posts' => $postsWithUser
        ]);
    }
}
