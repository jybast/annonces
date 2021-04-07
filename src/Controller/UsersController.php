<?php

namespace App\Controller;

use App\Entity\Annonces;
use App\Entity\Images;
use App\Form\AnnoncesType;
use App\Form\EditProfileType;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersController extends AbstractController
{
    #[Route('/users', name: 'users')]
    public function index(): Response
    {
        return $this->render('users/index.html.twig');
    }

    #[Route('/users/annonces/ajout', name: 'users_annonces_ajout')]
    public function ajoutAnnonces(Request $request): Response
    {
        // on crée une instance
        $annonce = new Annonces;
        // on génére un formulaire
        $form= $this->createForm(AnnoncesType::class, $annonce);
        // on traite les données de Request
        $form->handleRequest($request);
        // on traite le formulaire
        if($form->isSubmitted() && $form->isValid()){
            // on récupère l'utilisateur connecté
            $annonce->setUsers($this->getUser());
            // on passe le flag active à false -- en attente de modération
            $annonce->setActive(false);

            // On récupère les images transmises dans le formulaire
            $images = $form->get('images')->getData();

            // on boucle sur les images
            foreach( $images as $image){
                // on génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                // on copie le fichier dans /uploads depuis le dossier temporaire
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                // on crée l'instance d'image
                $img = new Images;
                $img->setName($fichier);
                // on ajoute les images dans l'annonce
                $annonce->addImage($img);
            }


            // on traite les données
            $em = $this->getDoctrine()->getManager();
            // en fin le persist va enregistrer les images en cascade
            $em->persist($annonce);
            $em->flush();

            // message de confirmation
            $this->addFlash('message', 'votre annonce est en attente de modération');
            // redirection vers la page users
            return $this->redirectToRoute('users');
        }



        return $this->render('users/annonces/ajout.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/annonces/edit/{id}', name: 'users_annonces_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Annonces $annonce): Response
    {
        $form = $this->createForm(AnnoncesType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // on récupère l'utilisateur connecté
            $annonce->setUsers($this->getUser());
            // on passe le flag active à false -- en attente de modération
            $annonce->setActive(false);

            // On récupère les images transmises dans le formulaire
            $images = $form->get('images')->getData();

            // on boucle sur les images
            foreach( $images as $image){
                // on génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                // on copie le fichier dans /uploads depuis le dossier temporaire
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                // on crée l'instance d'image
                $img = new Images;
                $img->setName($fichier);
                // on ajoute les images dans l'annonce
                $annonce->addImage($img);
            }
                
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('annonces_index');
        }

        return $this->render('annonces/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet d'afficher les données personnelles de l'utilisateur
     * 
     * @Route("/users/data", name="users_data")
     */
    public function usersData(){

        return $this->render('users/data.html.twig');
    }

     /**
     * Permet d'afficher les données personnelles de l'utilisateur
     * 
     * @Route("/users/data/download", name="users_data_download")
     */
    public function usersDataDownload()
    {
        // on définit les options du PDF -- instance de l'objet Option de Dompdf
        $pdfOptions = new Options();
        // police par défaut
        $pdfOptions->set('defaultFont', 'Arial');
        // permettre le téléchargement
        $pdfOptions->setIsRemoteEnabled(true);

        // on généère une instance de Dompdf avec ses options
        $dompdf = new Dompdf($pdfOptions);
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed' => TRUE
            ]
        ]);
        $dompdf->setHttpContext($context);

        // on génère le Html
        $html = $this->renderView('users/download.html.twig');

        // on transmet le html à Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // on génère un nom de fichier
        $fichier = 'user-data-'. $this->getUser()->getId(). '.pdf';

        // envoyer le pdf au navigateur
        $dompdf->stream($fichier, [
            'Attachment' => true
        ]);

        return new Response();
    }

    /**
     * @Route("/supprimer/image/{id}", name="users_supprimer_image", methods={"DELETE"})
     * 
     */
    public function deleteImage(Images $image, Request $request){
        $data = json_decode($request->getContent(), true);

        // on vérifie si le token est valide
        if( $this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])){
            // on récuprère le nom de l'image
            $nom = $image->getName();
            // on supprime le fichier de son répertoire
            unlink($this->getParameter('images_directory').'/'.$nom);
            // on supprime l'enregistrement dans la base
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            // on retourne la réponse en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token invalide'], 400);
        }


    }

    #[Route('/users/profil/modifier', name: 'users_profil_modifier')]
    public function editProfile(Request $request): Response
    {
        // récupère l'utilisateur connecté
        $user = $this->getUser();
        // on génére un formulaire on lui passant l'utilisateur connecté
        $form= $this->createForm(EditProfileType::class, $user);
        // on traite les données de Request
        $form->handleRequest($request);
        // on traite le formulaire
        if($form->isSubmitted() && $form->isValid()){
           
            // on traite les données
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // message de confirmation
            $this->addFlash('message', 'votre profil a été mis à jour.');
            // redirection vers la page users
            return $this->redirectToRoute('users');
        }



        return $this->render('users/editProfile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/pass/modifier', name: 'users_pass_modifier')]
    public function editPass(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        // vérifier que method en mode POST
        if($request->isMethod('POST')){
            $em=$this->getDoctrine()->getManager();

            $user= $this->getUser();

            // vérifier concordance des mots de passe
            if($request->request->get('pass') == $request->request->get('pass2')){
                // stocker et encoder le mot de passe
                    $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('pass')));
                    // envoi à la base de données
                    $em->flush();

                    // message de confirmation
                    $this->addFlash('message', 'Votre mot de passe a été mis à jour.');

                    // redirection vers la page users
                    return $this->redirectToRoute('users');

            } else {
                $this->addFlash('error','Les deux mots de passe doivent être identiques.');
            }
        }

        return $this->render('users/editPass.html.twig');
    }
}
