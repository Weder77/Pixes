<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Platform;
use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class AddGameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price', IntegerType::class)
            ->add('file', FileType::class, array(
                'required' => false
            ))
            ->add('pegi',  IntegerType::class)
            ->add('slug')
            ->add('platforms', EntityType::class, array(
                'class' => Platform::class,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
            ))
            ->add('tags', EntityType::class, array(
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
