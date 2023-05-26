<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/utilisateurs')]
class UserController extends CommonController
{
    #[Route('/', name: 'user_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER_LIST')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['nom' => 'ASC']);
        $users = array_filter($users, fn($user) => !$user->hasRole('ROLE_SUPER_ADMIN'));

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER_EDIT')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $user = new User();
        $user->plainPassword = bin2hex(random_bytes(4));
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush(); // pour avoir l'id
            $this->log('create', $user);
            $em->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER_LIST')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER_EDIT')]
    public function edit(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->touch(); // Pour déclencher l'event PreUpdate
            $this->log('update', $user);
            $em->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER_EDIT')]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $this->log('delete', $user);
        $em->flush();

        return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
    }
}
