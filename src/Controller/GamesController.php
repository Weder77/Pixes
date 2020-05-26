<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Opinion;
use App\Entity\Platform;
use App\Entity\Tag;
use App\Form\OpinionType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GamesController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $doc = $this->getDoctrine();
        $platforms = $doc->getRepository(Platform::class)->findAll();
        $tags = $doc->getRepository(Tag::class)->findAll();
        $games = $doc->getRepository(Game::class)->findBy([], ['id' => 'DESC'], 8);

        return $this->render('/index.html.twig', array(
            'platforms' => $platforms,
            'tags' => $tags,
            'games' => $games,
        ));
    }
    
    /**
     * @Route("/jeu/{slug}", name="game")
     */
    public function game($slug, Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $rep = $this->getDoctrine()->getRepository(Game::class);
        $game = $rep->findOneBy(['slug' => $slug]);

        // Si jeu n'existe pas => redirection
        if ($game == null) {
            $this->addFlash('error', 'Oups, il semblerait que le jeu que vous avez demandé n\'est pas disponible sur notre plateforme :/');
            return $this->redirectToRoute('home');
        }

        $platforms = [];
        $tags = [];
        $opinions = [];
        foreach ($game->getPlatforms() as $key => $platform) {
            array_push($platforms, $platform->getName());
        }
        foreach ($game->getTags() as $key => $tag) {
            array_push($tags, $tag->getName());
        }

        foreach ($game->getOpinions() as $key => $opinion) {
            array_push($opinions, $opinion);
        }

        $opinion = new Opinion;
        $form = $this->createForm(OpinionType::class, $opinion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($opinion);

            if ($opinion->getNote() > 5) {
                $opinion->setNote(5);
            } elseif ($opinion->getNote() < 0.5) {
                $opinion->setNote(0.5);
            }

            $opinion->setPostedOn(new DateTime());
            $opinion->setUser($this->getUser()->getProfile());
            $opinion->setGame($game);
            $manager->flush();

            $this->addFlash('success', 'Votre commentaire a bien été posté !');
        }

        return $this->render('games/game.html.twig', [
            'name' => $game->getName(),
            'description' => $game->getDescription(),
            'slug' => $slug,
            'tags' => $tags,
            'platforms' => $platforms,
            'pegi' => $game->getPegi(),
            'price' => $game->getPrice(),
            'opinions' => $opinions,
            'opinionForm' => $form->createView(),
        ]);
    }
}
