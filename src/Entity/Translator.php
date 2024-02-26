<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Entity;

use Danilovl\TranslatorBundle\Repository\TranslatorRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name: 'translator')]
#[ORM\UniqueConstraint(name: 'idx_unique_translator', columns: ['locale', 'domain', 'key'])]
#[UniqueEntity(fields: ['locale', 'domain', 'key'])]
#[ORM\Index(fields: ['domain'])]
#[ORM\Entity(repositoryClass: TranslatorRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Translator
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    private int $id;

    #[ORM\Column(name: 'locale', type: Types::STRING, length: 10, nullable: false)]
    private string $locale;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: false, options: ['default' => 'messages'])]
    private string $domain;

    #[ORM\Column(name: '`key`', type: Types::STRING, length: 191, nullable: false)]
    private string $key;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $value;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    protected DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $updatedAt = null;

    #[ORM\PrePersist]
    public function timestampAblePrePersist(): void
    {
        $this->createdAt = new DateTimeImmutable;
    }

    #[ORM\PreUpdate]
    public function timestampAblePreUpdate(): void
    {
        $this->updatedAt = new DateTime;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }
}
