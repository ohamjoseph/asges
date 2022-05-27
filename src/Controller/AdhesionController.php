<?php

namespace App\Controller;

use App\Entity\Adhesion;
use App\Entity\Association;
use App\Service\MailerService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/adhesion')]
class AdhesionController extends AbstractController
{
    #[Route('/association/{id}', name: 'app_adhesion')]
    public function index(Association $association): Response
    {
        $adhesions = $association->getAdhesions();



        return $this->render('adhesion/index.html.twig', [
            'adhesions' => $adhesions,
            'association'=>$association,
        ]);
    }

    #[Route('/detail/{id}',name: 'app_adhesion.detail')]
    public function adhesionDetail(Adhesion $adhesion, ManagerRegistry $doctrine):Response
    {
        $userAdhesion = $doctrine->getRepository(Adhesion::class)->userAdhesion($adhesion->getAssociation(),$this->getUser());
        return $this->render('adhesion/association_user_adhesion_detail.html.twig',[
            'adhesion'=>$adhesion,
            'userAdhesion'=>$userAdhesion,
        ]);

    }

    #[Route('/editeur/{id}/{role?3}',name: 'app_adhesion.editeur')]
    public function adhesionEditeur(
        ManagerRegistry $doctrine,
        Adhesion $adhesion,
        MailerService $mailer,
        $role
    ):Response
    {
        $repo = $doctrine->getRepository(Adhesion::class);
        $user = $adhesion->getUser();

        $userAdhesion = $repo->userAdhesion($adhesion->getAssociation(),$this->getUser());
        if($role!=0){
            $adhesion->setRole('EDITEUR');

            $message = $user. ', Bravo vous avez été nommé editeur du le association'.' '.$adhesion->getAssociation();

        }else{
            $adhesion->setRole(null);
            $message = $user. ", vous n'etes plus editeur de l'association".' '.$adhesion->getAssociation();
        }
        $repo->add($adhesion,true);
        $mailer->sendEmail(to: $user->getEmail(),subject: 'Editeur',content: $message);


        return $this->render('adhesion/association_user_adhesion_detail.html.twig',[
            'adhesion'=>$adhesion,
            'userAdhesion'=>$userAdhesion,
        ]);

    }

    #[Route('/add/{id}',
        name: 'app_adhesion.add',
    )]
    public function addAdhesion(
        ManagerRegistry $doctrine,
        Association $association,
        Request $request,
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

            //Mettre a jour le nombre d'adherant
            $association->setNbrAdherant($association->getNbrAdherant()+1);
            $doctrine->getManager()->persist($association);
            $doctrine->getManager()->flush();

            $this->addFlash('succes',"Votre adhésion à l'association $association à été effectuer avec success");
        }catch (\Exception $e){
            $this->addFlash('info',"Vous etes membres de l'association $association");
        }

        return $this->redirectToRoute('app_acceuil');
    }


}
