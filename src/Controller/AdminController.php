<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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
        $repository = $this -> getDoctrine() -> getRepository('App\Entity\Game');
        $games = $repository -> findAll();

        return $this->render('/admin/games.html.twig', array(
            'games' => $games
        ));
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
