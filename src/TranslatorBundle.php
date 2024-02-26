<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle;

use Danilovl\TranslatorBundle\DependencyInjection\Compiler\TranslatorCompilerPass;
use Danilovl\TranslatorBundle\DependencyInjection\TranslatorExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TranslatorBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new TranslatorCompilerPass);
    }

    public function getContainerExtension(): TranslatorExtension
    {
        return new TranslatorExtension;
    }
}
