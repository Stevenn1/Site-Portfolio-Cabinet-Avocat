<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\IsTrue;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'attr' => [
                    'class' => 'form-label',
                    'readonly' => true
                ]
            ])
            ->add('author', TextType::class, [
                'label' => 'Votre pseudo',
                'attr' => [
                    'class' => 'form-label',
                    'readonly' => true
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Votre commentaire',
                'attr' => [
                    'class' => 'form-label',
                    'id' => 'message-text'
                ]
            ])
            ->add('rgpd', CheckboxType::class, [
                'label' => 'J\'accepte le traitement de mes données personnelles conformément à la politique de confidentialité.
                ',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter le rgpd'
                    ])
                ]
            ])
            /*->add('createdAt', DateTimeType::class)*/
            ->add('parentid', HiddenType::class, [
                'mapped' => false
            ])
            ->add('envoyer', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
