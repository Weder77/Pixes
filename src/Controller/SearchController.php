<?php

namespace App\Controller;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/jeux/recherche/", name="search_game")
     */
    public function searchGame(Request $request, GameRepository $gameRepository)
    {
        $search = $request->request->get('search');

        if ($search != null) {
            $games = $gameRepository->findByNameLike($search);
        } else {
            return $this->redirectToRoute('allgames');
        }

        return $this->render('search/game.html.twig', [
            'search' => $search,
            'games' => $games,
        ]);
    }
}
