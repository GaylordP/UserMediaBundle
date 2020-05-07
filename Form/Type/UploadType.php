<?php

namespace GaylordP\UserMediaBundle\Form\Type;

use GaylordP\UserMediaBundle\Form\DataTransformer\UploadTransformer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UploadType extends AbstractType
{
    private $uploadDirectory;
    private $uploadTransformer;
    private $validator;
    private $translator;

    public function __construct(
        ParameterBagInterface $parameters,
        UploadTransformer $uploadTransformer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        $this->uploadDirectory = $parameters->get('upload_directory');
        $this->uploadTransformer = $uploadTransformer;
        $this->validator = $validator;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*
        $builder
            ->addModelTransformer($this->uploadTransformer)
            ->addEventListener(FormEvents::POST_SUBMIT, [
                $this,
                'onPostSubmit',
            ])
        ;
        */
    }

    /*
     * Les erreurs sont désormais réalisées en live par UserUploadController.php
    public function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $constraints = $form->getConfig()->getOption('upload_constraints');
        $fileConstraints = new File(self::removeExtraFileConstraints($constraints));

        foreach ($form->getData() as $file) {
            $errors = $this->validator->validate($file, $fileConstraints);

            foreach ($errors as $error) {
                $form->addError(
                    new FormError(
                        $file->getFileName() . ' : ' . $error->getMessage()
                    )
                );
            }
        }

        if (($countData = count($form->getData())) < $constraints['minFiles']) {
            $form->addError(
                new FormError(
                    $this->translator->trans('upload.min_file', [
                        '%count%' => $constraints['minFiles'],
                    ], 'validators'),
                )
            );
        }
    }
    */

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $constraints = $form->getConfig()->getOption('upload_constraints');
        $files = [];

        if (null !== $form->getData()) {
            foreach ($form->getData() as $file) {
                $files[] = [
                    'name' => $file->getFileName(),
                    'size' => $file->getSize(),
                    'path' => $this->uploadDirectory . $file->getPathname(),
                ];
            }
        }

        $view->vars['initial_files'] = $files;
        $view->vars['upload_constraints'] = $form->getConfig()->getOption('upload_constraints') + [
            'maxSizeBinary' => (new File(self::removeExtraFileConstraints($constraints)))->maxSize,
        ];

        $view->vars['row_attr']['id'] = 'dropzone-' . $view->vars['id'];
        $view->vars['row_attr']['class'] = 'dropzone';

        if (
            null !== $constraints['minFiles']
                &&
            0 !== $constraints['minFiles']
        ) {
            $view->vars['label_attr'] += [
                'class' => 'required',
            ];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'initial_files' => [],
            'upload_constraints' => [
                'minFiles' => 1,
                'maxSize' => '2G',
                'mimeTypes' => [
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                    'video/mp4',
                ],
            ],
        ]);
    }

    public static function removeExtraFileConstraints(array $constraints): array
    {
        unset($constraints['minFiles']);

        return $constraints;
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
