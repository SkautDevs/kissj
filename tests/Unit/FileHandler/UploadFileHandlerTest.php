<?php

declare(strict_types=1);

namespace Tests\Unit\FileHandler;

use kissj\FileHandler\UploadFileHandler;
use kissj\FlashMessages\FlashMessagesInterface;
use LogicException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;

class UploadFileHandlerTest extends TestCase
{
    private FlashMessagesInterface&MockInterface $flashMessages;
    private UploadFileHandler $handler;

    protected function setUp(): void
    {
        $this->flashMessages = Mockery::mock(FlashMessagesInterface::class);
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('warning')->byDefault();
        $this->handler = new UploadFileHandler($this->flashMessages, $logger);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testMissingUploadFileKeyReturnsNull(): void
    {
        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns([]);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.fileTooBig');

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertNull($result);
    }

    public function testNonUploadedFileInstanceReturnsNull(): void
    {
        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => 'not-an-uploaded-file']);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.fileTooBig');

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertNull($result);
    }

    public function testSuccessfulUploadReturnsFile(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_OK);
        $uploadedFile->allows('getSize')->andReturns(5_000_000);
        $uploadedFile->allows('getClientMediaType')->andReturns('application/pdf');

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertSame($uploadedFile, $result);
    }

    public function testFileExceeding10MbReturnsNull(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_OK);
        $uploadedFile->allows('getSize')->andReturns(10_000_001);

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.fileTooBig');

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertNull($result);
    }

    public function testFileExactly10MbReturnsFile(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_OK);
        $uploadedFile->allows('getSize')->andReturns(10_000_000);
        $uploadedFile->allows('getClientMediaType')->andReturns('application/pdf');

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertSame($uploadedFile, $result);
    }

    public function testNullFileSizeThrowsException(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_OK);
        $uploadedFile->allows('getSize')->andReturns(null);

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Uploaded file size is null.');

        $this->handler->resolveUploadedFile($request, 'uploadFile');
    }

    public function testUploadErrIniSizeReturnsNull(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_INI_SIZE);

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.fileTooBig');

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertNull($result);
    }

    public function testOtherUploadErrorReturnsNullWithWarning(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_PARTIAL);

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $this->flashMessages->shouldReceive('warning')->once()->with('flash.warning.fileGeneral');

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertNull($result);
    }

    public function testNoFileUploadErrorReturnsNullSilently(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_NO_FILE);

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $this->flashMessages->shouldNotReceive('warning');

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertNull($result);
    }

    public function testDisallowedContentTypeReturnsNull(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_OK);
        $uploadedFile->allows('getSize')->andReturns(5_000_000);
        $uploadedFile->allows('getClientMediaType')->andReturns('application/x-executable');

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.invalidFileType');

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertNull($result);
    }

    public function testNullContentTypeReturnsNull(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_OK);
        $uploadedFile->allows('getSize')->andReturns(5_000_000);
        $uploadedFile->allows('getClientMediaType')->andReturns(null);

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.invalidFileType');

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertNull($result);
    }

    public function testPngContentTypeIsAllowed(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_OK);
        $uploadedFile->allows('getSize')->andReturns(5_000_000);
        $uploadedFile->allows('getClientMediaType')->andReturns('image/png');

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertSame($uploadedFile, $result);
    }

    public function testJpegContentTypeIsAllowed(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_OK);
        $uploadedFile->allows('getSize')->andReturns(5_000_000);
        $uploadedFile->allows('getClientMediaType')->andReturns('image/jpeg');

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['uploadFile' => $uploadedFile]);

        $result = $this->handler->resolveUploadedFile($request, 'uploadFile');

        self::assertSame($uploadedFile, $result);
    }

    public function testResolveByCustomKeyReturnsFile(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_OK);
        $uploadedFile->allows('getSize')->andReturns(5_000_000);
        $uploadedFile->allows('getClientMediaType')->andReturns('application/pdf');

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['parentalConsent' => $uploadedFile]);

        $result = $this->handler->resolveUploadedFile($request, 'parentalConsent');

        self::assertSame($uploadedFile, $result);
    }

    public function testResolveUploadedImageAcceptsRealPngBytesAndRewindsStream(): void
    {
        $stream = $this->streamWith($this->pngBytes());
        $uploadedFile = $this->imageUploadWithStream($stream);

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['image' => $uploadedFile]);

        $result = $this->handler->resolveUploadedImage($request, 'image');

        self::assertSame($uploadedFile, $result);
        self::assertSame(0, $stream->tell());
    }

    public function testResolveUploadedImageRejectsHtmlDisguisedAsPng(): void
    {
        $uploadedFile = $this->imageUploadWithStream($this->streamWith('<html><script>alert(1)</script></html>'));

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['image' => $uploadedFile]);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.invalidFileType');

        $result = $this->handler->resolveUploadedImage($request, 'image');

        self::assertNull($result);
    }

    public function testResolveUploadedImageReturnsNullWhenNoFileWasUploaded(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_NO_FILE);

        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns(['image' => $uploadedFile]);

        $this->flashMessages->shouldNotReceive('warning');

        $result = $this->handler->resolveUploadedImage($request, 'image');

        self::assertNull($result);
    }

    public function testResolveByCustomKeyMissingReturnsNull(): void
    {
        $request = Mockery::mock(Request::class);
        $request->allows('getUploadedFiles')->andReturns([]);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.fileTooBig');

        $result = $this->handler->resolveUploadedFile($request, 'parentalConsent');

        self::assertNull($result);
    }

    private function imageUploadWithStream(Stream $stream): UploadedFile&MockInterface
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->allows('getError')->andReturns(UPLOAD_ERR_OK);
        $uploadedFile->allows('getSize')->andReturns($stream->getSize());
        $uploadedFile->allows('getClientMediaType')->andReturns('image/png');
        $uploadedFile->allows('getStream')->andReturns($stream);

        return $uploadedFile;
    }

    private function streamWith(string $contents): Stream
    {
        $resource = fopen('php://temp', 'rb+');
        if ($resource === false) {
            throw new LogicException('cannot open php://temp');
        }
        fwrite($resource, $contents);
        rewind($resource);

        return new Stream($resource);
    }

    private function pngBytes(): string
    {
        $image = imagecreatetruecolor(1, 1);
        if ($image === false) {
            throw new LogicException('cannot create image');
        }
        ob_start();
        imagepng($image);
        imagedestroy($image);

        return (string)ob_get_clean();
    }
}
