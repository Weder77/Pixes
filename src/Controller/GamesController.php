<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Opinion;
use App\Entity\Platform;
use App\Entity\Tag;
use App\Form\OpinionType;
use App\Repository\CodeRepository;
use App\Repository\GameRepository;
use App\Repository\PlatformRepository;
use App\Repository\TagRepository;
use App\Service\Game\GameService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GamesController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(PlatformRepository $platformRepository, TagRepository $tagRepository, GameRepository $gameRepository)
    {
        $platforms = $platformRepository->findAll();
        $tags = $tagRepository->findAll();
        $games = $gameRepository->findBy([], ['id' => 'DESC'], 8);

        return $this->render('/index.html.twig', array(
            'platforms' => $platforms,
            'tags' => $tags,
            'games' => $games,
        ));
    }

    /**
     * @Route("/jeu/tous-les-jeux", name="allgames")
     */
    public function allgames(PlatformRepository $platformRepository, TagRepository $tagRepository, GameRepository $gameRepository)
    {
        $platforms = $platformRepository->findAll();
        $tags = $tagRepository->findAll();
        $games = $gameRepository->findBy([], ['id' => 'DESC']);

        return $this->render('/games/allgames.html.twig', array(
            'platforms' => $platforms,
            'tags' => $tags,
            'games' => $games,
        ));
    }

    /**
     * @Route("/jeu/{searchplateform}/{searchtag}", name="game_filter")
     */
    public function gameFilter($searchplateform, $searchtag, PlatformRepository $platformRepository, TagRepository $tagRepository, GameRepository $gameRepository)
    {
        $plateform = $platformRepository->findOneBy(['plateform' => $searchplateform]);
        $tag = $tagRepository->findOneBy(['tag' => $searchtag]);

        $games = $gameRepository->findBy([], ['plateform' => $plateform], ['tag' => $tag] );

        return $this->render('/games/gamefilter.html.twig', array(
            'games' => $games,
            'tag' => $tag,
            'plateform' => $plateform,
        ));
    }

    /**
     * @Route("/jeu/{slug}", name="game")
     */
    public function game($slug, Request $request, GameRepository $gameRepository, CodeRepository $codeRepository, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = $gameRepository->findOneBy(['slug' => $slug]);

        // Récupération du nombre de codes en stock
        $availablesCodes = $codeRepository->getAvailableCodes($game->getId());

        // Récupération des jeux que l'utilisateur possède
        $ownedGames = [];
        if ($this->getUser() != null) {
            foreach ($this->getUser()->getProfile()->getInvoices() as $invoice) {
                foreach ($invoice->getCodes() as $code) {
                    array_push($ownedGames, $code->getGame()->getName());
                }
            }
        }

        // Si jeu n'existe pas => redirection
        if ($game == null) {
            $this->addFlash('error', 'Oups, il semblerait que le jeu que vous avez demandé n\'est pas disponible sur Pixes.');
            return $this->redirectToRoute('index');
        }

        $opinion = new Opinion;
        $form = $this->createForm(OpinionType::class, $opinion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($opinion);
            // Si note en dehors de l'échelle
            if ($opinion->getNote() > 5) {
                $opinion->setNote(5);
            } elseif ($opinion->getNote() < 1) {
                $opinion->setNote(1);
            }

            $opinion->setPostedOn(new DateTime());
            $opinion->setUser($this->getUser()->getProfile());
            $opinion->setGame($game);
            $manager->flush();

            // Redirection sur la route pour afficher le commentaire
            $this->addFlash('success', 'Votre commentaire a bien été posté !');
            return $this->redirectToRoute('game', [
                'slug' => $slug
            ]);
        }

        return $this->render('games/game.html.twig', [
            'game' => $game,
            'stock' => sizeof($availablesCodes),
            'averageNote' => $gameService->getAverageNote($game->getOpinions()),
            'ownedGames' => $ownedGames,
            'opinionForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/jeu/avis/modifier/{slug}/{id}", name="opinion_update")
     */
    public function opinionUpdate($slug, $id, Request $request, GameRepository $gameRepository, CodeRepository $codeRepository, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = $gameRepository->findOneBy(['slug' => $slug]);

        // Récupération du nombre de codes en stock
        $availablesCodes = $codeRepository->getAvailableCodes($game->getId());

        // Récupération des jeux que l'utilisateur possède
        $ownedGames = [];
        if ($this->getUser() != null) {
            foreach ($this->getUser()->getProfile()->getInvoices() as $invoice) {
                foreach ($invoice->getCodes() as $code) {
                    array_push($ownedGames, $code->getGame()->getName());
                }
            }
        }

        // Si jeu n'existe pas => redirection
        if ($game == null) {
            $this->addFlash('error', 'Oups, il semblerait que le jeu que vous avez demandé n\'est pas disponible sur Pixes.');
            return $this->redirectToRoute('index');
        }

        $opinion = $manager->find(Opinion::class, $id);

        $form = $this->createForm(OpinionType::class, $opinion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($opinion);
            // Si note en dehors de l'échelle
            if ($opinion->getNote() > 5) {
                $opinion->setNote(5);
            } elseif ($opinion->getNote() < 1) {
                $opinion->setNote(1);
            }

            $opinion->setPostedOn(new DateTime());
            $opinion->setUser($this->getUser()->getProfile());
            $opinion->setGame($game);
            $manager->flush();

            // Redirection sur la route pour afficher le commentaire
            $this->addFlash('success', 'Votre commentaire a bien été modifié !');
            return $this->redirectToRoute('game', [
                'slug' => $slug
            ]);
        }

        return $this->render('games/updateopinion.html.twig', [
            'game' => $game,
            'stock' => sizeof($availablesCodes),
            'averageNote' => $gameService->getAverageNote($game->getOpinions()),
            'ownedGames' => $ownedGames,
            'opinionForm' => $form->createView(),
        ]);
    }
}
