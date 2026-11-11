<?php

declare(strict_types=1);

namespace Tests\Unit\FileHandler;

use Aws\S3\S3Client;
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
}
