<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Annonces;
use App\Form\AnnonceContactType;
use App\Form\AnnoncesType;
use App\Repository\AnnoncesRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @Route("/annonces", name="annonces_")
 */
class AnnoncesController extends AbstractController
{
    
    /**
     * @Route("/", name="liste")
     * 
     * @param AnnoncesRepository $annoncesRepository
     * @param Request $request
     */
    public function index(AnnoncesRepository $annoncesRepository, Request $request)
    {
 
        // je récupère la page transmise dans la requête, en integer et par défaut = 1
        $page = (int)$request->query->get('page', 1);

        // nombre d'annonces à aficher par pages 
        $limit = 4;

        // je récupère les annonces actives
        $annonces = $annoncesRepository->getPaginatedAnnonces($page, $limit);

        // je récupère le nombre total d'annonces
        $total = $annoncesRepository->getTotalAnnonces();


        return $this->render('annonces/index.html.twig', [
            'annonces' => $annonces,
            'total' => $total,
            'limit' => $limit,
            'page' => $page
        ]);

        // on peut écrire compact('annonces','total', 'limit', 'page')
        // le nom à transmettre = nom de la variable
    }
    
    
    
    /**
     * Permet de retrouver une annonce par son slug
     * @Route("/details/{slug}", name="details")
     *
     * @param AnnoncesRepository $annoncesRepository
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     */
    public function details($slug, AnnoncesRepository $annoncesRepository, Request $request, MailerInterface $mailer): Response
    {
        // Recherche une annonce définie par son slug
        $annonce = $annoncesRepository->findOneBy(['slug' => $slug]);

        // Vérifie si l'annonce existe
        if(!$annonce){
            throw new NotFoundHttpException('Pas d\'annonce correspondante');
        }

          // on généère le formulaire (pas de données à passer)
          $form = $this->createForm(AnnonceContactType::class);

          // on traite le formulaire
          $contact = $form->handleRequest($request);

          if($form->isSubmitted() && $form->isValid()){
              // on crée l'email avec template
              $email = (new TemplatedEmail())
                    ->from($contact->get('email')->getData())     // on va chercher dans le formulaire $contact l'email de l'envoi
                    ->to($annonce->getUsers()->getEmail())        // Dans annonce on a le user et dans  User on son email 
                    ->subject('Contact au sujet de votre annonce "' . $annonce->getTitle() . '" ' )
                    ->htmlTemplate('emails/contact_annonce.html.twig')     // fichier twig du template
                    ->context([                                           // toutes les données dont on a besoin dans le template twig
                        'annonce' => $annonce,
                        'mail' => $contact->get('email')->getData(),
                        'message' => $contact->get('message')->getData()
                    ])
                ;

                // envoi du message qui a été créé
                $mailer->send($email);
                
                // on confirme et on redirige
                $this->addFlash('message', 'Votre mail a bien été envoyé');

                // on renvoie sur la route sur la quelle nous sommes (pas oublier le slug en paramètre)
                return $this-> redirectToRoute('annonces_details', ['slug'=> $annonce->getSlug()] );

          }

        return $this->render('annonces/details.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView()   // envoi du formulaire à la vue
        ]);
    }

    /* Les méthodes Ajout et modifier sont dans UsersController */

    #[Route('/{id}', name: 'annonces_delete', methods: ['POST'])]
    public function delete(Request $request, Annonces $annonce): Response
    {
        if ($this->isCsrfTokenValid('delete'.$annonce->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($annonce);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_home');
    }


     /**
     * Permet de sélectionner un favoris
     * @Route("/favoris/ajout/{id}", name="ajout_favoris")
     *
     * @param AnnoncesRepository $annoncesRepository
     * @return Response
     */
    public function ajoutFavoris(Annonces $annonce): Response
    {
        
        // Vérifie si l'annonce existe
        if(!$annonce){
            throw new NotFoundHttpException('Pas d\'annonce correspondante');
        }

        // on ajoute le favori avec injection du User connecté
        $annonce->addFavori($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->persist($annonce);
        $em->flush();

        return $this->redirectToRoute('app_home');
    }

    /**
     * Permet de retirer un favoris
     * @Route("/favoris/retrait/{id}", name="retrait_favoris")
     *
     * @param AnnoncesRepository $annoncesRepository
     * @return Response
     */
    public function retraitFavoris(Annonces $annonce): Response
    {
        
        // Vérifie si l'annonce existe
        if(!$annonce){
            throw new NotFoundHttpException('Pas d\'annonce correspondante');
        }

        // on ajoute le favori avec injection du User connecté
        $annonce->removeFavori($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->persist($annonce);
        $em->flush();

        return $this->redirectToRoute('app_home');
    }
}
