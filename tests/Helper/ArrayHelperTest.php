<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Tests\Helper;

use Danilovl\TranslatorBundle\Constant\OrderConstant;
use Danilovl\TranslatorBundle\Helper\ArrayHelper;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArrayHelperTest extends TestCase
{
    #[DataProvider('dataFlattenArray')]
    public function testGetSucceedIgnore(array $data, array $expected): void
    {
        $result = ArrayHelper::flattenArray($data);

        $this->assertSame($expected, $result);
    }

    #[DataProvider('dataDotToNested')]
    public function testDotToNested(array $data, array $expected): void
    {
        $result = ArrayHelper::dotToNested($data);

        $this->assertSame($expected, $result);
    }

    #[DataProvider('dataAddEscape')]
    public function testAddEscape(array $data, array $expected): void
    {
        ArrayHelper::addEscape($data);

        $this->assertSame($expected, $data);
    }

    #[DataProvider('dataGetDiff')]
    public function testGetDiff(array $currentArray, array $previousArray, array $expected): void
    {
        $result = ArrayHelper::getDiff($currentArray, $previousArray);

        $this->assertSame($expected, $result);
    }

    #[DataProvider('dataOrder')]
    public function testOrder(array $currentArray, OrderConstant $order, array $expected): void
    {
        $result = ArrayHelper::sort($currentArray, $order);

        $this->assertSame($expected, $result);
    }

    public static function dataFlattenArray(): Generator
    {
        yield [
            ['a' => 1, 'b' => ['c' => 2]],
            ['a' => 1, 'b.c' => 2]
        ];

        yield [
            ['a' => ['b' => 1, 'c' => ['d' => 2]]],
            ['a.b' => 1, 'a.c.d' => 2]
        ];

        yield [
            ['a' => ['b' => 1], 'c' => ['d' => 2]],
            ['a.b' => 1, 'c.d' => 2]
        ];
    }

    public static function dataDotToNested(): Generator
    {
        yield [
            ['a' => 1, 'b.c' => 2],
            ['a' => 1, 'b' => ['c' => 2]]
        ];

        yield [
            ['a.b' => 1, 'a.c.d' => 2],
            ['a' => ['b' => 1, 'c' => ['d' => 2]]]
        ];

        yield [
            ['a.b' => 1, 'c.d' => 2],
            ['a' => ['b' => 1], 'c' => ['d' => 2]]
        ];
    }

    public static function dataAddEscape(): Generator
    {
        yield [
            [
                'key1' => "It's a test",
                'key2' => "She said, 'Hello'"
            ],
            [
                'key1' => "It\\'s a test",
                'key2' => "She said, \\'Hello\\'"
            ],
        ];
    }

    public static function dataGetDiff(): Generator
    {
        yield [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 1, 'b' => 5, 'd' => 4],
            ['update' => ['b' => 2], 'delete' => ['d' => 4], 'insert' => ['c' => 3]]
        ];

        yield [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['update' => [], 'delete' => [], 'insert' => []]
        ];
    }

    public static function dataOrder(): Generator
    {
        yield [
            ['b' => 2, 'c' => 3, 'a' => 1],
            OrderConstant::ASCENDING,
            ['a' => 1, 'b' => 2, 'c' => 3]
        ];

        yield [
            ['a' => 1, 'b' => 2, 'c' => 3],
            OrderConstant::DESCENDING,
            ['c' => 3, 'b' => 2, 'a' => 1]
        ];
    }
}
