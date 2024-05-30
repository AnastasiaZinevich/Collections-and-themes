<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="home")
     */
  
public function index(Request $request): Response
{
   
    $latestItems = $this->entityManager->getRepository(Item::class)->findBy([], ['id' => 'DESC'], 9);

    $largestCollections = $this->entityManager
    ->getRepository(Collection::class)
    ->createQueryBuilder('c')
    ->leftJoin('c.items', 'i')
    ->groupBy('c.id')
    ->orderBy('COUNT(i)', 'DESC')
    ->setMaxResults(5)
    ->getQuery()
    ->getResult();

    $currentLanguage = $request->getLocale();

    return $this->render('home/index.html.twig', [
        'recentItems' => $latestItems,
        'largestCollections' => $largestCollections,
        'currentLanguage' => $currentLanguage,
    ]);
}
}
