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
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{
    InputArgument,
    InputInterface
};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'danilovl:translator:transform-to-format')]
class TransformToFormatCommand extends Command
{
    private readonly SymfonyStyle $io;

    public function __construct(
        private readonly TranslatorConfigurationUtil $translatorConfigurationUtil
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Change translation file format.')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'File name or file path.'
            )
            ->addArgument(
                'mode',
                InputArgument::REQUIRED,
                'Format mode.',
                suggestedValues: YamlFormatConstant::values()
            )
            ->addArgument(
                'order',
                InputArgument::OPTIONAL,
                'Order mode.',
                suggestedValues: OrderConstant::values()
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $file */
        $file = $input->getArgument('file');
        /** @var string $mode */
        $mode = $input->getArgument('mode');
        $mode = YamlFormatConstant::from($mode);

        /** @var string|null $order */
        $order = $input->getArgument('order');
        $order = $order ?OrderConstant::from($order) : null;

        $isExist = false;
        if (file_exists($file)) {
            $isExist = true;
        } else {
            $file = sprintf('%s/%s', $this->translatorConfigurationUtil->getTranslatorDefaultPath(), $file);
            if (file_exists($file)) {
                $isExist = true;
            }
        }

        if (!$isExist) {
            $this->io->error('File not found.');

            return Command::FAILURE;
        }

        $this->io->section($file);

        /** @var array $translations */
        $translations = Yaml::parseFile($file);

        if ($order) {
            $translations = ArrayHelper::sort(ArrayHelper::flattenArray($translations), $order);
        }

        $result = match ($mode) {
            YamlFormatConstant::FLATTEN => ArrayHelper::flattenArray($translations),
            YamlFormatConstant::DOT_NESTED => ArrayHelper::dotToNested($translations),
        };

        (new Filesystem())->dumpFile($file, YamlHelper::defaultDump($result));

        $this->io->success('Done');

        return Command::SUCCESS;
    }
}
