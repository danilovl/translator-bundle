<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Command;

use Danilovl\TranslatorBundle\Constant\{
    OrderConstant,
    YamlFormatConstant
};
use Danilovl\TranslatorBundle\Helper\{
    YamlHelper,
    ArrayHelper
};
use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use Symfony\Component\Console\Attribute\{
    Argument,
    AsCommand
};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'danilovl:translator:transform-to-format',
    description: 'Change translation file format.'
)]
class TransformToFormatCommand
{
    public function __construct(private readonly TranslatorConfigurationUtil $translatorConfigurationUtil) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'File name or file path.')]
        string $file,
        #[Argument(description: 'Format mode.')]
        YamlFormatConstant $mode,
        #[Argument(description: 'Order mode.')]
        ?OrderConstant $order = null
    ): int {
        if (!file_exists($file)) {
            $file = sprintf('%s/%s', $this->translatorConfigurationUtil->getTranslatorDefaultPath(), $file);
        }

        if (!file_exists($file)) {
            $io->error('File not found.');

            return Command::FAILURE;
        }

        $io->section($file);

        /** @var array $translations */
        $translations = Yaml::parseFile($file);

        if ($order) {
            $translations = ArrayHelper::sort(ArrayHelper::flattenArray($translations), $order);
        }

        $result = match ($mode) {
            YamlFormatConstant::FLATTEN => ArrayHelper::flattenArray($translations),
            YamlFormatConstant::DOT_NESTED => ArrayHelper::dotToNested($translations),
        };

        new Filesystem()->dumpFile($file, YamlHelper::defaultDump($result));

        $io->success('Done');

        return Command::SUCCESS;
    }
}
