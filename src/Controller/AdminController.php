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
     * @Route("/admin/games", name="admin_games")
     */
    public function games()
    {
        return $this->render('/admin/games.html.twig');
    }

    /**
     * @Route("/admin/orders", name="admin_orders")
     */
    public function orders()
    {
        return $this->render('/admin/orders.html.twig');
    }

    /**
     * @Route("/admin/users", name="admin_users")
     */
    public function users()
    {
        return $this->render('/admin/users.html.twig');
    }
}
