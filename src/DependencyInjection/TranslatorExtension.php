<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\DependencyInjection;

use Danilovl\TranslatorBundle\Admin\DashboardController;
use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\Extension;

class TranslatorExtension extends Extension
{
    final public const string ALIAS = 'danilovl_translator';
    private const string DIR_CONFIG = '/../Resources/config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $parameterBag = $container->getParameterBag();

        $kernelCacheDir = $parameterBag->get('kernel.cache_dir');
        $kernelProjectDir = $parameterBag->get('kernel.project_dir');
        $translatorDefaultPath = $parameterBag->get('translator.default_path');

        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . self::DIR_CONFIG));
        $loader->load('services.yaml');

        if (!$config['enabledDashboardController']) {
            $container->removeDefinition(DashboardController::class);
        }

        $translatorConfiguration = $container->getDefinition(TranslatorConfigurationUtil::class);
        $translatorConfiguration->addMethodCall('setIsEnabled', [$config['enabled']]);
        $translatorConfiguration->addMethodCall('setIsAutoAdminRefreshCache', [$config['enabledAutoAdminRefreshCache']]);
        $translatorConfiguration->addMethodCall('setIsEnabledDashboardController', [$config['enabledDashboardController']]);
        $translatorConfiguration->addMethodCall('setLocales', [$config['locale']]);
        $translatorConfiguration->addMethodCall('setDomains', [$config['domain']]);
        $translatorConfiguration->addMethodCall('setKernelCacheDir', [$kernelCacheDir]);
        $translatorConfiguration->addMethodCall('setKernelProjectDir', [$kernelProjectDir]);
        $translatorConfiguration->addMethodCall('setTranslatorDefaultPath', [$translatorDefaultPath]);
    }

    public function getAlias(): string
    {
        return self::ALIAS;
    }
}
