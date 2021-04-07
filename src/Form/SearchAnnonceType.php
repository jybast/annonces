<?php

namespace App\Form;

use App\Entity\Categories;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour la recherhe FullText dans Annonces
 */
class SearchAnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mot', SearchType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Rechercher'
                ],
                'required' => false,
            ])
            ->add('categorie', EntityType::class, [
                'label' => false,
                'class' => Categories::class,
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
            ->add('Rechercher', SubmitType::class, [
                'attr' => [
                    'class' => 'btn small rounded-2 success'
                ]
                
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
