<?php

namespace App\Controller;

use App\Entity\Code;
use App\Entity\Invoice;
use App\Repository\CodeRepository;
use App\Repository\InvoiceRepository;
use App\Service\Cart\CartService;
use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CartController extends AbstractController
{
    /**
     * @Route("/facture/{id}", name="invoice_generate")
     */
    public function invoice($id, InvoiceRepository $invoiceRepository)
    {
        $invoice = $invoiceRepository->find($id);

        if ($invoice->getProfile()->getUser() != $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            if ($this->getUser() == null) {
                $this->addFlash('error', 'Vous devez vous connecter pour accéder à votre facture.');
                return $this->redirectToRoute('login');
            }
            return $this->redirectToRoute('index');
        }


        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('invoice.html.twig', [
            'invoice' => $invoice,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Pixes_Facture#" . $invoice->getId() . ".pdf", [
            "Attachment" => true
        ]);
    }

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
    public function checkout(CodeRepository $codeRepository ,CartService $cartService, MailerInterface $mailer)
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
                $allCodes = $codeRepository->getAvailableCodes($item['product']->getId());
                for ($i = 0; $i < $item['quantity']; $i++) {
                    // Pour chaque édition du jeu ajouté au panier on regarde si on a un code disponible
                    if (empty($allCodes[$i])) {
                        $this->addFlash('error', 'Malheureusement, un jeu que vous souhaitez n\'est plus en stock chez nous.');
                        return $this->redirectToRoute('cart_index');
                    }
                    array_push($codes, $allCodes[$i]);
                }
            }

            $invoice = new Invoice();
            $invoice->setPrice($price);
            $invoice->setPurchaseDate(new DateTime());
            $invoice->setProfile($user);
            foreach ($codes as $code) {
                $invoice->addCode($code);
                $code->setUsed(1);
                $manager->persist($code);
            }
            $manager->persist($invoice);

            $user->setBalance($balance - $price);
            $cartService->clear();
            $manager->persist($user);
            $manager->flush();

            $urlInvoice = $this->generateUrl('invoice_generate', [
                'id' => $invoice->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $urlAccount = $this->generateUrl('profile', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $template = '
            <div>Pixes vous remercie pour votre achat et vous souhaite bon jeu !</div>
            <div>Retrouvez votre code sur <a href="'. $urlAccount .'">votre compte</a>.</div>
            <a href="'. $urlInvoice .'">Obtenir ma facture</a>
            ';

            $email = (new Email())
                ->to($this->getUser()->getEmail())
                ->from('pixes.facture@gmail.com')
                ->subject('Merci pour votre achat chez Pixes !')
                ->html($template);
            $mailer->send($email);

            $this->addFlash('success', 'Merci pour votre achat ! Retrouvez dès à présent votre code sur votre espace "Mon compte".');
            return $this->redirectToRoute('index');
        } else {
            $this->addFlash('error', 'Malheureusement, votre solde n\'est pas assez élevé pour procéder au paiement.');
            return $this->redirectToRoute('cart_index');
        }
    }
}
