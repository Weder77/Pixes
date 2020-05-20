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
    public function redirect()
    {
        return $this->redirectToRoute('index');
    }
}
