<?php

namespace App\Controller;

use App\Entity\Code;
use App\Entity\Game;
use App\Entity\User;
use App\Entity\Profile;
use App\Form\AddGameType;
use App\Form\CodesFormType;
use App\Form\ProfileFormType;
use App\Form\RegisterFormType;
use App\Service\Game\GameService;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Repository\InvoiceRepository;
use App\Repository\OpinionRepository;
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
    public function index(InvoiceRepository $invoiceRepository, UserRepository $userRepository, OpinionRepository $opinionRepository)
    {
        return $this->render('/admin/index.html.twig', array(
            'lastInvoices' => $invoiceRepository->findBy([], ['purchase_date' => 'DESC'], 5),
            'lastUsers' => $invoiceRepository->findBy([], ['purchase_date' => 'DESC'], 5),
            'lastOpinions' => $opinionRepository->findBy([], ['id' => 'DESC'], 5)
        ));
    }

    /**
     * @Route("/admin/jeux", name="admin_games")
     */
    public function games(GameRepository $gameRepository)
    {
        return $this->render('/admin/games.html.twig', array(
            'games' => $gameRepository->findAll(),
        ));
    }

    /**
     * @Route("/admin/jeux/supprimer/{id}", name="admin_delete_game")
     */
    public function deleteGame($id, GameRepository $gameRepository)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = $gameRepository->find('App\Entity\Game', $id);
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
        $game = new Game();
        $code = new Code();

        $formAdd = $this->createForm(AddGameType::class, $game);
        $formCode = $this->createForm(CodesFormType::class, $code);

        $formAdd->handleRequest($request);
        if ($formAdd->isSubmitted() && $formAdd->isValid()) {
            $manager->persist($game);
            $game->uploadFile();
            $game->setSlug($gameService->generateSlug($game->getName()));
            $manager->flush();
            $this->addFlash('success', 'Le jeu ' . $game->getName() . ' a bien été ajouté.');
        }

        $formCode->handleRequest($request);
        if ($formCode->isSubmitted() && $formCode->isValid()) {
            $manager->persist($code);

            $manager->flush();
            $this->addFlash('success', 'Les codes ont bien été générés.');
        }

        return $this->render('admin/addgame.html.twig', array(
            'gameForm' => $formAdd->createView(),
            'gameCode' => $formCode->createView()
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
    public function users(UserRepository $userRepository)
    {
        return $this->render('/admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/utilisateurs/supprimer/{id}", name="admin_delete_user")
     */
    public function deleteUser($id)
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $manager->find('App\Entity\User', $id);
        $profile = $manager->find(Profile::class, $id);
        $manager->remove($user);
        $manager->flush();

        $this->addFlash('success', 'L\'utilisateur ' . $profile->getFirstname() . ' ' . $profile->getLastname() . ' a bien été supprimé.');
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/admin/utilisateurs/modifier/{id}", name="admin_update_user")
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
     * @Route("/admin/mon-compte", name="admin_profile")
     */
    public function profile(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $profile = $user->getProfile();

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


    /**
     * @Route("/admin/opinions", name="admin_opinions")
     */
    public function opinions(OpinionRepository $opinionRepository)
    {
        $opinions = $opinionRepository->findBy([], ['id' => 'DESC']);

        return $this->render('/admin/opinions.html.twig', array(
            'opinions' => $opinions
        ));
    }

    /**
     * @Route("/admin/opinions/supprimer/{id}", name="admin_delete_opinion")
     */
    public function deleteOpinion($id)
    {
        $manager = $this->getDoctrine()->getManager();
        $opinion = $manager->find('App\Entity\Opinion', $id);
        $manager->remove($opinion);
        $manager->flush();

        $this->addFlash('success', 'Le commentaire a bien été supprimé.');
        return $this->redirectToRoute('admin_opinions');
    }

}
