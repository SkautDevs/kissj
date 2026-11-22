<?php

declare(strict_types=1);

namespace Tests\Unit\FileHandler;

use kissj\FileHandler\LocalSaveFileHandler;
use kissj\Programme\Image\ProgrammeImageProcessor;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\ImageFixtures;

class LocalSaveFileHandlerTest extends TestCase
{
    private string $uploadFolder;

    protected function setUp(): void
    {
        $this->uploadFolder = sys_get_temp_dir() . '/kissj-uploads-' . uniqid() . '/';
        mkdir($this->uploadFolder);
    }

    protected function tearDown(): void
    {
        $files = glob($this->uploadFolder . '*');
        foreach ($files === false ? [] : $files as $file) {
            unlink($file);
        }
        rmdir($this->uploadFolder);
    }

    public function testSaveContentsWritesUnderANew32HexName(): void
    {
        $webp = (new ProgrammeImageProcessor(true))->process(ImageFixtures::png(40, 30))->full->bytes;
        $handler = new LocalSaveFileHandler($this->uploadFolder);

        $filename = $handler->saveContents($webp, 'image/webp');

        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/D', $filename);
        $file = $handler->getFile($filename);
        self::assertSame($webp, (string)$file->stream);
        self::assertSame('image/webp', $file->mimeContentType);
    }

    public function testDeleteFileRemovesItAndIgnoresMissingOnes(): void
    {
        $handler = new LocalSaveFileHandler($this->uploadFolder);
        $filename = $handler->saveContents('bytes', 'text/plain');

        $handler->deleteFile($filename);
        $handler->deleteFile($filename);
        $handler->deleteFile(str_repeat('0', 32));

        self::assertFileDoesNotExist($this->uploadFolder . $filename);
    }

    public function testDeleteFileThrowsWhenThePathStaysAfterUnlink(): void
    {
        $handler = new LocalSaveFileHandler($this->uploadFolder);
        $directoryName = str_repeat('a', 32);
        mkdir($this->uploadFolder . $directoryName);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Cannot delete file: ' . $directoryName);

            $handler->deleteFile($directoryName);
        } finally {
            rmdir($this->uploadFolder . $directoryName);
        }
    }
}
