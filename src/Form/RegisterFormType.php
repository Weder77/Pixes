<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder


            ->add('email', EmailType::class)


            ->add('password', PasswordType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(array(
                        'message' => 'This field is required'
                    )),
                    new Assert\Length(array(
                        'min' => 2,
                        'max' => 180,
                        'minMessage' => 'Votre mot de passe doit avoir au moins 2 caractères.',
                        'maxMessage' => 'Votre mot de passe ne doit pas avoir plus de 180 caractères. '
                    ))
                ),
            ))

            ->add('Register', SubmitType::class, [
                'attr' => ['class' => 'btn btn-custom'],
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => array(
                'novalidate' => 'novalidate' // = on ne veut pas de vérif html5
            )
        ]);
    }
}
