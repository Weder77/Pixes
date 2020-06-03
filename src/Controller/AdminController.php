<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Profile;
use App\Entity\User;
use App\Form\AddGameType;
use App\Form\ProfileFormType;
use App\Repository\InvoiceRepository;
use App\Form\RegisterFormType;
use App\Service\Game\GameService;
use App\Service\Invoice\InvoiceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        $repository = $this->getDoctrine()->getRepository('App\Entity\Game');
        $games = $repository->findAll();

        return $this->render('/admin/games.html.twig', array(
            'games' => $games
        ));
    }

    /**
     * @Route("/admin/jeux/supprimer/{id}", name="admin_delete_game")
     */
    public function deleteGame($id)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = $manager->find('App\Entity\Game', $id);
        $manager->remove($game);
        $manager->flush();

        $this->addFlash('success', 'Le jeux ' . $game->getName() . ' a bien été supprimé.');

        return $this->redirectToRoute('admin_games');
    }

    /**
     * @Route("/admin/jeux/ajouter", name="admin_add")
     */
    public function add(Request $request, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = new Game;

        $form = $this->createForm(AddGameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($game);
            $game->uploadFile();
            $game->setSlug($gameService->generateSlug($game->getName()));
            $manager->flush();
            $this->addFlash('success', 'Le jeu ' . $game->getName() . ' a bien été ajouté.');
        }

        return $this->render('admin/addgame.html.twig', array(
            'gameForm' => $form->createView()
        ));
    }

    /**
     * @Route("/admin/jeu/update/{id}", name="admin_update")
     */
    public function updateGame($id, Request $request, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = $manager->find(Game::class, $id);
        $form = $this->createForm(AddGameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($game);
            if ($game->getFile()) {
                $game->removeFile();
                $game->uploadFile();
            }
            $game->setSlug($gameService->generateSlug($game->getName()));
            $manager->flush();

            $this->addFlash('success', 'Le jeu ' . $game->getName() . ' a bien été mis à jour.');
            return $this->redirectToRoute('admin_games');
        }

        return $this->render('admin/updategame.html.twig', [
            'gameForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/ventes", name="admin_orders")
    */
    public function orders(InvoiceRepository $invoiceRepository, InvoiceService $invoiceService)
    {
        $allInvoices = $invoiceRepository->findAll();
        $lastMonthInvoices = $invoiceService->getLastMonthInvoices($allInvoices);
        $lastWeekInvoices = $invoiceService->getLastWeekInvoices($lastMonthInvoices);
        $todayInvoices = $invoiceService->getTodayInvoices($lastWeekInvoices);

        return $this->render('/admin/orders.html.twig', [
            'todayInvoices' => $todayInvoices,
            'todayProfit' => $invoiceService->getProfit($todayInvoices),
            'lastWeekInvoices' => $lastWeekInvoices,
            'lastWeekProfit' => $invoiceService->getProfit($lastWeekInvoices),
            'lastMonthInvoices' => $lastMonthInvoices,
            'lastMonthProfit' => $invoiceService->getProfit($lastMonthInvoices),
            'allInvoices' => $allInvoices,
            'allProfit' => $invoiceService->getProfit($allInvoices),
        ]);
    }

    /**
     * @Route("/admin/utilisateurs", name="admin_users")
     */
    public function users()
    {
        $repository = $this->getDoctrine()->getRepository('App\Entity\Profile');
        $profiles = $repository->findAll();

        return $this->render('/admin/users.html.twig', array(
            'profiles' => $profiles
        ));
    }

    /**
     * @Route("/admin/users/supprimer/{id}", name="admin_delete_user")
     */
    public function deleteUser($id)
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $manager->find('App\Entity\User', $id);
        $profile = $manager->find(Profile::class, $id);
        $manager->remove($user);
        $manager->flush();

        $this->addFlash('success', 'L\'utilisateur ' . $profile->getFirstname() . ' ' . $profile->getLastname() .' a bien été supprimé.');

        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/admin/users/update/{id}", name="admin_update_user")
     */
    public function updateUser($id, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $manager = $this->getDoctrine()->getManager();

        $profile = $manager->find(Profile::class, $id);
        $user = $manager->find(User::class, $id);

        $formProfile = $this->createForm(ProfileFormType::class, $profile);
        $formUser = $this->createForm(RegisterFormType::class, $user);

        $formProfile->handleRequest($request);
        $formUser->handleRequest($request);

        if ($formProfile->isSubmitted() && $formProfile->isValid()) {
            $manager->persist($profile);
            if ($profile->getFile()) {
                $profile->removeFile();
                $profile->uploadFile();
            }
            $manager->flush();

            $this->addFlash('success', 'L\'utilisateur ' . $profile->getFirstname() . ' ' . $profile->getLastname() . ' a bien été mis à jour.');
            return $this->redirectToRoute('admin_users');
        }

        if ($formUser->isSubmitted() && $formUser->isValid()) {
            $manager->persist($user);
            $password = $user->getPassword();
            $user->setPassword($encoder->encodePassword($user, $password));
            $manager->flush();

            $this->addFlash('success', 'L\'utilisateur ' . $profile->getFirstname() . ' ' . $profile->getLastname() . ' a bien été mis à jour.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/updateuser.html.twig', [
            'ProfileForm' => $formProfile->createView(),
            'UserForm' => $formUser->createView(),
        ]);
    }



    /**
     * @Route("/admin/mon-profile", name="admin_profile")
     */
    public function profile(Request $request, UserPasswordEncoderInterface $encoder)
    {

        $manager = $this->getDoctrine()->getManager();

        $profile = $this->getUser()->getProfile();
        $user = $this->getUser();

        // get form
        $formProfile = $this->createForm(ProfileFormType::class, $profile);
        $formProfile->handleRequest($request);
        $formUser = $this->createForm(RegisterFormType::class, $user);
        $formUser->handleRequest($request);

        if ($formProfile->isSubmitted() && $formProfile->isValid()) {
            $manager->persist($profile);
            if ($profile->getFile()) {
                // $profile -> removeFile();
                $profile->uploadFile();
            }
            $manager->flush();
            $this->addFlash('success', 'Votre profil à bien été modifié.');
            return $this->redirectToRoute('admin_profile');
        }


        if ($formUser->isSubmitted() && $formUser->isValid()) {
            $manager->persist($user);
            $password = $user->getPassword();
            $user->setPassword($encoder->encodePassword($user, $password));
            $manager->flush();

            $this->addFlash('success', 'Votre profil à bien été modifié.');
            return $this->redirectToRoute('admin_profile');
        }

        return $this->render('admin/adminprofile.html.twig', [
            'ProfileForm' => $formProfile->createView(),
            'UserForm' => $formUser->createView()
        ]);
    }
}
