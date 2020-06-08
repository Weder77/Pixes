<?php

namespace App\Controller;

use DateTime;
use App\Entity\Tag;
use App\Entity\Code;
use App\Entity\Game;
use App\Entity\User;
use App\Entity\Opinion;
use App\Entity\Profile;
use App\Entity\Platform;
use App\Form\AddGameType;
use App\Form\OpinionType;
use App\Form\TagsFormType;
use App\Form\ProfileFormType;
use App\Form\RegisterFormType;
use App\Form\PlateformFormType;
use App\Repository\TagRepository;
use App\Service\Game\GameService;
use App\Form\GenerateCodeFormType;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Repository\InvoiceRepository;
use App\Repository\OpinionRepository;
use App\Repository\PlatformRepository;
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
            'lastUsers' => $userRepository->findBy([], ['id' => 'DESC'], 5),
            'lastOpinions' => $opinionRepository->findBy([], ['id' => 'DESC'], 5)
        ));
    }

    /**
     * @Route("/admin/jeux", name="admin_games")
     */
    public function games(GameRepository $gameRepository)
    {
        return $this->render('/admin/games/games.html.twig', array(
            'games' => $gameRepository->findAll(),
        ));
    }

    /**
     * @Route("/admin/jeux/supprimer/{id}", name="admin_delete_game")
     */
    public function deleteGame($id, GameRepository $gameRepository)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = $gameRepository->find($id);
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
        $formAdd = $this->createForm(AddGameType::class, $game);

        $formAdd->handleRequest($request);
        if ($formAdd->isSubmitted() && $formAdd->isValid()) {
            $manager->persist($game);
            $game->uploadFile();
            $game->setSlug($gameService->generateSlug($game->getName()));
            $manager->flush();
            $this->addFlash('success', 'Le jeu ' . $game->getName() . ' a bien été ajouté.');
        }

        return $this->render('admin/games/addgame.html.twig', array(
            'gameForm' => $formAdd->createView(),
        ));
    }

    /**
     * @Route("/admin/jeu/update/{id}", name="admin_update")
     */
    public function updateGame($id, Request $request, GameRepository $gameRepository, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $game = $gameRepository->find($id);
        $nameFile = $game->getImgUrl();
        $formGame = $this->createForm(AddGameType::class, $game);
        $formGame->handleRequest($request);

        if ($formGame->isSubmitted() && $formGame->isValid()) {
            $manager->persist($game);
            if ($game->getFile()) {
                if ($nameFile != "default.png") {
                    $game->removeFile();
                }
                $game->uploadFile();
            }
            $game->setSlug($gameService->generateSlug($game->getName()));
            $manager->flush();

            $this->addFlash('success', 'Le jeu ' . $game->getName() . ' a bien été mis à jour.');
            return $this->redirectToRoute('admin_games');
        }

        $codeQuantity = (int)$request->request->get('quantity');
        if ($codeQuantity != null) {
            for ($i = 0; $i < $codeQuantity; $i++) { 
                $code = new Code();
                $code->setGame($game);
                $code->setUsed(0);
                $code->setCode($gameService->generateCode());
                $manager->persist($code);
                $manager->flush();
            }

            $this->addFlash('success', 'Les codes pour ' . $game->getName() . ' ont bien été ajoutés.');
            return $this->redirectToRoute('admin_games');
        }

        return $this->render('admin/games/updategame.html.twig', [
            'gameForm' => $formGame->createView(),
        ]);
    }



    /**
     * @Route("/admin/plateformes-tags", name="admin_plateforms_tags")
     */
    public function plateformsAndTags(PlatformRepository $platformRepository, TagRepository $tagRepository)
    {
        return $this->render('/admin/plateformsandtags.html.twig', array(
            'plateforms' => $platformRepository->findAll(),
            'tags' => $tagRepository->findAll(),
        ));
    }


    /**
     * @Route("/admin/tag/supprimer/{id}", name="admin_delete_tag")
     */
    public function deleteTag($id, TagRepository $tagRepository)
    {
        $manager = $this->getDoctrine()->getManager();
        $tag = $tagRepository->find($id);
        $manager->remove($tag);
        $manager->flush();

        $this->addFlash('success', 'Le tag ' . $tag->getName() . ' a bien été supprimé.');
        return $this->redirectToRoute('admin_plateforms_tags');
    }

    /**
     * @Route("/admin/plateforme/supprimer/{id}", name="admin_delete_plateform")
     */
    public function deletePlateform($id, PlatformRepository $platformRepository)
    {
        $manager = $this->getDoctrine()->getManager();
        $palteform = $platformRepository->find($id);
        $manager->remove($palteform);
        $manager->flush();

        $this->addFlash('success', 'La plateforme ' . $palteform->getName() . ' a bien été supprimé.');
        return $this->redirectToRoute('admin_plateforms_tags');
    }

      /**
     * @Route("/admin/tag/ajouter", name="admin_add_tag")
     */
    public function addTag(Request $request, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $tag = new Tag();
        $form = $this->createForm(TagsFormType::class, $tag);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($tag);
            $tag->setSlug($gameService->generateSlug($tag->getName()));
            $manager->flush();
            $this->addFlash('success', 'Le tag ' . $tag->getName() . ' a bien été ajouté.');
        }


        return $this->render('admin/tags/addtag.html.twig', array(
            'tagForm' => $form->createView(),
        ));
    }

        /**
     * @Route("/admin/plateforme/ajouter", name="admin_add_plateform")
     */
    public function addPlateform(Request $request, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $palteform = new Platform();
        $form = $this->createForm(PlateformFormType::class, $palteform);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($palteform);
            $palteform->setSlug($gameService->generateSlug($palteform->getName()));
            $manager->flush();
            $this->addFlash('success', 'Le tag ' . $palteform->getName() . ' a bien été ajouté.');
        }


        return $this->render('admin/plateforms/addplateform.html.twig', array(
            'palteformForm' => $form->createView(),
        ));
    }

      /**
     * @Route("/admin/tag/update/{id}", name="admin_update_tag")
     */
    public function updateTag($id, Request $request, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $tag = $manager->find(Tag::class, $id);
        $form = $this->createForm(TagsFormType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($tag);
            $tag->setSlug($gameService->generateSlug($tag->getName()));
            $manager->flush();

            $this->addFlash('success', 'Le tag ' . $tag->getName() . ' a bien été mis à jour.');
        }

        return $this->render('admin/tags/updatetag.html.twig', [
            'tagForm' => $form->createView(),
        ]);
    }

          /**
     * @Route("/admin/plateforme/update/{id}", name="admin_update_plateform")
     */
    public function updatePlateform($id, Request $request, GameService $gameService)
    {
        $manager = $this->getDoctrine()->getManager();
        $plateform = $manager->find(Platform::class, $id);
        $form = $this->createForm(PlateformFormType::class, $plateform);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($plateform);
            $plateform->setSlug($gameService->generateSlug($plateform->getName()));
            $manager->flush();

            $this->addFlash('success', 'La plateforme ' . $plateform->getName() . ' a bien été mis à jour.');
        }

        return $this->render('admin/plateforms/updateplateform.html.twig', [
            'plateformForm' => $form->createView(),
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

        return $this->render('/admin/orders/orders.html.twig', [
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
        $userCount = 0;
        foreach ($userRepository->findAll() as $user) {
            $userCount += 1;
        }

        return $this->render('/admin/users/users.html.twig', [
            'users' => $userRepository->findAll(),
            'userCount' => $userCount,
        ]);
    }

    /**
     * @Route("/admin/utilisateurs/supprimer/{id}", name="admin_delete_user")
     */
    public function deleteUser($id, UserRepository $userRepository)
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $userRepository->find($id);
        $profile = $user->getProfile();

        // Boucle sur les factures de l'utilisateur pour les attribuer à l'admin
        foreach ($profile->getInvoices() as $invoice) {
            $invoice->setProfile($this->getUser()->getProfile());
            $manager->persist($invoice);
            $manager->flush();
        }
        
        // Boucle sur les commentaire de l'utilisateur pour les supprimer
        foreach ($profile->getOpinions() as $opinion) {
            $manager->remove($opinion);
            $manager->flush();
        }

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
                if ($profile->getPicture() != null) {
                    $profile->removeFile();
                }
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

        return $this->render('admin/users/updateuser.html.twig', [
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
                if ($profile->getPicture() != null) {
                    $profile->removeFile();
                }
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

        return $this->render('/admin/opinions/opinions.html.twig', array(
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


    /**
     * @Route("/admin/opinions/modifier/{slug}/{id}", name="admin_update_opinion")
     */
    public function updateOpinion($slug, $id, Request $request,GameRepository $gameRepository)
    {
        $manager = $this->getDoctrine()->getManager();

        $game = $gameRepository->findOneBy(['slug' => $slug]);

        $opinion = $manager->find(Opinion::class, $id);

        $form = $this->createForm(OpinionType::class, $opinion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($opinion);
            // Si note en dehors de l'échelle
            if ($opinion->getNote() > 5) {
                $opinion->setNote(5);
            } elseif ($opinion->getNote() < 1) {
                $opinion->setNote(1);
            }

            $opinion->setPostedOn(new DateTime());
            $opinion->setGame($game);
            $manager->flush();

            // Redirection sur la route pour afficher le commentaire
            $this->addFlash('success', 'Le commentaire a bien été modifié !');
            return $this->redirectToRoute('admin_opinions');
        }
        return $this->render('admin/opinions/updateopinion.html.twig', [
            'opinionForm' => $form->createView(),
        ]);
    }

}
