<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Form\SearchAnnonceType;
use App\Repository\AnnoncesRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     *
     * @param AnnoncesRepository $annoncesRepository
     * @param Request $request
     * @return Response
     */
    public function index(AnnoncesRepository $annoncesRepository, Request $request): Response
    {
        // je récupère toutes les annonces actives
        $annonces = $annoncesRepository->findBy(
            ['active' => true],
            ['createdAt' => 'desc'],
            8 );
        // je crée le formulaire
        $form = $this->createForm(SearchAnnonceType::class);
        // je stocke les données du formulaire dansla variable $search
        $search = $form->handleRequest($request);
        // je vérifie les données
        if($form->isSubmitted() && $form->isValid()){
            // on recherche les annonces correspondant à la chaîne recherchée avec la méthode search()
            $annonces = $annoncesRepository->search(
                $search->get('mot')->getData(),
                $search->get('categorie')->getData()
            );
        }

        return $this->render('main/index.html.twig', [
            'annonces' => $annonces,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/mentions-legales", name="app_mentions")
     *
     * @return Response
     */
    public function mentions(): Response
    {
        return $this->render('main/mentions.html.twig');
    }

     /**
     * @Route("/contact", name="app_contact")
     * 
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     */
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        // on génère le formulaire
        $form = $this->createForm(ContactType::class);

        // On traite le formulaire
        $contact = $form->handleRequest($request);

        // On traite les données
        if($form->isSubmitted() && $form->isValid()){
             // on crée l'email avec template
             $email = (new TemplatedEmail())
                ->from($contact->get('email')->getData())     // on va chercher dans le formulaire $contact l'email de l'envoi
                ->to('adresse-du-site@domaine.fr ')        // c'est l'adresse du site à contacter
                ->subject('Contact depuis le site - Mon site ' )
                ->htmlTemplate('emails/contact.html.twig')     // fichier twig du template
                ->context([                                           // toutes les données dont on a besoin dans le template twig
                    'mail' => $contact->get('email')->getData(),
                    'sujet' => $contact->get('sujet')->getData(),
                    'message' => $contact->get('message')->getData()
                ])
            ;

         // envoi du message qui a été créé
         $mailer->send($email);
         
         // on confirme et on redirige
         $this->addFlash('message', 'Votre mail de contact a bien été envoyé');

         // on renvoie sur la route sur la quelle nous sommes 
         return $this-> redirectToRoute('app_home');

        }

        return $this->render('main/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
