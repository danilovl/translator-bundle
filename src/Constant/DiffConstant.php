<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Constant;

enum DiffConstant: string
{
    case INSERT = 'insert';
    case DELETE = 'delete';
    case UPDATE = 'update';
}
