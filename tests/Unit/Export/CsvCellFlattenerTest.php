<?php

declare(strict_types=1);

namespace Tests\Unit\Export;

use kissj\Export\CsvCellFlattener;
use League\Csv\Writer;
use PHPUnit\Framework\TestCase;

class CsvCellFlattenerTest extends TestCase
{
    public function testFlattensCrlf(): void
    {
        $flattener = new CsvCellFlattener();

        self::assertSame(['first line second line'], $flattener(["first line\r\nsecond line"]));
    }

    public function testCollapsesBreakRunsAndTrims(): void
    {
        $flattener = new CsvCellFlattener();

        self::assertSame(['line1 line2'], $flattener(["line1\r\n\r\nline2\r\n"]));
    }

    public function testFlattensLoneCrAndLf(): void
    {
        $flattener = new CsvCellFlattener();

        self::assertSame(['a b c'], $flattener(["a\rb\nc"]));
    }

    public function testTrimsAllCells(): void
    {
        $flattener = new CsvCellFlattener();

        self::assertSame(['plain', 'spaced'], $flattener(['plain', ' spaced ']));
    }

    public function testWriterWithFormatterProducesSingleLineRecord(): void
    {
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setDelimiter(',');
        $csv->addFormatter(new CsvCellFlattener());
        $csv->insertOne(["multi\r\n\r\nline", 'plain']);

        // fputcsv encloses fields containing spaces — that quoting is expected and valid
        self::assertSame("\"multi line\",plain\n", $csv->toString());
    }
}
