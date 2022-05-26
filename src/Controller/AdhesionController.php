<?php

namespace App\Controller;

use App\Entity\Adhesion;
use App\Entity\Association;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/adhesion')]
class AdhesionController extends AbstractController
{
    #[Route('/', name: 'app_adhesion')]
    public function index(): Response
    {
        return $this->render('adhesion/index.html.twig', [
            'controller_name' => 'AdhesionController',
        ]);
    }

    #[Route('/add/{id}', name: 'app_adhesion.add')]
    public function addAdhesion(
        ManagerRegistry $doctrine,
        Association $association,
        Request $request
    ): Response
    {
        if(!$association){
            $this->addFlash('error', "Cette associtation n'exite pas");
            return $this->redirectToRoute("app_acceuil");
        }
        $adhesionRepo = $doctrine->getRepository(Adhesion::class);
        $associationRepo = $doctrine->getRepository(Association::class);
        $user = $this->getUser();

        try {
            $adhesion = new Adhesion();
            $adhesion->setAssociation($association);
            $adhesion->setUser($user);
            $adhesionRepo->add($adhesion,true);
            $this->addFlash('success',"Votre adhésion à été effectuer avec success");
        }catch (\Exception $e){
            $this->addFlash('success',"Votre adhésion n'a pas été");
        }

        return $this->redirectToRoute('app_acceuil');
    }
}
