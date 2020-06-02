<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Opinion;
use App\Entity\Profile;
use App\Form\ProfileFormType;
use App\Form\RegisterFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $manager = $this->getDoctrine()->getManager();
        $user = new User;
        $profile = new Profile();

        $form = $this->createForm(RegisterFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $user->getPassword();
            $user->setPassword($encoder->encodePassword($user, $password));

            $date = new DateTime();
            $profile->setRegisterDate($date);
            $profile->setBalance(0);
            $profile->setUser($user);

            $manager->persist($user);
            $manager->persist($profile);


            $manager->flush();

            $this->addFlash('success', 'Le compte à bien été créer, vous pouvez dès à présent vous connecter !');
            return $this->redirectToRoute('index');
        }

        return $this->render('/user/register.html.twig', array(
            'userForm' => $form->createView()
        ));
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user

        return $this->render('user/login.html.twig', [
            'error' => $error
        ]);
    }

    /**
     * route nécessaire pour le fonctionnement de sécurité de la connexion
     * @Route("/login_check", name="login_check")
     */
    public function loginCheck()
    {
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        $this->addFlash('success', 'Vous avez été déconnecté.');
        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/mon-compte", name="profile")
     */
    public function profile(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $manager = $this->getDoctrine()->getManager();

        $profile = $this->getUser()->getProfile();
        $user = $this->getUser();

        // get pictures user
        $picture = $this->getUser()->getProfile()->getPicture();

        // get opinions
        $opinions = [];
        $opinionsNumber = 0;
        foreach ($profile->getOpinions() as $key => $opinion) {
            $opinionsNumber += 1;
            array_push($opinions, $opinion);
        }

        // get purchashed games
        $invoices = [];
        $gamesNumber = 0;
        foreach ($profile->getInvoices() as $invoice) {
            $gamesNumber += 1;
            foreach ($invoice->getCodes() as $code) {
                array_push($invoices, $code);
            }
        }

        $ownedCodes = [];
        foreach ($profile->getInvoices() as $invoice) {
            foreach ($invoice->getCodes() as $ownedCode) {
                array_push($ownedCodes, $ownedCode->getCode());
            }
        }


        // get form
        $formProfile = $this->createForm(ProfileFormType::class, $profile);
        $formProfile->handleRequest($request);
        $formUser = $this->createForm(RegisterFormType::class, $user);
        $formUser->handleRequest($request);


        if ($formProfile->isSubmitted() && $formProfile->isValid()) {
            $manager->persist($profile);
            if($profile -> getFile()){
                $profile -> removeFile();
                $profile-> uploadFile();
            }
            $manager->flush();
            $this->addFlash('success', 'Votre profil à bien été modifié.');
            return $this->redirectToRoute('profile');
        }


        if ($formUser->isSubmitted() && $formUser->isValid()) {
            $manager->persist($user);
            $password = $user->getPassword();
            $user->setPassword($encoder->encodePassword($user, $password));
            $manager->flush();

            $this->addFlash('success', 'Votre profil à bien été modifié.');
            return $this->redirectToRoute('profile');
        }

        return $this->render('user/profile.html.twig', [
            'ProfileForm' => $formProfile->createView(),
            'UserForm' => $formUser->createView(),
            'opinions' => $opinions,
            'opinionsNumber' => $opinionsNumber,
            'invoices' => $invoices,
            'codes' => $ownedCodes,
            'gamesNumber' => $gamesNumber,
        ]);
    }
}
