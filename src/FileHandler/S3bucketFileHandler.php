<?php

namespace kissj\FileHandler;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Utils;
use kissj\Participant\Participant;
use Slim\Psr7\UploadedFile;

class S3bucketFileHandler extends FileHandler {
    public function __construct(private S3Client $s3client, private string $s3bucket)
    {
    }

    public function getFile(string $filename): File {
        $this->s3client->registerStreamWrapper();
        $file = file_get_contents('s3://'.$this->s3bucket.'/'.$filename);
        $stream = Utils::streamFor($file);

        // get content type from S3
        $contentType = $this->s3client->HeadObject([
            'Bucket' => $this->s3bucket,
            'Key' => $filename,
        ]);

        return new File($stream, $contentType['ContentType']);
    }

    public function saveFileTo(Participant $participant, UploadedFile $uploadedFile): Participant {
        $newFilename = $this->getNewFilename();

        try {
            $this->s3client->putObject([
                'Bucket' => $this->s3bucket,
                'Key' => $newFilename,
                'Body' => $uploadedFile->getStream(),
                'ContentType' => $uploadedFile->getClientMediaType(),
            ]);
        } catch (S3Exception) {
            // TODO add log
        }

        $participant->uploadedFilename = $newFilename;
        $participant->uploadedOriginalFilename = $uploadedFile->getClientFilename();
        $participant->uploadedContenttype = $uploadedFile->getClientMediaType();

        return $participant;
    }
}
