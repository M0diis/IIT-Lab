<?php

namespace App\Controller;

use App\Entity\User;
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

    #[Route('/user/new', name: 'user_new')]
    public function userNew(Request $request,  ManagerRegistry $doctrine): Response
    {
        if(!$this->requestStack->getSession()->get('logged_in')) {
            return $this->redirectToRoute('login');
        }

        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('name', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('email', EmailType::class, array('attr' => array('class' => 'form-control')))
            ->add('password', PasswordType::class, array('attr' => array('class' => 'form-control')))
            ->add(
                'admin',
                ChoiceType::class,
                array('attr' => array('class' => 'form-control'),
                    'choices' => array('Yes' => true, 'No' => false)))
            ->add(
                'save',
                SubmitType::class,
                array('label' => 'Sukurti', 'attr' => array('class' => 'btn btn-sm btn-primary'))
            )->getForm();

        $form->handleRequest($request);

        $userRepository = $doctrine->getRepository(User::class);

        if($form->isSubmitted() && $form->isValid())
        {
            $name = $form['name']->getData();
            $email = $form['email']->getData();
            $password = $form['password']->getData();
            $admin = $form['admin']->getData();
            $created_timestamp = time();

            $existing = $userRepository->findBy(['email' => $email, 'name' => $name]);

            if($existing)
            {
                $this->addFlash('error', 'Vartotojas su tokiu el. paštu jau egzistuoja.');

                return $this->redirectToRoute('user_new');
            }

            $user->setName($name);
            $user->setEmail($email);
            $user->setPassword($password);
            $user->setAdmin($admin);
            $user->setCreatedTimestamp($created_timestamp);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('info', 'User has been created successfully');

            return $this->redirectToRoute('user_new');
        }

        return $this->render('user/user_new.html.twig', [
            'formItem' => $form->createView()
        ]);
    }
}
