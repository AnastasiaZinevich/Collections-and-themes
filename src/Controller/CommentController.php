<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Item;
use App\Entity\User;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CommentController extends AbstractController
{
    

    /**
     * @Route("/comment/{itemId}", name="comment_page", methods={"GET", "POST"})
     */
    public function commentPage(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, $itemId): Response
    {
       
        $item = $entityManager->getRepository(Item::class)->find($itemId);
        $userData = $session->get('user');
    
        if (!empty($userData) && isset($userData['id'])) {
           
            $user = $entityManager->getRepository(User::class)->find($userData['id']);
    
           
            if (!$user) {
              
                return new Response('User not found', 404);
            }
        } else {
          
            return $this->redirectToRoute('app_login');
        }
    
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
    
       
        if ($form->isSubmitted() && $form->isValid()) {
           
            $comment->setUser($user);
            $comment->setItem($item);
            $comment->setCreatedAt(new \DateTime());
            $entityManager->persist($comment);
            $entityManager->flush();
    
            return $this->redirectToRoute('comment_page', ['itemId' => $itemId]);
        }
        $comments = $item->getComments();

       
        return $this->render('comment/comment_page.html.twig', [
            'item' => $item,
            'comments' => $comments,
            'form' => $form->createView(),
        ]);
    }
    

    /**
     * @Route("/comments/{itemId}", name="fetch_comments", methods={"GET"})
     */
    public function fetchComments(EntityManagerInterface $entityManager, $itemId): Response
    {
        $item = $entityManager->getRepository(Item::class)->find($itemId);
        $comments = $item->getComments();
    
        $serializedComments = [];
        foreach ($comments as $comment) {
            $serializedComment = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                'user' => [
                    'email' => $comment->getUser()->getEmail(), 
                  
                ],
            ];
            $serializedComments[] = $serializedComment;
        }
    
        return $this->json($serializedComments, 200);
    }
    
}
