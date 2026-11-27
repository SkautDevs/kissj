<?php

namespace kissj\FileHandler;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use kissj\Telemetry\Sentry\Collector;
use LogicException;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\UploadedFile;

class S3BucketSaveFileHandler extends SaveFileHandler
{
    public function __construct(
        private readonly S3Client $s3client,
        private readonly string $s3bucket,
        private readonly Collector $sentryCollector,
    ) {
    }

    public function getFile(string $filename): File
    {
        $object = $this->s3client->getObject([
            'Bucket' => $this->s3bucket,
            'Key' => $filename,
        ]);
        /** @var StreamInterface $body */
        $body = $object['Body'];
        /** @var string $contentType */
        $contentType = $object['ContentType'];

        return new File($body, $contentType);
    }

    public function saveFile(UploadedFile $uploadedFile, string $newFilename): UploadedFile
    {
        $this->put(
            $newFilename,
            $uploadedFile->getStream(),
            $uploadedFile->getClientMediaType() ?? throw new LogicException('Uploaded file has no media type'),
        );

        return $uploadedFile;
    }

    public function saveContents(string $contents, string $contentType): string
    {
        $newFilename = $this->getNewFilename();
        $this->put($newFilename, $contents, $contentType);

        return $newFilename;
    }

    public function deleteFile(string $filename): void
    {
        // S3 answers a delete of a missing key with success too
        try {
            $this->s3client->deleteObject([
                'Bucket' => $this->s3bucket,
                'Key' => $filename,
            ]);
        } catch (S3Exception $e) {
            $this->sentryCollector->collect($e);

            throw $e;
        }
    }

    private function put(string $key, StreamInterface|string $body, string $contentType): void
    {
        try {
            $this->s3client->putObject([
                'Bucket' => $this->s3bucket,
                'Key' => $key,
                'Body' => $body,
                'ContentType' => $contentType,
            ]);
        } catch (S3Exception $e) {
            $this->sentryCollector->collect($e);

            throw $e;
        }
    }
}
