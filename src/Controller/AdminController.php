<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Adhesion;
use App\Entity\Association;
use App\Service\MailerService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{

    private $status = [
        'accepter'=>'ACCEPTER',
        'rejeter' =>'REJETER',
        'suspendus'=>'SUSPENDUS',
        'Supprimer'=>'SUPRIMER',
        'reactiver'=>'REACTIVER'
    ];

    #[Route('/{page?1}', name: 'app_admin')]
    public function index(ManagerRegistry $doctrine, $page): Response
    {
        $repo = $doctrine->getRepository(Association::class);
        $nbr = 8;
        $page = (int)$page;
        $associations = $repo->findBy(['status' => 'CREER'], ['createAt' => 'DESC'], $nbr, ($page - 1) * $nbr);
        $nbPersonne = $repo->count(['status' => 'CREER']);
        $nbPage = ceil($nbPersonne / $nbr);
        return $this->render('admin/index.html.twig', [
            'associations' => $associations,
            'nbPage' => $nbPage,
            'page' => $page,
            'isPaginated' => true,
            'adminNav'=>true,
            'dc'=>true,
            'na'=>true
        ]);
    }

    #[Route('/detail/{id}', name: 'app_admin.detail')]
    public function indexDetail(Association $association): Response
    {
        return $this->render('admin/detail.html.twig', [
            'association' => $association,
            'isPaginated' => true,
            'adminNav'=>true,
            'la'=>true,
            'na'=>false
        ]);
    }

    #[Route('/response/{id}/{status}', name: 'app_admin.response')]
    public function indexResponse(
        Association $association,
        MailerService $mailerService,
        ManagerRegistry $managerRegistry,
        $status
    ): Response
    {
        $subject = "Le status de votre assocition $association";
        $repo = $managerRegistry->getRepository(Adhesion::class);

        //Recupération de l'adhesion du president de l'association
        $presidentAdhesion = $repo->findBy(['association'=>$association,'role'=>'PRESIDENT']);


        //Gestion des status
        $association->setStatus($this->status[$status]);
        $manager =$managerRegistry->getManager();
        $manager->persist($association);
        $manager->flush();

        if ($status =='accepter'){
            $this->addFlash('info','Vous avez valider la creation de cette association');
            $message = "La creation de votre association $association vient d'etre accepté";
        }elseif ($status=='rejeter'){
            $this->addFlash('info','Vous avez rejeter la creation de cette association');
            $message = "La creation de votre association $association vient d'etre rejeter";
        }else{
            $this->addFlash('info',"Vous avez $status cette association");
            $message = "Votre association $association vient d'etre $status";
        }

        $mailerService->sendEmail(to:$presidentAdhesion[0]->getUser()->getEmail() ,subject:$subject ,content:$message );

        return $this->redirectToRoute('app_admin.detail',[
            'id'=>$association->getId(),
        ]);
    }

    #[Route('/{page?1}/list', name: 'app_admin.list')]
    public function listAssociation(
        ManagerRegistry $managerRegistry,
        $page
    ):Response{
        $critere =new Criteria();
        $critere->where(Criteria::expr()->neq('status','CREER'));
        $repo = $managerRegistry->getRepository(Association::class);
        $nbr = 8;
        $associations = $repo->matching($critere);
        $nbAss = $repo->matching($critere)->count();
        $nbPage = ceil($nbAss / $nbr);
        return $this->render('admin/list_association.html.twig', [
            'associations' => $associations,
            'nbPage' => $nbPage,
            'page' => $page,
            'isPaginated' => true,
            'adminNav'=>true,
            'la'=>true,
            'na'=>false
        ]);
    }

}
