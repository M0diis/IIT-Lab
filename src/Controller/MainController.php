<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/', name: 'main')]
    public function mainToHome(): Response
    {
        return $this->redirectToRoute('home');
    }

    #[Route('/login', name: 'login', methods: ['GET', 'HEAD'])]
    public function login(): Response
    {
        if ($this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('home');
        }

        return $this->render('main/login.html.twig');
    }

    #[Route('/login', name: 'login_post', methods: ['POST'])]
    public function loginPost(Request $request, ManagerRegistry $doctrine): Response
    {
        if ($this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('home');
        }

        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $userRepository = $doctrine->getRepository(User::class);

        $user = $userRepository->findOneBy(
            ['name' => $username, 'password' => $password]
        );

        if (is_null($user)) {
            $this->addFlash("error", "User not registered.");

            return $this->redirectToRoute('login');
        }

        $this->requestStack->getSession()->set('logged_in', true);
        $this->requestStack->getSession()->set('user_id', $user->getId());
        $this->requestStack->getSession()->set('user_object', $user);
        $this->requestStack->getSession()->set('admin', $user->isAdmin());

        $this->addFlash("info", "Logged in successfully.");

        return $this->redirectToRoute('home');
    }

    #[Route('/register', name: 'register', methods: ['GET', 'HEAD'])]
    public function register(): Response
    {
        return $this->render('main/registration.html.twig');
    }

    #[Route('/register', name: 'register_post', methods: ['POST'])]
    public function registerPost(Request $request, ManagerRegistry $doctrine): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $email = $request->request->get('email');

        $userRepository = $doctrine->getRepository(User::class);

        $user = $userRepository->findOneBy(
            ['name' => $username]
        );

        if (!is_null($user)) {
            $this->addFlash("error", "User by the specified name already exists.");

            return $this->redirectToRoute('register');
        }

        $user = $userRepository->findOneBy(
            ['email' => $email]
        );

        if (!is_null($user)) {
            $this->addFlash("error", "User by the specified email already exists.");

            return $this->redirectToRoute('register');
        }

        $entityManager = $doctrine->getManager();

        $user = new User();

        $user->setName($username);
        $user->setPassword($password);
        $user->setEmail($email);
        $user->setAdmin(false);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash("info", "User registered successfully.");

        $this->requestStack->getSession()->set('logged_in', true);
        $this->requestStack->getSession()->set('user_id', $user->getId());
        $this->requestStack->getSession()->set('user_object', $user);
        $this->requestStack->getSession()->set('admin', $user->isAdmin());

        return $this->redirectToRoute('home');
    }


    #[Route('/logout', name: 'logout')]
    public function logout(): Response
    {
        $this->requestStack->getSession()->set('logged_in', false);
        $this->requestStack->getSession()->remove('user_id');
        $this->requestStack->getSession()->remove('user_object');
        $this->requestStack->getSession()->remove('admin');

        $this->addFlash("info", "Logged out successfully.");

        return $this->redirectToRoute('home');
    }


    #[Route('/test', name: 'test')]
    public function test(): Response
    {
        return $this->render('react.html.twig');
    }
}
