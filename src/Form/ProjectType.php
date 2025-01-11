<?php

namespace App\Form;

use App\Entity\Project;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('client')
            ->add('attachment', FileType::class, [
                'label' => 'Attachment (PDF file)',
                'mapped' => false, // Ne lie pas ce champ à l'entité
                'required' => false,

            ])
            ->add('sandboxes')
        ;
           // ->add('start_date')
           // ->add('dead_line')
          //  ->add('status')
          //  ->add('priority')
          //  ->add('applicant')
          //  ->add('type')
          //  ->add('content')
          //  ->add('user')
          //  ->add('team')

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
