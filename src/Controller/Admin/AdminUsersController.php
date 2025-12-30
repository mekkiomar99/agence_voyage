<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserRoleFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users', name: 'admin_users')]
#[IsGranted('ROLE_ADMIN')]
class AdminUsersController extends AbstractController
{
    #[Route('', name: '')]
    public function list(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/users_list.html.twig', ['users' => $users]);
    }

    #[Route('/{id}/edit', name: '_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        // Prevent editing your own roles
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier vos propres rôles.');
            return $this->redirectToRoute('admin_users');
        }

        // Store original roles from database (reflection to access private property)
        $reflection = new \ReflectionClass($user);
        $rolesProperty = $reflection->getProperty('roles');
        $rolesProperty->setAccessible(true);
        $originalRoles = $rolesProperty->getValue($user);
        
        // Ensure ROLE_USER is in the array for the form (it will be shown as checked)
        if (!in_array('ROLE_USER', $originalRoles)) {
            $originalRoles[] = 'ROLE_USER';
        }
        $user->setRoles($originalRoles);

        $form = $this->createForm(UserRoleFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the selected roles from the form
            $selectedRoles = $user->getRoles();
            
            // Remove ROLE_USER from the array since it's auto-added by getRoles()
            // We only store additional roles in the database
            $selectedRoles = array_filter($selectedRoles, fn($role) => $role !== 'ROLE_USER');
            
            // Set the roles (only store non-ROLE_USER roles, ROLE_USER is auto-added)
            $user->setRoles(array_values($selectedRoles));
            
            $em->flush();
            $this->addFlash('success', 'Rôles de l\'utilisateur modifiés avec succès.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: '_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em): Response
    {
        // CSRF token check
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_user' . $user->getId(), $token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_users');
        }

        // Prevent deleting yourself
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('admin_users');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('admin_users');
    }
}
