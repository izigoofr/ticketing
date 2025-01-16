<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\Sandbox;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('start_date')
            ->add('dead_line')
            ->add('status')
            ->add('priority')
            ->add('applicant')
            ->add('type')
            ->add('content')
            ->add('attachment')
            ->add('mailApplicant')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'id',
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'id',
            ])
            ->add('sandboxes', EntityType::class, [
                'class' => Sandbox::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
