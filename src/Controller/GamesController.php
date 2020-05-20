<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GamesController extends AbstractController
{
    /**
     * @Route("/games/{slug}", name="games")
     */
    public function index($slug)
    {
        return $this->render('games/buy-game.html.twig', [
            'slug' => $slug
        ]);
    }
}
