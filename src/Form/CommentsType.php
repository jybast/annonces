<?php

namespace App\Form;

use App\Entity\Comments;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            

            ->add('email', EmailType::class, ['label' => 'Votre e-mail', 'attr' => ['class' => 'form-control']])
            ->add('nickname', TextType::class, ['label' => 'Votre pseudo', 'attr' => ['class' => 'form-control']])
            ->add('content', CKEditorType::class, ['label' => 'Votre commentaire', 'attr' => ['class' => 'form-control']])
            ->add('rgpd', CheckboxType::class, ['label' => 'J\'accepte l\'enregistrement de mes données',
                                                'constraints' => [ new NotBlank()] ])
            //->add('annonces')  // C'est l'annonce sur laquelle on fait le commentaire
            ->add('parentid', HiddenType::class, ['mapped' => false])      // C'est le commentaire auquel on fait référence
            ->add('Envoyer', SubmitType::class)
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comments::class,
        ]);
    }
}
