<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
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

        $form = $this->createForm(RegisterFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $password = $user->getPassword();
            $user->setPassword($encoder->encodePassword($user, $password));
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
    public function profile(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $user = $this->getUser()->getProfile();

        $balance = $this->getUser()->getProfile()->getBalance();
        $firstname = $this->getUser()->getProfile()->getFirstname();

        $form = $this->createForm(ProfileFormType::class,$user);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);

            $manager->flush();

            $this->addFlash('success', 'Votre profil à bien été modifié.');
            return $this->redirectToRoute('profile');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'ProfileForm' => $form->createView(),
            'balance' => $balance,
            'firstname' => $firstname,
        ]);
    }
}
