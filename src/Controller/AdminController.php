<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\AddGameType;
use App\Service\Game\GameService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_index")
     */
    public function index()
    {
        return $this->render('/admin/index.html.twig');
    }

    /**
     * @Route("/admin/jeux", name="admin_games")
     */
    public function games()
    {
        $repository = $this->getDoctrine()->getRepository('App\Entity\Game');
        $games = $repository->findAll();

        return $this->render('/admin/games.html.twig', array(
            'games' => $games
        ));
    }

    /**
     * @Route("/admin/jeux/supprimer{id}", name="admin_delete")
     */
    public function delete($id)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = $manager->find('App\Entity\Game', $id);
        $manager->remove($game);
        $manager->flush();

        $this->addFlash('success', 'Le jeux avec l\'id numéro ' . $id . ' a bien été supprimé.');

        return $this->redirectToRoute('admin_games');

    }

     /**
     * @Route("/admin/jeux/ajouter", name="admin_add")
     */
    public function add(Request $request, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = new Game; 
        
        $form = $this->createForm(AddGameType::class, $game);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() )
        {
            $manager->persist($game);
            $game->uploadFile();
            $game->setSlug($gameService->generateSlug($game->getName()));
            $manager->flush();
            $this->addFlash('success', 'Le jeu '. $game->getName() . ' a bien été ajouté.');

        }

        return $this->render('admin/addgame.html.twig', array(
            'gameForm' => $form->createView()
        ));
    }

     /**
     * @Route("/admin/jeu/update/{id}", name="admin_update")
     */
    public function updateGame($id, Request $request, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = $manager->find(Game::class, $id);
        $form = $this->createForm(AddGameType::class,$game);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($game);
            if($game->getFile()){
                $game->removeFile();
                $game-> uploadFile();
            }
            $game->setSlug($gameService->generateSlug($game->getName()));
            $manager->flush();

            $this->addFlash('success', 'Le jeu '. $game->getName().' a bien été mis à jour.');
            return $this->redirectToRoute('admin_games');
        }

        return $this->render('admin/updategame.html.twig', [
            'gameForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/ventes", name="admin_orders")
     */
    public function orders()
    {
        return $this->render('/admin/orders.html.twig');
    }

    /**
     * @Route("/admin/utilisateurs", name="admin_users")
     */
    public function users()
    {
        return $this->render('/admin/users.html.twig');
    }
}
