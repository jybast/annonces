<?php

namespace App\Form;

use App\Entity\Calendrier;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class CalendrierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Rendez-vous',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('start', DateTimeType::class, [
                'label' => 'Début',
                'date_widget' => 'single_text'
            ])
            ->add('end', DateTimeType::class, [
                'label' => 'Fin',
                'date_widget' => 'single_text'
            ])
            ->add('description', CKEditorType::class)
            ->add('all_day', CheckboxType::class, [
                'label'    => 'Journée entière ?',
                'required' => false,
            ])
            ->add('background_color', ColorType::class, [
                'label' => 'Couleur de fond :',])
            ->add('border_color', ColorType::class, [
                'label' => 'Couleur de bordure :',])
            ->add('text_color', ColorType::class, [
                'label' => 'Couleur de texte :',])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Calendrier::class,
        ]);
    }
}
