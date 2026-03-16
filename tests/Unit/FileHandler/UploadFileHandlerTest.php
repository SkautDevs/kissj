<?php declare(strict_types=1);

namespace Tests\Unit\FileHandler;

use kissj\FileHandler\UploadFileHandler;
use kissj\FlashMessages\FlashMessagesInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
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
}
