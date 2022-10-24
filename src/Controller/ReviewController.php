<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/reviews', name: 'reviews')]
    public function index(ManagerRegistry $doctrine): Response
    {
        return $this->render('review/index.html.twig', [
            'logged_in' => $this->requestStack->getSession()->get('logged_in'),
            'reviews' => $this->getReviewsByUser($doctrine)
        ]);
    }

    private function getReviewsByUser($doctrine)
    {
        $reviewRepository = $doctrine->getRepository(Review::class);

        $userRepository = $doctrine->getRepository(User::class);

        $reviews = $reviewRepository->findAll();

        $reviewsWithUser = [];

        foreach($reviews as $review) {
            $user = $userRepository->find($review->getFkUserId());

            $review->setUser($user);

            $reviewsWithUser[] = $review;
        }

        return $reviewsWithUser;
    }

    #[Route('/review/insert', name: 'review_post', methods: ['POST'])]
    public function reviewPost(Request $request, ManagerRegistry $doctrine): Response
    {
        $review = new Review();

        $review->setFkUserId($this->requestStack->getSession()->get('user_id'))
            ->setData($request->get('review'))
            ->setCreatedTimestamp(date('Y-m-d H:i:s'));

        $entityManager = $doctrine->getManager();

        $entityManager->persist($review);

        $entityManager->flush();

        return $this->redirectToRoute('reviews');
    }
}
