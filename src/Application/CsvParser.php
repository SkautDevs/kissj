<?php

declare(strict_types=1);

namespace kissj\Application;

use Iterator;
use League\Csv\Exception as LeagueCsvException;
use League\Csv\Reader;
use Slim\Psr7\UploadedFile;

class CsvParser
{
    /**
     * @throws LeagueCsvException
     */
    public function parseCsv(UploadedFile $file): Iterator
    {
        $stream = $file->getStream();

        $csv = Reader::createFromString($stream->getContents());
        $csv->setHeaderOffset(0);

        return $csv->getRecords();
    }
}
