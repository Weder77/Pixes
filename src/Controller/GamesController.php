<?php

namespace App\Controller;

use App\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GamesController extends AbstractController
{
    /**
     * @Route("/games/{slug}", name="games")
     */
    public function index($slug)
    {
        $rep = $this->getDoctrine()->getRepository(Game::class);
        $game = $rep->findOneBy(['slug' => $slug]);

        // Si jeu n'existe pas => redirection
        if ($game == null) {
            // $this->addFlash('error', 'Oups, il semblerait que le jeu que vous avez demandé n\'est pas disponible sur notre plateforme :/');
            // return $this->redirectToRoute('home');

            $game = $rep->findOneBy(['slug' => 'grand-theft-auto-v']);
        }

        $platforms = [];
        $tags = [];

        foreach ($game->getPlatforms() as $key => $value) {
            array_push($platforms, $value->getName());
        }
        foreach ($game->getTags() as $key => $value) {
            array_push($tags, $value->getName());
        }

        return $this->render('games/game.html.twig', [
            'name' => $game->getName(),
            'description' => $game->getDescription(),
            'slug' => $slug,
            'tags' => $tags,
            'platforms' => $platforms,
            'pegi' => $game->getPegi(),
            'price' => $game->getPrice(),
        ]);
    }

    /**
     * @Route("/index", name="index")
     */
    public function home()
    {
        return $this->render('games/index.html.twig', array());
    }

    /**
     * @Route("/")

     */
    public function redirectToIndex()
    {
        return $this->redirectToRoute('index');
    }
}
