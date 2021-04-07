<?php

namespace App\Controller;

use App\Entity\Calendrier;
use App\Form\CalendrierType;
use DateTime;
use App\Repository\CalendrierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

    /**
     * 
     * @Route("/calendrier")
     * 
     */
class CalendrierController extends AbstractController
{
    #[Route('/', name: 'calendrier_index', methods: ['GET'])]
    public function index(CalendrierRepository $calendrierRepository): Response
    {
        // je récupère les évènements en base
        $events = $calendrierRepository->findAll();

       // parser les données dans un tableau en json avant de les envoyer à la vue
       $rdvs = [];

       foreach( $events as $event){
            $rdvs[] = [                 // fait un array_push()
                'id' => $event->getId(),
                'start' => $event->getStart()->format('Y-m-d H:i:s' ),
                'end' => $event->getEnd()->format('Y-m-d H:i:s' ),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'backgroundColor' => $event->getBackgroundColor(),
                'borderColor' => $event->getBorderColor(),
                'textColor' => $event->getTextColor(),
                'allDay' => $event->getAllDay(),

            ];
        }

        // j'encode les données
        $data = json_encode($rdvs);

        return $this->render('calendrier/index.html.twig', [
            'data' => $data,
        ]);
    }

    #[Route('/new', name: 'calendrier_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $calendrier = new Calendrier();
        $form = $this->createForm(CalendrierType::class, $calendrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($calendrier);
            $entityManager->flush();

            return $this->redirectToRoute('calendrier_index');
        }

        return $this->render('calendrier/new.html.twig', [
            'calendrier' => $calendrier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'calendrier_show', methods: ['GET'])]
    public function show(Calendrier $calendrier): Response
    {
        return $this->render('calendrier/show.html.twig', [
            'calendrier' => $calendrier,
        ]);
    }

    #[Route('/{id}/edit', name: 'calendrier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Calendrier $calendrier): Response
    {
        $form = $this->createForm(CalendrierType::class, $calendrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('calendrier_index');
        }

        return $this->render('calendrier/edit.html.twig', [
            'calendrier' => $calendrier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'calendrier_delete', methods: ['POST'])]
    public function delete(Request $request, Calendrier $calendrier): Response
    {
        if ($this->isCsrfTokenValid('delete'.$calendrier->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($calendrier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('calendrier_index');
    }

    /**
     * 
     * @Route("/api/{id}/edit", name="calendrier_api_edit", methods={"PUT", "GET"})
     * 
     *  ?Calendrier permet de passer un ID qui peut ne pas exister encore
     *  le méthode PUT peut créer une instance si elle n'existe pas
     * 
     * @return Response
     */
    public function majEvent(?Calendrier $calendrier, Request $request){

        // on récupère les données fournies par fullcalendar
        $donnees = json_decode($request->getContent());

        // on vérifie que l'on a toutes les données
        if (
            isset($donnees->title) && !empty($donnees->title) &&
            isset($donnees->start) && !empty($donnees->start) &&
            isset($donnees->end) && !empty($donnees->end) &&
            isset($donnees->description) && !empty($donnees->description) &&
            isset($donnees->backgroundColor) && !empty($donnees->backgroundColor) &&
            isset($donnees->borderColor) && !empty($donnees->borderColor) &&
            isset($donnees->textColor) && !empty($donnees->textColor)
        ){
            // les données sont complètes, on initialise un code pour dire "j'ai mis à jour"
            $code = 200;

            // vérifie si l'Id existe
            if(!$calendrier){
                // on instancie un rendez-vous
                $calendrier = new Calendrier;
                // on change le code en 201 pour created
                $code = 201;

            }
            // on hydrate l'objet Calendrier avec les données
            $calendrier->setTitle($donnees->title);
            $calendrier->setDescription($donnees->description);
            $calendrier->setStart(new Datetime($donnees->start));
            // test si RDV sur journée entière ou pas
            if($donnees->allDay){
                $calendrier->setEnd(new Datetime($donnees->start));
            } else {
                $calendrier->setEnd(new Datetime($donnees->end));
            }
            
            $calendrier->setBackgroundColor($donnees->backgroundColor);
            $calendrier->setBorderColor($donnees->borderColor);
            $calendrier->settextColor($donnees->textColor);

            // entity manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($calendrier);
            $em->flush();

            // on returne un code
            return new Response('OK', $code);

        } else {
            // les données ne sont pas complètes
            return new Response('Données incomplètes', 404);
        }

       
    }
}
