<?php

namespace App\Controller;

use App\Entity\Adhesion;
use App\Entity\Association;
use App\Form\AssociationType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/association')]
class AssociationController extends AbstractController
{
    #[Route('/', name: 'app_association')]
    public function index(): Response
    {
        return $this->render('association/index.html.twig', [
            'controller_name' => 'AssociationController',
        ]);
    }

    #[Route('/add', name: 'app_association.add')]
    public function addAssociation(
        ManagerRegistry $doctrine,
        Request         $request,
    ): Response
    {
        $entityRegister = $doctrine->getManager();

        $association = new Association();
        $association->setNbrAdherant(1);
        $form = $this->createForm(AssociationType::class, $association);

        //
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $association = $form->getData();
            $adhesion = new Adhesion();
            $adhesion
                ->setUser($this->getUser())
                ->setAssociation($association)
                ->setRole("PRESIDENT")
                ->setStatus('ACTIVE')

            ;

            // ... perform some action, such as saving the task to the database

            $entityRegister->persist($association);
            $adhesionRepo = $doctrine->getRepository(Adhesion::class);
            $adhesionRepo->add($adhesion);



            //excute
            $entityRegister->flush();

//            $mailMessage = $personne->getFirstname() . ' ' . $personne->getLastname() . ' a été ajouté';
//            $mailerService->sendEmail(content: $mailMessage);
            return $this->redirectToRoute('app_acceuil');
        }

        return $this->render('association/new.html.twig', [
            'association' => $association,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/list', name: 'app_association.list' )]
    public function userAssociationList(ManagerRegistry $doctrine): Response{

        $userAdhesions = $doctrine->getRepository(Adhesion::class)->userAdhesions($this->getUser());

        return $this->render('association/user_association_list.html.twig',[
            'associationNav'=>true,
            'userAssociationNav'=>true,
            'open'=>true,
            'userAdhesions'=>$userAdhesions,
            ]);
    }

    #[Route('/list/creer', name: 'app_association.list.creer' )]
    public function userAssociationCreer(ManagerRegistry $doctrine): Response{

        return $this->render('association/user_adhesion_list.html.twig',[
            'associationNav'=>true,
            'userAssociationNavCreer'=>true,
            'open'=>true,
        ]);
    }

    #[Route('/detail/{id}', name: 'app_association.detail')]
    public function detail(ManagerRegistry $doctrine, Association $association)
    {
        $userAdhesion = $doctrine->getRepository(Adhesion::class)->userAdhesion($association,$this->getUser());

        $adhesion = $association->getAdhesions();

        if (!$association) {
            $this->addFlash('error', "Cette associtation n'exite pas");
            return $this->redirectToRoute("app_association.list");
        }


        return $this->render('association/user_association_detail.html.twig', [
            'association' => $association,
            'adhesions' => $adhesion,
            'userAdhesion'=>$userAdhesion,

        ]);
    }

}
