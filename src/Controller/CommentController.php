<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Commentaire;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/add/{id}', name: 'app_comment')]
    public function index(Request $request, Activite $activite, ManagerRegistry $doctrine): Response
    {
        $commentaire = new Commentaire();
        $text = $request->request->get('comment');
        $commentaire->setUser($this->getUser())
            ->setActivite($activite)
            ->setCommentaire($text)
        ;

        $registry = $doctrine->getManager();
        $registry->persist($commentaire);

        $registry->flush();

        $this->addFlash('succes','Votre commentaire a été bien ajouter');

        return $this->redirectToRoute("app_activite.detail",[
            'id'=>$activite->getId(),
        ]);
    }
}
