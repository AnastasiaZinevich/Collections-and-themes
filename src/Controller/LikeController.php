<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Item;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LikeController extends AbstractController
{
    /**
     * @Route("/like/{itemId}", name="like_action", methods={"POST"})
     */
    public function likeAction(Request $request, EntityManagerInterface $entityManager, $itemId, SessionInterface $session): Response
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

          
            
        if ($user) {
           
            $existingLike = $entityManager->getRepository(Like::class)->findOneBy(['item' => $item, 'user' => $user]);
            if (!$existingLike) {
              
                $like = new Like();
                $like->setUser($user);
                $like->setItem($item);

                $entityManager->beginTransaction();

                try {
                   
                    $entityManager->persist($like);
                    $entityManager->flush();

                    $item->incrementLikeCount();
                    $entityManager->flush();

                    $entityManager->commit();
                } catch (\Exception $e) {
                    $entityManager->rollback();

                    
                    return $this->redirectToRoute('homepage'); 
                }
            }
        }

       
        return $this->redirectBack($request);
    }

   
    private function redirectBack(Request $request): Response
    {
        $referer = $request->headers->get('referer');

        if (!$referer) {
            throw new \RuntimeException('Unable to determine the referring page.');
        }

        return $this->redirect($referer);
    }
}
