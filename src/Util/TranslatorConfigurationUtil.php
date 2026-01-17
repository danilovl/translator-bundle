<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Util;

class TranslatorConfigurationUtil
{
    private bool $isEnabled = true;

    private bool $isAutoAdminRefreshCache = false;

    private bool $isEnabledDashboardController = false;

    private string $kernelCacheDir;

    private string $kernelProjectDir;

    private string $translatorDefaultPath;

    /**
     * @var string[]
     */
    private array $locales;

    /**
     * @var string[]
     */
    private array $domains;

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function isAutoAdminRefreshCache(): bool
    {
        return $this->isAutoAdminRefreshCache;
    }

    public function setIsAutoAdminRefreshCache(bool $isAutoAdminRefreshCache): void
    {
        $this->isAutoAdminRefreshCache = $isAutoAdminRefreshCache;
    }

    public function isEnabledDashboardController(): bool
    {
        return $this->isEnabledDashboardController;
    }

    public function setIsEnabledDashboardController(bool $isEnabledDashboardController): void
    {
        $this->isEnabledDashboardController = $isEnabledDashboardController;
    }

    public function getKernelCacheDir(): string
    {
        return $this->kernelCacheDir;
    }

    public function setKernelCacheDir(string $kernelCacheDir): void
    {
        $this->kernelCacheDir = $kernelCacheDir;
    }

    public function getTranslatorDefaultPath(): string
    {
        return $this->translatorDefaultPath;
    }

    public function setTranslatorDefaultPath(string $translatorDefaultPath): void
    {
        $this->translatorDefaultPath = $translatorDefaultPath;
    }

    public function getKernelProjectDir(): string
    {
        return $this->kernelProjectDir;
    }

    public function setKernelProjectDir(string $kernelProjectDir): void
    {
        $this->kernelProjectDir = $kernelProjectDir;
    }

    public function getTranslationsKernelCacheDir(): string
    {
        return $this->kernelCacheDir . '/translations';
    }

    /**
     * @return string[]
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    /**
     * @return string[]
     */
    public function getDomains(): array
    {
        return $this->domains;
    }

    public function setDomains(array $domains): void
    {
        $this->domains = $domains;
    }

    public function getTranslationFileName(string $locale, string $domain): string
    {
        return sprintf('%s/%s.%s.yaml',
            $this->getTranslatorDefaultPath(),
            $domain,
            $locale
        );
    }
}
