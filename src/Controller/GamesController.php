<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Platform;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GamesController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        $doc = $this->getDoctrine();
        $platforms = $doc->getRepository(Platform::class)->findAll();
        $tags = $doc->getRepository(Tag::class)->findAll();
        $games = $doc->getRepository(Game::class)->findBy([], null, 8);

        return $this->render('/index.html.twig', array(
            'platforms' => $platforms,
            'tags' => $tags,
            'games' => $games,
        ));
    }
    
    /**
     * @Route("/jeu/{slug}", name="game")
     */
    public function game($slug)
    {
        $rep = $this->getDoctrine()->getRepository(Game::class);
        $game = $rep->findOneBy(['slug' => $slug]);

        // Si jeu n'existe pas => redirection
        if ($game == null) {
            // $this->addFlash('error', 'Oups, il semblerait que le jeu que vous avez demandé n\'est pas disponible sur notre plateforme :/');
            // return $this->redirectToRoute('home');

            $game = $rep->findOneBy(['slug' => 'game-1']);
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
}
