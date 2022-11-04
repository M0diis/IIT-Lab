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
            'admin' => $this->requestStack->getSession()->get('admin'),
            'reviews' => $this->getReviewsByUser($doctrine)
        ]);
    }

    private function getReviewsByUser($doctrine): array
    {
        $reviewRepository = $doctrine->getRepository(Review::class);

        $userRepository = $doctrine->getRepository(User::class);

        $reviews = $reviewRepository->findBy([], [
            'created_timestamp' => 'DESC'
        ]);

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

        $message = $request->get('review');

        if(empty($message)) {
            $this->addFlash('error', "The review should not be empty.");

            return $this->redirectToRoute('reviews');
        }

        $review
            ->setFkUserId($this->requestStack->getSession()->get('user_id'))
            ->setData($message)
            ->setCreatedTimestamp(date('Y-m-d H:i:s'));

        $entityManager = $doctrine->getManager();

        $entityManager->persist($review);

        $entityManager->flush();

        return $this->redirectToRoute('reviews');
    }

    #[Route('/review/delete/{id}', name: 'review_delete', methods: ['POST'])]
    public function reviewDelete($id, ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('admin')) {
            return $this->redirectToRoute('reviews');
        }

        $reviewRepository = $doctrine->getRepository(Review::class);

        $review = $reviewRepository->find($id);

        $entityManager = $doctrine->getManager();

        $entityManager->remove($review);

        $entityManager->flush();

        return $this->redirectToRoute('reviews');
    }
}
