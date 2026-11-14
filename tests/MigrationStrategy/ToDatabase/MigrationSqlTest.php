<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Tests\MigrationStrategy\ToDatabase;

use Danilovl\TranslatorBundle\MigrationStrategy\ToDatabase\{
    BaseMigration,
    DatabaseDiffMigration,
    FullMigration,
    OnlyNewMigration
};
use Danilovl\TranslatorBundle\Repository\TranslatorRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class MigrationSqlTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private Environment $twig;

    private SplFileInfo $file;

    protected function setUp(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection
            ->method('quote')
            ->willReturnCallback(static fn (string $value): string => self::mysqlQuote($value));

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager
            ->method('getConnection')
            ->willReturn($connection);

        $this->entityManager = $entityManager;

        $loader = new FilesystemLoader;
        $loader->addPath(__DIR__ . '/../../../src/Resources/views', 'Translator');

        // Same autoescape strategy as TwigBundle default: .html.twig => html escaping
        $this->twig = new Environment($loader, ['autoescape' => 'name']);

        $path = __DIR__ . '/../../Fixtures/translations/messages.en.yaml';
        $this->file = new SplFileInfo($path, '', 'messages.en.yaml');
    }

    public function testFullMigrationQuotesValuesAndKeys(): void
    {
        $sql = $this->getSql(new FullMigration($this->entityManager, $this->twig));

        $this->assertStringContainsString("('en', 'quote', 'It\\'s a test', 'messages', NOW())", $sql);
        $this->assertStringContainsString("('en', 'backslash', 'C:\\\\path\\\\', 'messages', NOW())", $sql);
        $this->assertStringContainsString("('en', 'injection', '\\\\\\'; DROP TABLE translator; -- ', 'messages', NOW())", $sql);
        $this->assertStringContainsString("('en', 'nested.key', 'value', 'messages', NOW());", $sql);
    }

    public function testFullMigrationDoesNotHtmlEscape(): void
    {
        $sql = $this->getSql(new FullMigration($this->entityManager, $this->twig));

        $this->assertStringContainsString("('en', 'a&b', 'A & B', 'messages', NOW())", $sql);
        $this->assertStringNotContainsString('&amp;', $sql);
        $this->assertStringNotContainsString('&#039;', $sql);
    }

    public function testOnlyNewMigrationFiltersByDomainAndLocale(): void
    {
        $sql = $this->getSql(new OnlyNewMigration($this->entityManager, $this->twig));

        $this->assertStringContainsString("('en', 'quote', 'It\\'s a test', 'messages', NOW())", $sql);
        $this->assertStringContainsString(
            "WHERE `domain` = 'messages' AND `locale` = 'en' AND `key` NOT IN (SELECT `key` FROM `translator` WHERE `domain` = 'messages' AND `locale` = 'en');",
            $sql
        );
        $this->assertStringNotContainsString('&amp;', $sql);
    }

    public function testDatabaseDiffMigration(): void
    {
        $repository = $this->createStub(TranslatorRepository::class);
        $repository
            ->method('getKeyValue')
            ->willReturn([
                'quote' => "It's a test",
                'backslash' => 'C:\\path\\',
                'injection' => "\\'; DROP TABLE translator; -- ",
                'a&b' => 'old & value',
                'removed' => "it's removed"
            ]);

        $sql = $this->getSql(new DatabaseDiffMigration($this->entityManager, $this->twig, $repository));

        // unchanged values containing ' or \ must not be detected as updated
        $this->assertStringNotContainsString("`key` = 'quote'", $sql);
        $this->assertStringNotContainsString("`key` = 'backslash'", $sql);
        $this->assertStringNotContainsString("`key` = 'injection'", $sql);

        $this->assertStringContainsString(
            "UPDATE `translator` SET `value` = 'A & B' WHERE `locale` = 'en' AND `domain` = 'messages' AND `key` = 'a&b';",
            $sql
        );
        $this->assertStringContainsString(
            "DELETE FROM `translator` WHERE `locale` = 'en' AND `domain` = 'messages' AND `key` = 'removed';",
            $sql
        );
        $this->assertStringContainsString("('en', 'nested.key', 'value', 'messages', NOW());", $sql);
        $this->assertStringNotContainsString('&amp;', $sql);
    }

    public function testDatabaseDiffMigrationNothingChanged(): void
    {
        $repository = $this->createStub(TranslatorRepository::class);
        $repository
            ->method('getKeyValue')
            ->willReturn([
                'quote' => "It's a test",
                'backslash' => 'C:\\path\\',
                'injection' => "\\'; DROP TABLE translator; -- ",
                'a&b' => 'A & B',
                'nested.key' => 'value'
            ]);

        $sql = $this->getSql(new DatabaseDiffMigration($this->entityManager, $this->twig, $repository));

        $this->assertSame('', $sql);
    }

    private function getSql(BaseMigration $migration): string
    {
        /** @var string $sql */
        $sql = new ReflectionMethod($migration, 'getSql')->invoke($migration, $this->file);

        return $sql;
    }

    /**
     * Emulates MySQL string quoting (PDO::quote with backslash escapes).
     */
    private static function mysqlQuote(string $value): string
    {
        return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
    }
}
