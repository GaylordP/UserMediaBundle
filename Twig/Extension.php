<?php

namespace GaylordP\UserMediaBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
    private $request;
    private $parameterUserMedia;
    private $views = [
        'top' => [],
        'bottom' => [],
    ];

    public function __construct(
        RequestStack $requestStack,
        array $parameterUserMedia
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->parameterUserMedia = $parameterUserMedia;

        $this->initViews();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'user_media_item_control',
                [$this, 'userMediaItemControl'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function userMediaItemControl(): array
    {
        return $this->views;
    }

    private function initViews(): void
    {
        if (
            null !== $this->request
                &&
            array_key_exists($this->request->get('_route'), $this->parameterUserMedia['item_control'])
        ) {
            $controls = $this->parameterUserMedia['item_control'][$this->request->get('_route')];

            foreach (['top', 'bottom'] as $position) {
                if (array_key_exists($position, $controls)) {
                    foreach ($controls[$position] as $route) {
                        $this->views[$position][] = $this->parameterUserMedia['item_control_view'][$route];
                    }
                }
            }
        }
    }
}