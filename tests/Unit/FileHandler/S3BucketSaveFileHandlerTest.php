<?php

declare(strict_types=1);

namespace Tests\Unit\FileHandler;

use Aws\Result;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Utils;
use kissj\FileHandler\S3BucketSaveFileHandler;
use kissj\Telemetry\Sentry\Collector;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Sentry\State\Hub;
use Slim\Psr7\UploadedFile;

class S3BucketSaveFileHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSaveFileWithoutMediaTypeThrowsAndDoesNotUpload(): void
    {
        $s3client = Mockery::mock(S3Client::class);
        $s3client->shouldNotReceive('putObject');
        $handler = new S3BucketSaveFileHandler($s3client, 'bucket', new Collector(Mockery::mock(Hub::class)));

        $this->expectException(LogicException::class);

        $handler->saveFile(new UploadedFile('php://memory', 'file.png', null, 0), 'new-file.png');
    }

    public function testSaveContentsPutsTheBytesWithTheContentType(): void
    {
        $putKey = null;
        $s3client = Mockery::mock(S3Client::class);
        $s3client->shouldReceive('putObject')
            ->once()
            ->with(Mockery::on(static function (array $arguments) use (&$putKey): bool {
                $putKey = $arguments['Key'];

                return $arguments['Bucket'] === 'bucket'
                    && $arguments['Body'] === 'webp bytes'
                    && $arguments['ContentType'] === 'image/webp';
            }));
        $handler = new S3BucketSaveFileHandler($s3client, 'bucket', new Collector(Mockery::mock(Hub::class)));

        $filename = $handler->saveContents('webp bytes', 'image/webp');

        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/D', $filename);
        self::assertSame($filename, $putKey);
    }

    public function testGetFileReadsTheObjectWithOneStorageCall(): void
    {
        $s3client = Mockery::mock(S3Client::class);
        $s3client->shouldReceive('getObject')
            ->once()
            ->with(['Bucket' => 'bucket', 'Key' => 'abc'])
            ->andReturn(new Result(['Body' => Utils::streamFor('webp bytes'), 'ContentType' => 'image/webp']));
        $s3client->shouldNotReceive('HeadObject');
        $s3client->shouldNotReceive('registerStreamWrapper');
        $handler = new S3BucketSaveFileHandler($s3client, 'bucket', new Collector(Mockery::mock(Hub::class)));

        $file = $handler->getFile('abc');

        self::assertSame('webp bytes', $file->stream->getContents());
        self::assertSame('image/webp', $file->mimeContentType);
    }

    public function testDeleteFileDeletesTheObject(): void
    {
        $s3client = Mockery::mock(S3Client::class);
        $s3client->shouldReceive('deleteObject')
            ->once()
            ->with(['Bucket' => 'bucket', 'Key' => 'abc']);
        $handler = new S3BucketSaveFileHandler($s3client, 'bucket', new Collector(Mockery::mock(Hub::class)));

        $handler->deleteFile('abc');
    }
}
