<?php
namespace App\Controller;

use App\Entity\Collection;
use App\Entity\Item;
use App\Form\CollectionType;
use Cloudinary\Cloudinary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Cloudinary\Configuration\Configuration;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\User;
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

use App\Form\ItemType;



class CollectionController extends AbstractController
{
   

    #[Route('/collections/create', name: 'collection_create')]
    public function create(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
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
        
        $caBundlePath = 'C:/Users/zinev/проекты/kursash/config/cloudinary.pem';
    
        $client = new Client([
            'verify' => $caBundlePath,
        ]);
    
     
        $config = Configuration::instance([
            'cloud' => [
                'cloud_name' => 'dlfg1jedo',
                'api_key' => '173122989836467',
                'api_secret' => '0owW3NdgFidW5rk7rBjOWnPWzyY',
                'secure' => true,
            ],
            'url' => [
                'secure' => true,
            ],
            'api' => [
                'client' => $client, 
            ]
        ]);
    
       
        $cloudinary = new Cloudinary($config);
    
      
        $collection = new Collection();
        $form = $this->createForm(CollectionType::class, $collection);
    
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
         
            $existingCollection = $em->getRepository(Collection::class)->findOneBy(['name' => $collection->getName(), 'author' => $user]);
            if ($existingCollection) {
              
                $form->get('name')->addError(new FormError('You already have a collection with this name.'));
            } else {
              
                $collection->setAuthor($user);
    
                /** @var UploadedFile $imageFile */
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    try {
                        $uploadResult = $cloudinary->uploadApi()->upload($imageFile->getPathname(), [
                            'folder' => 'collections_images',
                        ]);
                        $collection->setImageUrl($uploadResult['secure_url']);
                    } catch (\Exception $e) {
                     
                        return new Response($e->getMessage(), 500);
                    }
                }
    
                $em->persist($collection);
                $em->flush();
    
                return $this->redirectToRoute('collection_index');
            }
        }
    
       
        return $this->render('collection/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
     #[Route('/collections', name: 'collection_index')]
public function index(EntityManagerInterface $em): Response
{
   
    $collections = $em->getRepository(Collection::class)->findAll();

    return $this->render('collection/index.html.twig', [
        'collections' => $collections,
    ]);
}



#[Route('/collections/{id}/show', name: 'collection_show')]
public function show(Collection $collection, Request $request): Response
{
    
    $item = new Item();
    $itemForm = $this->createForm(ItemType::class, $item);

    $itemForm->handleRequest($request);
    if ($itemForm->isSubmitted() && $itemForm->isValid()) {
    
        $item->setCollection($collection);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($item);
        $entityManager->flush();

       
        return $this->redirectToRoute('collection_show', ['id' => $collection->getId()]);
    }

    return $this->render('collection/show.html.twig', [
        'collection' => $collection,
        'itemForm' => $itemForm->createView(),
        'items' => $collection->getItems(),
        'customFields' => $collection->getCustomFields(),
    ]);
}


#[Route('/collection/{collectionId}/item/new', name: 'item_new')]
public function new(Request $request, int $collectionId, EntityManagerInterface $entityManager): Response
{
    $collection = $entityManager->getRepository(Collection::class)->find($collectionId);

    if (!$collection) {
        throw $this->createNotFoundException('Collection not found');
    }

    $item = new Item();
    $form = $this->createForm(ItemType::class, $item);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      
        $item->setCollection($collection);
        $entityManager->persist($item);
        $entityManager->flush();

        return $this->redirectToRoute('collection_show', ['id' => $collectionId]);
    }

    return $this->render('item/new.html.twig', [
        'form' => $form->createView(),
        'collection' => $collection,
    ]);
}

#[Route('/collection/{collectionId}/item/edit/{id}', name: 'item_edit_action', methods: ['GET', 'POST'])]
public function editItem(Request $request, int $collectionId, int $id, EntityManagerInterface $em, SessionInterface $session, AuthenticationUtils $authenticationUtils): Response
{
   
    $userId = $session->get('userId');

   
    if (!$userId) {
        return $this->redirectToRoute('app_login');
    }

    

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $collection = $em->getRepository(Collection::class)->find($collectionId);

        if (!$collection) {
            throw $this->createNotFoundException('Collection not found');
        }

  
        $item = $em->getRepository(Item::class)->find($id);

      
        if (!$item) {
            throw $this->createNotFoundException('Item not found');
        }

      
        if ($item->getUser()->getId() !== $userId) {
            throw new AccessDeniedException('Access denied');
        }

    
        $form = $this->createFormBuilder($item)
            ->add('name', TextType::class)
            ->add('tags', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Save changes'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $item->setUpdatedAt(new \DateTime());
            $em->flush();
   
            return $this->redirectToRoute('collection_show', ['id' => $collectionId]);
        }

        return $this->render('item/edit.html.twig', [
            'form' => $form->createView(),
            'collection' => $collection,
            'items' => $collection->getItems(),
        ]);
   
}

#[Route('/collections/delete/{id}', name: 'collection_delete')]
public function delete(Collection $collection, EntityManagerInterface $em, SessionInterface $session): Response
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
    $userId = $userData['id'];

   
    $em->remove($collection);
    $em->flush();

   
    return $this->redirectToRoute('collection_index');
}

#[Route('/collections/{id}/edit', name: 'collection_edit')]
public function edit(Request $request, Collection $collection, EntityManagerInterface $em, SessionInterface $session): Response
{
    
    $userData = $session->get('user');

    if (!empty($userData) && isset($userData['id'])) {
        $user = $em->getRepository(User::class)->find($userData['id']);

        if (!$user) {
            return new Response('User not found', 404);
        }

        if ($collection->getAuthor()->getId() !== $user->getId()) {
            return new Response('Unauthorized', 403);
        }
    } else {
        return $this->redirectToRoute('app_login');
    }

    $caBundlePath = 'C:/Users/zinev/проекты/kursash/config/cloudinary.pem';

    // Создание клиента Guzzle с указанием файла сертификатов
    $client = new Client([
        'verify' => $caBundlePath,
    ]);

    $config = Configuration::instance([
        'cloud' => [
            'cloud_name' => 'dlfg1jedo',
            'api_key' => '173122989836467',
            'api_secret' => '0owW3NdgFidW5rk7rBjOWnPWzyY',
            'secure' => true,
        ],
        'url' => [
            'secure' => true,
        ],
        'api' => [
            'client' => $client, 
        ]
    ]);

    $cloudinary = new Cloudinary($config);

    $form = $this->createForm(CollectionType::class, $collection);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
       
        $existingCollection = $em->getRepository(Collection::class)->findOneBy([
            'name' => $collection->getName(),
            'author' => $user,
        ]);

        if ($existingCollection && $existingCollection->getId() !== $collection->getId()) {
            $form->get('name')->addError(new FormError('You already have a collection with this name.'));
        } else {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                try {
                    $uploadResult = $cloudinary->uploadApi()->upload($imageFile->getPathname(), [
                        'folder' => 'collections_images',
                    ]);
                    $collection->setImageUrl($uploadResult['secure_url']);
                } catch (\Exception $e) {
                    return new Response($e->getMessage(), 500);
                }
            }

            $em->flush();

            return $this->redirectToRoute('collection_index');
        }
    }

  
    return $this->render('collection/edit.html.twig', [
        'form' => $form->createView(),
        'collection' => $collection,
    ]);
}

}
