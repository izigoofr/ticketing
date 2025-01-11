<?php

namespace App\Form;

use App\Entity\Sandbox;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SandboxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('taggedUsers', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', // Remplacez par le champ approprié (par ex. "email" ou "name")
                'multiple' => true,          // Permet de sélectionner plusieurs utilisateurs
                'expanded' => true,          // Affiche comme des cases à cocher (true) ou une liste déroulante (false)
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sandbox::class,
        ]);
    }
}
