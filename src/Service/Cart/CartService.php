<?php

namespace App\Service\Cart;

use App\Repository\GameRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{

    protected $session;
    protected $gameRepository;

    public function __construct(SessionInterface $session, GameRepository $gameRepository)
    {
        $this->session = $session;
        $this->gameRepository = $gameRepository;
    }

    public function add(int $id)
    {
        $cart = $this->session->get('cart', []);

        if (empty($cart[$id])) {
            $cart[$id] = 0;
        }

        $cart[$id]++;

        $this->session->set('cart', $cart);
    }

    public function removeQuantity(int $id)
    {
        $cart = $this->session->get('cart', []);

        if ($cart[$id] > 1) {
            $cart[$id]--;
        } 

        $this->session->set('cart', $cart);
    }

    public function remove(int $id)
    {
        $cart = $this->session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $this->session->set('cart', $cart);
    }

    public function getFullCart(): array
    {
        $cart = $this->session->get('cart', []);

        $cartData = [];

        foreach ($cart as $id => $quantity) {
            $cartData[] = [
                'product' => $this->gameRepository->find($id),
                'quantity' => $quantity
            ];
        }

        return $cartData;
    }

    public function getTotal(): float
    {
        $cartData = $this->getFullCart();
        $total = 0;

        foreach ($cartData as $couple) {
            $total += $couple['product']->getPrice() * $couple['quantity'];
        }

        return $total;
    }
}