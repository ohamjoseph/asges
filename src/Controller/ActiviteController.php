<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Adhesion;
use App\Entity\Association;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use App\Service\uploaderService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/activite')]
class ActiviteController extends AbstractController
{

    private $status = array(
        'active' => 'ACTIVE',
        'suspendus' => 'SUSPENDUS',
        'annuler' => 'ANNULER',
        'terminer' => 'TERMINER',
    );

    #[Route('add/{id}', name: 'app_activite')]
    public function index(
        Request $request,
        Association $association,
        ManagerRegistry $managerRegistry,
        uploaderService $uploader,
    ): Response {

        $registry = $managerRegistry->getManager();

        // creation d'une  activité
        $activite = new Activite();

        // creation d'un formulaire associé a l'entité activité
        $form = $this->createForm(ActiviteType::class, $activite);
        $activite->setAssociation($association);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $flyers = $form->get('brochure')->getData();

            if ($flyers) {
                $directory = $this->getParameter('activite_directory');
                $activite->setImage($uploader->uploadFile($flyers, $directory));
            }


            //Persistence des données
            $registry->persist($activite);

            //Excuté de la requet sql associée
            $registry->flush();

            $this->addFlash('succes', "L'activité $activite a été ajoutée avec success");

            return $this->redirectToRoute('app_adhesion', [
                'id' => $association->getId(),
            ]);
        }

        return $this->render('activite/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //    public function listActivite(
    //        ManagerRegistry $doctrine
    //    ):Response{
    //        $repo = $doctrine->getRepository(Activite::class);
    //        activi
    //    }
    #[Route('/detail/{id}', name: 'app_activite.detail')]
    public function detailActivite(Activite $activite, ManagerRegistry $doctrine): Response
    {
        $userAdhesion = $doctrine->getRepository(Adhesion::class)
            ->userAdhesion(
                $activite->getAssociation(),
                $this->getUser()
            );
        return $this->render('activite/detail.html.twig', [
            'activite' => $activite,
            'userAdhesion' => $userAdhesion
        ]);
    }


    #[Route('/status/{id}/{status}', name: 'app_activite.status')]
    public function statusActivite(Activite $activite, $status, ManagerRegistry $doctrine)
    {
        $activite->setStatus($this->status[$status]);
        $entity = $doctrine->getManager();
        $entity->persist($activite);
        $entity->flush();

        $this->addFlash('succes', "L'activité a bien été mise a jour");
        return $this->redirectToRoute('app_activite.detail', [
            'id' => $activite->getId(),
        ]);
    }
}
