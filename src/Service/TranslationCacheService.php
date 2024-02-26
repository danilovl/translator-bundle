<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationCacheService
{
    public function __construct(private readonly TranslatorInterface $translatorDefault) {}

    public function warmUpCatalogue(string $locale): void
    {
        /** @var Translator $translator */
        $translator = $this->translatorDefault;
        $translator->getCatalogue($locale);
    }

    public function clear(string $locale, string $cacheDir): void
    {
        $filesToRemove = $this->getFilesToRemove($locale, $cacheDir);
        (new Filesystem())->remove($filesToRemove);
    }

    /**
     * @return string[]
     */
    private function getFilesToRemove(string $locale, string $cacheDir): array
    {
        if (!is_dir($cacheDir)) {
            return [];
        }

        $name = sprintf('catalogue.%s.*', $locale);

        $finder = new Finder();
        $finder->files()
            ->name($name)
            ->in($cacheDir);

        $files = [];
        foreach ($finder as $item) {
            $files[] = $item->getRealPath();
        }

        return $files;
    }
}
