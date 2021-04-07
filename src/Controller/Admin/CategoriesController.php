<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Repository\CategoriesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Controleur pour les catégories
 * 
 * @Route("/admin/categories", name="admin_categories_")
 * @package App\Controller\Admin
 */
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categoriesRepository->findAll(),
        ]);
    }

    #[Route('/ajout', name: 'ajout')]
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
            // redirection sur la page d'accueil des catégories
            return $this->redirectToRoute('admin_categories_home');


        }

        return $this->render('admin/categories/ajout.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/modifier/{id}', name: 'modifier')]
    public function modifierCategorie(Request $request, Categories $categories): Response
    {
        // on crée l'objet formulaire
        $form = $this->createForm(CategoriesType::class, $categories);
        // on gère les données passées dans Request
        $form->handleRequest($request);
        // on teste le formaulaire
        if( $form->isSubmitted() && $form->isValid()) {
            // traitement des données
            $em = $this->getDoctrine()->getManager();
            // conserver les données
            $em->persist($categories);
            // envoi en base
            $em->flush();

            // message de validation
            $this->addFlash('message', 'Nouvelle catégorie créée.');
            // redirection sur la page d'accueil des catégories
            return $this->redirectToRoute('admin_categories_home');


        }

        return $this->render('admin/categories/ajout.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
