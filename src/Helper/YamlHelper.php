<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Helper;

use Symfony\Component\Yaml\Yaml;

class YamlHelper
{
    public static function defaultDump(mixed $input): string
    {
        return Yaml::dump($input, 4, 2);
    }
}
