<?php

namespace GaylordP\UserMediaBundle\Form;

use GaylordP\UserMediaBundle\Entity\UserMediaComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserMediaCommentType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('content', null, [
                'label' => false,
                'ico' => 'fas fa-pencil-alt',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'label.your_comment',
                ],
                'translation_domain' => 'user_media',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserMediaComment::class,
        ]);
    }
}
