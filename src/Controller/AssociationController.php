<?php

namespace App\Controller;

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
    public function addPersonne(
        ManagerRegistry $doctrine,
        Request         $request,
    ): Response
    {
        $entityRegister = $doctrine->getManager();

        $association = new Association();
        $form = $this->createForm(AssociationType::class, $association);

        //
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $personne = $form->getData();

            // ... perform some action, such as saving the task to the database
            $entityRegister->persist($personne);

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

}

