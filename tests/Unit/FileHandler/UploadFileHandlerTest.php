<?php declare(strict_types=1);

namespace Tests\Unit\FileHandler;

use kissj\FileHandler\UploadFileHandler;
use kissj\FlashMessages\FlashMessagesInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

class UploadFileHandlerTest extends TestCase
{
    private FlashMessagesInterface&MockInterface $flashMessages;
    private UploadFileHandler $handler;

    protected function setUp(): void
    {
        $this->flashMessages = Mockery::mock(FlashMessagesInterface::class);
        $this->handler = new UploadFileHandler($this->flashMessages);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testMissingUploadFileKeyReturnsNull(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getUploadedFiles')->andReturn([]);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.fileTooBig');

        $result = $this->handler->resolveUploadedFile($request);

        self::assertNull($result);
    }

    public function testNonUploadedFileInstanceReturnsNull(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getUploadedFiles')->andReturn(['uploadFile' => 'not-an-uploaded-file']);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.fileTooBig');

        $result = $this->handler->resolveUploadedFile($request);

        self::assertNull($result);
    }

    public function testSuccessfulUploadReturnsFile(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('getError')->andReturn(UPLOAD_ERR_OK);
        $uploadedFile->shouldReceive('getSize')->andReturn(5_000_000);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getUploadedFiles')->andReturn(['uploadFile' => $uploadedFile]);

        $result = $this->handler->resolveUploadedFile($request);

        self::assertSame($uploadedFile, $result);
    }

    public function testFileExceeding10MbReturnsNull(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('getError')->andReturn(UPLOAD_ERR_OK);
        $uploadedFile->shouldReceive('getSize')->andReturn(10_000_001);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getUploadedFiles')->andReturn(['uploadFile' => $uploadedFile]);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.fileTooBig');

        $result = $this->handler->resolveUploadedFile($request);

        self::assertNull($result);
    }

    public function testFileExactly10MbReturnsFile(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('getError')->andReturn(UPLOAD_ERR_OK);
        $uploadedFile->shouldReceive('getSize')->andReturn(10_000_000);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getUploadedFiles')->andReturn(['uploadFile' => $uploadedFile]);

        $result = $this->handler->resolveUploadedFile($request);

        self::assertSame($uploadedFile, $result);
    }

    public function testNullFileSizeThrowsException(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('getError')->andReturn(UPLOAD_ERR_OK);
        $uploadedFile->shouldReceive('getSize')->andReturn(null);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getUploadedFiles')->andReturn(['uploadFile' => $uploadedFile]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Uploaded file size is null.');

        $this->handler->resolveUploadedFile($request);
    }

    public function testUploadErrIniSizeReturnsNull(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('getError')->andReturn(UPLOAD_ERR_INI_SIZE);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getUploadedFiles')->andReturn(['uploadFile' => $uploadedFile]);

        $this->flashMessages->shouldReceive('warning')
            ->once()
            ->with('flash.warning.fileTooBig');

        $result = $this->handler->resolveUploadedFile($request);

        self::assertNull($result);
    }

    public function testOtherUploadErrorReturnsNull(): void
    {
        $uploadedFile = Mockery::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('getError')->andReturn(UPLOAD_ERR_PARTIAL);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getUploadedFiles')->andReturn(['uploadFile' => $uploadedFile]);

        $this->flashMessages->shouldNotReceive('warning');

        $result = $this->handler->resolveUploadedFile($request);

        self::assertNull($result);
    }
}
