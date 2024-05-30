<?php

namespace App\Controller;

use App\Entity\Collection;
use App\Entity\Item;
use App\Entity\Tag;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;


use App\Form\CollectionType;
use Cloudinary\Cloudinary;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Cloudinary\Configuration\Configuration;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use GuzzleHttp\Client;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class ItemController extends AbstractController
{
    #[Route('/collections/{collection}/items', name: 'items_index')]
    public function index(Collection $collection, ItemRepository $itemRepository): Response
    {
        $this->denyAccessUnlessGranted('view', $collection);

        $items = $itemRepository->findBy(['collection' => $collection]);

        return $this->render('item/index.html.twig', [
            'collection' => $collection,
            'items' => $items,
        ]);
    }

    #[Route('/collections/{collection}/items/new', name: 'items_new')]
    public function new(Request $request, Collection $collection, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('edit', $collection);

        $item = new Item();
        $item->setCollection($collection);
        $form = $this->createForm(ItemType::class, $item, ['custom_fields' => $collection->getCustomFields()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($item);
            $em->flush();
            return $this->redirectToRoute('items_index', ['collection' => $collection->getId()]);
        }

        return $this->render('item/new.html.twig', [
            'form' => $form->createView(),
            'collection' => $collection,
        ]);
    }


    #[Route('/collection/{collectionId}/item/edit/{id}', name: 'items_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $collectionId, int $id, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
       
        $collection = $entityManager->getRepository(Collection::class)->find($collectionId);

        if (!$collection) {
            throw $this->createNotFoundException('Collection not found');
        }

        $item = $entityManager->getRepository(Item::class)->find($id);

        if (!$item) {
            throw $this->createNotFoundException('Item not found');
        }

       
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
         
            $item->setUpdatedAt(new \DateTime());

            $entityManager->flush();

            return $this->redirectToRoute('collection_show', ['id' => $collectionId]);
        }
        return $this->render('item/edit.html.twig', [
            'form' => $form->createView(),
            'collection' => $collection,
            'item' => $item,
        ]);
    }
    

   #[Route('/items/{id}/delete', name: 'items_delete', methods: ['POST'])]
public function delete(Request $request, Item $item, EntityManagerInterface $em, SessionInterface $session): Response
{
    $userData = $session->get('user');

    if (!empty($userData) && isset($userData['id'])) {
     
        $user = $em->getRepository(User::class)->find($userData['id']);
        if (!$user) {
            return $this->redirectToRoute('index');
        }
    } else {
       
        return $this->redirectToRoute('app_login');
    }

  
    $collection = $item->getCollection();

   
    if ($collection->getAuthor()->getId() !== $user->getId()) {
        return new Response('Unauthorized', 403);
    }

    if ($this->isCsrfTokenValid('delete' . $item->getId(), $request->request->get('_token'))) {
        $em->remove($item);
        $em->flush();
    }

    return $this->redirectToRoute('collection_show', ['id' => $collection->getId()]);
}
    
}