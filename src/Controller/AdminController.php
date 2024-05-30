<?php
// src/Controller/AdminController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Cache\CacheInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminController extends AbstractController
{
    public function dashboard(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        // Check if the user is authenticated
        $userData = $session->get('user');
    
        // Check if user data exists and if it contains the user ID
        if (!empty($userData) && isset($userData['id'])) {
            // Retrieve the user entity from the database based on the user ID
            $user = $em->getRepository(User::class)->find($userData['id']);
    
            // Check if the user entity is found
            if (!$user) {
                // Handle case where user entity is not found
                return $this->redirectToRoute('index');
            }
    
            // Check if the user has the ROLE_ADMIN role
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $users = $em->getRepository(User::class)->findAll();
                return $this->render('admin/manage_users.html.twig', [
                    'users' => $users,
                ]);
            }
        }
    
        // Redirect to a different route if the user is not authenticated or does not have the ROLE_ADMIN role
        return $this->redirectToRoute('index');
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function manageUsers(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $userData = $session->get('user');
    
        // Check if user data exists and if it contains the user ID
        if (!empty($userData) && isset($userData['id'])) {
            // Retrieve the user entity from the database based on the user ID
            $user = $em->getRepository(User::class)->find($userData['id']);
    
            // Check if the user entity is found
            if (!$user) {
                // Handle case where user entity is not found
                return $this->redirectToRoute('index');
            }
    
            // Check if the user has the ROLE_ADMIN role
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $users = $em->getRepository(User::class)->findAll();
                return $this->render('admin/manage_users.html.twig', [
                    'users' => $users,
                ]);
            }
        }
      
        return $this->redirectToRoute('index');
      
    }

    #[Route('/admin/user/{id}/edit', name: 'admin_edit_user')]
    public function editUser(Request $request, User $user, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $userData = $session->get('user');
    
        // Check if user data exists and if it contains the user ID
        if (!empty($userData) && isset($userData['id'])) {
            // Retrieve the user entity from the database based on the user ID
            $user = $entityManager->getRepository(User::class)->find($userData['id']);
    
            // Check if the user entity is found
            if (!$user) {
                // Handle case where user entity is not found
                return $this->redirectToRoute('index');
            }
    
            // Check if the user has the ROLE_ADMIN role
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                // Create a form to edit the user
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
            }
        }
      
        return $this->redirectToRoute('home/index.html.twig');
        // Ensure the user has admin role
       
       
    }

    #[Route('/admin/user/{id}/delete', name: 'admin_delete_user')]
    public function deleteUser(User $user, EntityManagerInterface $entityManager, SessionInterface $session, CacheInterface $cache): Response
    {
        // Удаление пользователя из базы данных
        $entityManager->remove($user);
        $entityManager->flush();
    
        // Удаление данных о пользователе из сессии, если они существуют
        $userId = $session->get('user')['id'] ?? null;
        if ($userId === $user->getId()) {
            $session->remove('user');
        }
    
        // Очистка кэша, если используется кэширование
        $cache->clear();
    
        // Перенаправление на страницу пользователей администратора
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/user/{id}/toggle-admin', name: 'admin_toggle_admin', methods: ['POST'])]
    #[Security('is_granted("ROLE_ADMIN")')]
    public function toggleAdmin(User $user, EntityManagerInterface $entityManager): Response
    {
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles)) {
            $user->setRoles(array_diff($roles, ['ROLE_ADMIN']));
        } else {
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
        }
    
        $entityManager->flush();
    
        return $this->redirectToRoute('admin_users');
    }
    
    #[Route('/admin/user/{id}/toggle-block', name: 'admin_toggle_block', methods: ['POST'])]
    #[Security('is_granted("ROLE_ADMIN")')]
    public function toggleBlock(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsBlocked(!$user->getIsBlocked());
        $entityManager->flush();
    
        return $this->redirectToRoute('admin_users');
    }
    
}
