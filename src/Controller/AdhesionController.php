<?php

namespace App\Controller;

use App\Entity\Adhesion;
use App\Entity\Association;
use App\Entity\Mail;
use App\Entity\User;
use App\Form\MailType;
use App\Service\MailerService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

use PhpOffice\PhpSpreadsheet\IOFactory;

#[Route('/adhesion')]
class AdhesionController extends AbstractController
{

    private $status = array(
        'active'=> 'ACTIVE',
        'suspendus'=> 'SUSPENDUS',
        'bannis'=> 'BANNIS',
        'creer'=> 'CREER'
    );

    public function __construct(
        private UserPasswordHasherInterface $hasher
    )
    {
    }


    #[Route('/association/{id}', name: 'app_adhesion')]
    public function index(Association $association, Request $request): Response
    {
        $adhesions = $association->getAdhesions();
        try {
            $notFlush = $request->request->get('notFlush');
        }catch (\Exception $e){
            $notFlush = null;
        }


        return $this->render('adhesion/index.html.twig', [
            'adhesions' => $adhesions,
            'association'=>$association,
            'notFlush' => $notFlush
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


        return $this->redirectToRoute('app_adhesion.detail',[
            'id'=>$adhesion->getId(),
        ]);

    }

    #[Route('/response/{id}/{status}', name: 'app_adhesion.response')]
    public function acceptAdhesion(
        ManagerRegistry $doctrine,
        Adhesion $adhesion,
        $status,
        MailerService $mailerService
    ): RedirectResponse
    {

        $adhesion->setStatus($this->status[$status]);
        $doctrine->getRepository(Adhesion::class)->add($adhesion,true);
        $subject = "Votre status dans l'association ".$adhesion->getAssociation();
        $message = "Chèr.e ".$adhesion->getUser();
        if ($status == 'active'){
            $this->addFlash('succes',$adhesion->getUser()." a été accepter dans l'association ");
            $message = $message. ", Votre demande d'adhèsion à l'association ".$adhesion->getAssociation()." a été accepter";

        }else{
            $this->addFlash('info',$adhesion->getUser()." a été ".$status." de l'association ");
            $message = $message. ", vous avez été $status de l'association ".$adhesion->getAssociation();
        }

        $mailerService->sendEmail(to:$adhesion->getUser()->getEmail(),subject: $subject,content: $message);


        return $this->redirectToRoute('app_adhesion.detail',[
            'id'=>$adhesion->getId(),
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

    #[Route('/email/{id}/{pub}',
        name: 'app_adhesion.mail',
        defaults: [
            'pub'=>false,
        ]
    )]
    public function envoiMail(
        ManagerRegistry $doctrine,
        Adhesion $adhesion,
        Request $request,
        $pub,
        MailerService $mailerService
    ): Response
    {
        $mail = new Mail();
        $form = $this->createForm(MailType::class,$mail);

        $mailRepo = $doctrine->getRepository(Mail::class);

        $form->handleRequest($request);


        if($form->isSubmitted()){

            $mail = $form->getData();
            if($pub){
                // Recupperations de touts les elements envoyer dans le  resquest
                $checkbox = array_keys($request->request->all());

                // Recuperations des options
                $options = [];
                foreach ($checkbox as $option){
                    if (str_contains($option,'option')) {
                        array_push($options,$option);
                    }
                }
                $repoAdh = $doctrine->getRepository(Adhesion::class);

                //Recupérations des Adhesions

                foreach ($options as $id){
                    $adhesion = $repoAdh->find($request->request->get($id));
                    $mail->setUser($adhesion->getUser());
                    $mail->setAssociation($adhesion->getAssociation());
                    $mailRepo->add($mail);
                    $mailerService->sendEmail(
                        to:$adhesion->getUser()->getEmail(),
                        subject: $mail->getSubject(),
                        content: $adhesion->getUser().',<br><br>'.$mail->getMessage()
                    );
                }
                $this->addFlash('succes','Votre publipostage  a été envoyer avec succes');
            }else{
                $mail->setUser($adhesion->getUser());
                $mail->setAssociation($adhesion->getAssociation());
                $mailRepo->add($mail);
                $mailerService->sendEmail(
                    to:$adhesion->getUser()->getEmail(),
                    subject: $mail->getSubject(),
                    content: $adhesion->getUser().',<br><br>'.$mail->getMessage()
                );
                $this->addFlash('succes','Le mail a envoyer avec succes');
            }





            return $this->redirectToRoute('app_adhesion',[
                'id'=>$adhesion->getAssociation()->getId(),
            ]);
        }


        return $this->render('adhesion/email.html.twig',[
            'form'=>$form->createView(),
            'adhesion'=>$adhesion,
            'adhesions'=>$adhesion->getAssociation()->getAdhesions(),
            'pub' => $pub,
        ]);
    }



    #[Route('import/{id}',name: 'xlsx')]
    public function importAdhesion(Request $request,
                                   Association $association,
                                   ManagerRegistry $doctrine,
                                   MailerService $mailerService
    ): Response
    {

        $file = $request->files->get('xlsx'); // get the file from the sent request
        $fileFolder = __DIR__ . '/../../public/uploads/';  //choose the folder in which the uploaded file will be stored

        $filePathName = md5(uniqid()) . $file->getClientOriginalName();
        // apply md5 function to generate an unique identifier for the file and concat it with the file extension
        try {
            $file->move($fileFolder, $filePathName);
        } catch (FileException $e) {
            dd($e);
        }
        $spreadsheet = IOFactory::load($fileFolder . $filePathName); // Here we are able to read from the excel file
        $row = $spreadsheet->getActiveSheet()->removeRow(1); // I added this to be able to remove the first file line
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); // here, the read data is turned into an array

        $userRepo = $doctrine->getManager();
        $adhesionRepo = $doctrine->getManager();


        foreach ($sheetData as $data){

            try {
                $user = new User();

                $username = $data['A'];
                $mail = $data['B'];
                $adresse = $data['C'];

                $mp = uniqid();
                $user->setUsername($username)
                    ->setEmail($mail)
                    ->setAdresse($adresse)
                    ->setPassword($this->hasher->hashPassword($user, $mp ))
                ;

                $userRepo->persist($user);
                $userRepo->flush();

                $adhesion = new Adhesion();
                $adhesion->setAssociation($association);
                $adhesion->setUser($user)->setStatus($this->status['active']);
                $adhesionRepo->persist($adhesion);
                $adhesionRepo->flush();

                $subject = "Adhesion a l'association $association";
                $this->addFlash('succes','Ok');

                //Envoi de mail
                $message = "$username, <br><br>Vous avez été integré dans l'association $association<br><br>
            Vos identifiants:<br><br>
            usernane : $username<br><br>
            mot de passe: $mp";

                $mailerService->sendEmail(to: $mail,subject: $subject,content: $message);
            }
            catch (\Exception $e) {
                $this->addFlash('info',$user.' existe deja dans la base de données ');
            }

        }

        return $this->redirectToRoute('app_adhesion',[
            'id'=>$association->getId(),

        ]);
    }

}
