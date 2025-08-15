<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Tests\Helper;

use Danilovl\TranslatorBundle\Exception\RuntimeException;
use Danilovl\TranslatorBundle\Helper\TranslationHelper;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TranslationHelperTest extends TestCase
{
    #[DataProvider('provideGetDomainFromFilenameValidCases')]
    public function testGetDomainFromFilenameValid(string $filename, string $expectedDomain): void
    {
        $this->assertEquals(
            $expectedDomain,
            TranslationHelper::getDomainFromFilename($filename)
        );
    }

    #[DataProvider('provideGetLocaleFromFilenameValidCases')]
    public function testGetLocaleFromFilenameValid(string $filename, string $expectedLocale): void
    {
        $this->assertEquals(
            $expectedLocale,
            TranslationHelper::getLocaleFromFilename($filename)
        );
    }

    #[DataProvider('invalidFilenameProvider')]
    public function testGetDomainFromFilenameInvalidFormat(string $filename): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can not determine domain from filename.');

        TranslationHelper::getDomainFromFilename($filename);
    }

    #[DataProvider('invalidFilenameProvider')]
    public function testGetLocaleFromFilenameInvalidFormat(string $filename): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can not determine locale from filename.');

        TranslationHelper::getLocaleFromFilename($filename);
    }

    public static function provideGetDomainFromFilenameValidCases(): Generator
    {
        yield ['domain.en.json', 'domain'];
        yield ['another_domain.ru.json', 'another_domain'];
        yield ['my_app.de.json', 'my_app'];
    }

    public static function provideGetLocaleFromFilenameValidCases(): Generator
    {
        yield ['domain.en.json', 'en'];
        yield ['another_domain.fr.json', 'fr'];
        yield ['my_app.de.json', 'de'];
    }

    public static function invalidFilenameProvider(): Generator
    {
        yield ['invalidfilename'];
        yield ['missing.parts'];
        yield ['too.many.parts.in.filename'];
    }
}
