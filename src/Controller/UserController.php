<?php

namespace App\Controller;

use App\Entity\User;
use DateTime;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/user', name: 'user')]
    public function index(): Response
    {
        if(!$this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('login');
        }

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/create', name: 'user_create', methods: ['POST'])]
    public function userCreate(Request $request,  ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('login');
        }

        $name = $request->get('user_name');
        $email = $request->get('user_email');
        $password = $request->get('user_password');
        $admin = $request->get('is_admin');

        if(empty($name) || empty($email) || empty($password)) {
            $this->addFlash('error', 'Please fill in all fields.');

            return $this->redirectToRoute('admin');
        }

        $user = new User();

        $user
            ->setName($name)
            ->setEmail($email)
            ->setPassword($password)
            ->setAdmin($admin)
            ->setCreatedTimestamp(date('Y-m-d H:i:s'));

        $userRepository = $doctrine->getRepository(User::class);

        $existing = $userRepository->findBy(['email' => $email, 'name' => $name]);

        if($existing)
        {
            $this->addFlash('error', 'User already exists by that name and email.');

            return $this->redirectToRoute('admin');
        }

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('info', 'User has been created successfully');

        return $this->redirectToRoute('admin');
    }
}
