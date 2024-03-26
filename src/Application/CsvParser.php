<?php

declare(strict_types=1);

namespace kissj\Application;

use Iterator;
use League\Csv\Exception as LeagueCsvException;
use League\Csv\Reader;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\UploadedFile;
use UnexpectedValueException;

class CsvParser
{
    /**
     * @throws UnexpectedValueException
     * @throws LeagueCsvException
     */
    public function parseCsv(UploadedFile $file): Iterator
    {
        /** @var StreamInterface|null $stream */
        $stream = $file->getStream();
        if ($stream === null) {
            throw new UnexpectedValueException('Could not get stream from uploaded file.');
        }

        $csv = Reader::createFromString($stream->getContents());
        $csv->setHeaderOffset(0);

        return $csv->getRecords();
    }
}
