<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Danilovl\TranslatorBundle\Command\{
    MigrationToDatabaseCommand,
    GenerateTranslationCommand,
    MigrationFromDatabaseCommand
};

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->public();

    $services->load('App\\TranslatorBundle\\', '../../../src')
        ->exclude('../../../src/DependencyInjection')
        ->exclude('../../../src/Entity')
        ->exclude('../../../src/Helper')
        ->exclude('../../../src/Resources');

    $services->set(GenerateTranslationCommand::class)
        ->call('setLogger', ['@?logger']);

    $services->set(MigrationToDatabaseCommand::class)
        ->call('setLogger', ['@?logger']);

    $services->set(MigrationFromDatabaseCommand::class)
        ->call('setLogger', ['@?logger']);
};
