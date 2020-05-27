<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Service\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/panier", name="cart_index")
     */
    public function cart(CartService $cartService)
    {
        return $this->render('cart/cart.html.twig', [
            'cart' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
        ]);
    }

    /**
     * @Route("/panier/ajouter/{id}", name="cart_add")
     */
    public function cartAdd($id, CartService $cartService)
    {
        $cartService->add($id);

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/panier/retirer/{id}", name="cart_remove_quantity")
     */
    public function cartRemoveQuantity($id, CartService $cartService)
    {
        $cartService->removeQuantity($id);

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/panier/supprimer/{id}", name="cart_remove")
     */
    public function cartRemove($id, CartService $cartService)
    {
        $cartService->remove($id);

        return $this->redirectToRoute('cart_index');
    }
}
