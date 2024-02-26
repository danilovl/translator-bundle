<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\EventSubscriber;

use Danilovl\TranslatorBundle\Entity\Translator;
use Danilovl\TranslatorBundle\Service\TranslationCacheService;
use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TranslatorConfigurationUtil $translatorConfigurationUtil,
        private readonly TranslationCacheService $translationCacheService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityUpdatedEvent::class => 'onAfterEntityUpdatedEvent',
            AfterEntityPersistedEvent::class => 'onAfterEntityPersistedEvent',
            AfterEntityDeletedEvent::class => 'onAfterEntityDeletedEvent'
        ];
    }

    public function onAfterEntityUpdatedEvent(AfterEntityUpdatedEvent $event): void
    {
        if (!$this->translatorConfigurationUtil->isAutoAdminRefreshCache()) {
            return;
        }

        /** @var Translator $entity */
        $entity = $event->getEntityInstance();
        $this->refreshCache($entity);
    }

    public function onAfterEntityPersistedEvent(AfterEntityPersistedEvent $event): void
    {
        if (!$this->translatorConfigurationUtil->isAutoAdminRefreshCache()) {
            return;
        }

        /** @var Translator $entity */
        $entity = $event->getEntityInstance();
        $this->refreshCache($entity);
    }

    public function onAfterEntityDeletedEvent(AfterEntityDeletedEvent $event): void
    {
        if (!$this->translatorConfigurationUtil->isAutoAdminRefreshCache()) {
            return;
        }

        /** @var Translator $entity */
        $entity = $event->getEntityInstance();
        $this->refreshCache($entity);
    }

    private function refreshCache(Translator $translator): void
    {
        if (!$this->translatorConfigurationUtil->isAutoAdminRefreshCache()) {
            return;
        }

        $locale = $translator->getLocale();
        $translationCacheDir = $this->translatorConfigurationUtil->getTranslationsKernelCacheDir();

        $this->translationCacheService->clear($locale, $translationCacheDir);
        $this->translationCacheService->warmUpCatalogue($locale);
    }
}
