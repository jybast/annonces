<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Repository\AnnoncesRepository;
use App\Repository\CategoriesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Controleur d'administration
 * 
 * @Route("/admin", name="admin_")
 * @package App\Controller\Admin
 */
class AdminController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/categories/ajout', name: 'categories_ajout')]
    public function ajoutCategorie(Request $request): Response
    {
        // Intance de classe
        $categorie = new Categories;
        // on crée l'objet formulaire
        $form = $this->createForm(CategoriesType::class, $categorie);
        // on gère les données passées dans Request
        $form->handleRequest($request);
        // on teste le formaulaire
        if( $form->isSubmitted() && $form->isValid()) {
            // traitement des données
            $em = $this->getDoctrine()->getManager();
            // conserver les données
            $em->persist($categorie);
            // envoi en base
            $em->flush();

            // message de validation
            $this->addFlash('message', 'Nouvelle catégorie créée.');
            // redirection
            return $this->redirectToRoute('admin_home');


        }

        return $this->render('admin/categories/ajout.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard", name="dashboard")
     *
     * @return void
     */
    public function dashbord(CategoriesRepository $categoriesRepository, AnnoncesRepository $annoncesRepository){
        // on cherche toutes  les catégories
        $categories = $categoriesRepository->findAll();

        // je récupère les données utiles pour le graph
        $catNom = [];
        $catCount = [];
        $catColor = [];

        // on démonte les données pour ChartJs
        foreach($categories as $categorie){
            // je remplis les tableaux
            $catNom[] = $categorie->getName(); 
            $catColor[] = $categorie->getBackgroundColor();
            // je compte les annonces ayant cette catégorie
            $catCount[] = count($categorie->getAnnonces());
        }
        
        // on cherche le nombre d'annonces publiées par date
        $annonces = $annoncesRepository->countByDate();

        
        $dates = [];
        $annoncesCount = [];
        
        // $annonces = $annoncesRepository->searchByInterval("2021-03-20", "2021-05-30", 3);
        // dd($annonces);
        foreach($annonces as $annonce){
            $dates[] = $annonce['datesAnnonces'];  // $annonces renvoie des tableaux
            $annoncesCount[] = $annonce['count'];
        }

        // j'envoie les données encodées Json à la vue
        return $this->render('admin/dashboard.html.twig', [
            'catNom' => json_encode($catNom),
            'catColor' => json_encode($catColor),
            'catCount' => json_encode($catCount),
            'dates' => json_encode($dates),
            'annoncesCount' => json_encode($annoncesCount),
        ]);

    }
}
