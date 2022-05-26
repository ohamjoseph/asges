<?php

namespace App\Controller;

use App\Entity\Association;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/acceuil')]
class AcceuilController extends AbstractController
{
    #[Route('/{page?1}', name: 'app_acceuil')]
    public function index(ManagerRegistry $doctrine, $page): Response
    {
        $associationRepo = $doctrine->getRepository(Association::class);

        try {
            $user = $this->getUser();

        }catch (\Exception $e){
            //
        }
        $nbr = 6;
        $repository = $doctrine->getRepository(Association::class);
        $associations = $repository->findBy([], [], $nbr, ($page - 1) * $nbr);

        $nbPersonne = $repository->count([]);
        $nbPage = ceil($nbPersonne / $nbr);

        return $this->render('acceuil/index.html.twig', [
            'associations' => $associations,
            'nbPage' => $nbPage,
            'page' => $page,
            'isPaginated' => true,
        ]);
    }

    #[Route('/detail/{id}', name: 'acceuil.ass.detail')]
    public function detail(ManagerRegistry $doctrine, Association $association)
    {

        if (!$association) {
            $this->addFlash('error', "Cette associtation n'exite pas");
            return $this->redirectToRoute("app_acceuil");
        }


        return $this->render('acceuil/detail.html.twig', [
            'association' => $association,
        ]);
    }
}
