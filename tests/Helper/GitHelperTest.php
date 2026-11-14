<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Tests\Helper;

use Danilovl\TranslatorBundle\Helper\GitHelper;
use PHPUnit\Framework\TestCase;

class GitHelperTest extends TestCase
{
    public function testNotExistFileReturnsEmptyArray(): void
    {
        $this->assertSame([], GitHelper::getShowLatestFileContent('not-exist/messages.en.yaml'));
    }

    public function testFilePathIsNotExecutedByShell(): void
    {
        $marker = sys_get_temp_dir() . '/translator-bundle-git-helper-' . uniqid();

        $result = GitHelper::getShowLatestFileContent('messages.en.yaml; touch ' . $marker);

        $this->assertSame([], $result);
        $this->assertFileDoesNotExist($marker);
    }
}
