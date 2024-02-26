<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\DependencyInjection\Compiler;

use Danilovl\TranslatorBundle\Loader\DatabaseLoader;
use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TranslatorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var TranslatorConfigurationUtil $translatorConfig */
        $translatorConfig = $container->get(TranslatorConfigurationUtil::class);
        if (!$translatorConfig->isEnabled()) {
            return;
        }

        $databaseLoader = $container->getDefinition(DatabaseLoader::class);
        $translator = $container->getDefinition('translator.default');
        $translator->addMethodCall('addLoader', ['database', $databaseLoader]);

        foreach ($translatorConfig->getLocales() as $locale) {
            foreach ($translatorConfig->getDomains() as $domain) {
                $translator->addMethodCall('addResource', ['database', 'translator', $locale, $domain]);
            }
        }
    }
}
