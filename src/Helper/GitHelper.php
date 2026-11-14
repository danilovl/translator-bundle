<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Helper;

use Symfony\Component\Yaml\Yaml;

class GitHelper
{
    public static function getShowLatestFileContent(string $filePath): array
    {
        $command = sprintf('git show %s 2>/dev/null', escapeshellarg('HEAD:' . $filePath));
        exec($command, $previousContent);

        /** @var array $previousArray */
        $previousArray = Yaml::parse(implode("\n", $previousContent));

        if (empty($previousArray)) {
            return [];
        }

        return ArrayHelper::flattenArray($previousArray);
    }
}
