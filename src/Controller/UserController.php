<?php

namespace App\Controller;

use App\Entity\User;
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
        $user = new User; // objet vide de l'entity Post

        // formulaire...
        $form = $this->createForm(RegisterFormType::class, $user);

        // traitement des infos du formulaire
        $form->handleRequest($request); // lier definitivement le $post aux infos du formulaire (recupere les donner en saisies en $_POST)

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user); // enregistrer le post dans le systeme

            //  encodage du mot de passer
            $password = $user->getPassword();
            $user->setPassword($encoder->encodePassword($user, $password));

            $manager->flush(); // execute toutes les requetes en attentes
            $this->addFlash('success', 'Le compte à bien été créer !');

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
     * route nécessaire pour le fonctionnement de sécurité de ma connexion
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
        return $this->redirectToRoute('login');
    }
}
