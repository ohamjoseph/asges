<?php

namespace App\Form;

use App\Entity\Activite;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('dateDebut', DateType::class,[
                    'widget' => 'single_text',
                    'attr' => ['class' => 'js-datepicker'],
                ]
            )
            ->add('dateFin', DateType::class,[
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('Description')
            ->add('brochure', FileType::class, [
                'label' => "Flyers de l'activité (image)",
                'mapped' => false,
                'required' => false,
            ])



        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
        ]);
    }
}
