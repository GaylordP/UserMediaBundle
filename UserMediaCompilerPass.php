<?php

namespace GaylordP\UserMediaBundle;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UserMediaCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasParameter('twig.form.resources')) {
            $resources = $container->getParameter('twig.form.resources') ?: [];
            array_unshift($resources, '@UserMedia/form/fields.html.twig');
            $container->setParameter('twig.form.resources', $resources);
        }
    }
}
