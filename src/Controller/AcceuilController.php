<?php

namespace App\Controller;

use App\Entity\Association;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class AcceuilController extends AbstractController
{
    #[Route('/{page?1}', name: 'app_acceuil',requirements: ['page'=>'\d+'])]
    public function index(ManagerRegistry $doctrine, $page): Response
    {

        $nbr = 6;
        $critere = new Criteria();
        $critere->orderBy(['createAt'=>'desc']);
        $critere->where(Criteria::expr()->eq('status','ACCEPTER'));
//        $critere->setLimit($nbr);
//        $critere->offset(($page - 1) * $nbr);
        $associationRepo = $doctrine->getRepository(Association::class);

        try {
            $user = $this->getUser();

        }catch (\Exception $e){
            //
        }

        $repository = $doctrine->getRepository(Association::class);
        $associations = $repository->findBy(['status'=>'ACCEPTER'], ['createAt'=>'desc'], $nbr, ($page - 1) * $nbr);
//        $associations = $repository->matching($critere);

        $nbPersonne = $repository->count(['status'=>'ACCEPTER']);
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
