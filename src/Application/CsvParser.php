<?php

declare(strict_types=1);

namespace kissj\Application;

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
     * @return array<array<string,string>>
     */
    public function parseCsv(UploadedFile $file): array
    {
        /** @var StreamInterface|null $stream */
        $stream = $file->getStream();
        if ($stream === null) {
            throw new UnexpectedValueException('Could not get stream from uploaded file.');
        }

        $csv = Reader::createFromString($stream->getContents());
        $csv->setHeaderOffset(0);

        /** @var array<array<string,string>> $records */
        $records = $csv->getRecords();

        return $records;
    }
}
