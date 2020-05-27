<?php

namespace App\Controller;

use App\Entity\Buy;
use App\Entity\Code;
use App\Entity\Game;
use App\Repository\GameRepository;
use App\Service\Cart\CartService;
use DateTime;
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

    /**
     * @Route("/paiement", name="cart_checkout")
     */
    public function checkout(CartService $cartService)
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $this->getUser()->getProfile();
        $balance = $user->getBalance();
        $price = $cartService->getTotal();
        $cart = $cartService->getFullCart();
        $codes = [];

        // L'utilisateur a t'il assez d'argent sur son compte ?
        if ($balance >= $price) {
            // On parcourt l'ensemble des jeux de notre panier
            foreach ($cart as $item) {
                // On récupère les codes valides pour le jeu
                $allCodes = $this->getDoctrine()->getRepository(Code::class)->getAvailableCodes($item['product']->getId());
                for ($i = 0; $i < $item['quantity']; $i++) {
                    // Pour chaque édition du jeu ajouté au panier on regarde si on a un code disponible
                    if ($allCodes[$i] == null) {
                        $this->addFlash('error', 'Malheureusement, un jeu que vous souhaitez n\'est plus en stock chez nous.');
                        return $this->redirectToRoute('cart_index');
                    }
                    array_push($codes, $allCodes[$i]);
                }
            }

            $buy = new Buy();
            $buy->setPrice($price);
            $buy->setPurchaseDate(new DateTime());
            $buy->setProfile($user);
            foreach ($codes as $code) {
                $buy->addCode($code);
                $code->setUsed(1);
                $manager->persist($code);
            }
            $buy->setUrlInvoice('none');
            $manager->persist($buy);

            $user->setBalance($balance - $price);
            $cartService->clear();
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Merci pour votre achat ! Retrouvez dès à présent votre code sur votre espace "Mon compte".');
            return $this->redirectToRoute('index');
        } else {
            $this->addFlash('error', 'Malheureusement, votre solde n\'est pas assez élevé pour procéder au paiement.');
            return $this->redirectToRoute('cart_index');
        }
    }
}
