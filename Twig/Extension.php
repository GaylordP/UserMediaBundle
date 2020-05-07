<?php

namespace GaylordP\UserMediaBundle\Twig;

use GaylordP\UserMediaBundle\Util\IsImage;
use Symfony\Component\Form\FormView;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

class Extension extends AbstractExtension
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'uploadJs',
                [$this, 'uploadJs'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest(
                'image',
                [IsImage::class, 'check']
            ),
        ];
    }

    public function uploadJs(FormView $form): string
    {
        return $this->twig->render('@UserMedia/js/upload.js.twig', [
            'form' => $form,
        ]);
    }
}
