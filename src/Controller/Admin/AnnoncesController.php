<?php

namespace App\Controller\Admin;

use App\Entity\Annonces;
use App\Form\AnnoncesType;
use App\Repository\AnnoncesRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Controleur pour les annonces
 * 
 * @Route("/admin/annonces", name="admin_annonces_")
 * @package App\Controller\Admin
 */
class AnnoncesController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(AnnoncesRepository $annoncesRepository): Response
    {
        return $this->render('admin/annonces/index.html.twig', [
            'annonces' => $annoncesRepository->findAll(),
        ]);
    }

    #[Route('/activer/{id}', name: 'activer')]
    public function activer(Annonces $annonce, AnnoncesRepository $annoncesRepository): Response
    {
        // traitement en AJAX
        // si active on la désactive et si pas active on l'active
        $annonce->setActive($annonce->getActive() ? false : true);

        $em = $this->getDoctrine()->getManager();

        $em->persist($annonce);

        $em->flush();

        return new Response("true");



    }

    #[Route('/supprimer/{id}', name: 'supprimer')]
    public function supprimer(Annonces $annonce, AnnoncesRepository $annoncesRepository): Response
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($annonce);

        $em->flush();

        $this->addFlash('message', 'Votre annonce est supprimée');

        return $this->redirectToRoute('admin_annonces_home');
    }

   
}
