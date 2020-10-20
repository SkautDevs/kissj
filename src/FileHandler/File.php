<?php

namespace kissj\FileHandler;

use Psr\Http\Message\StreamInterface;

class File {
    public StreamInterface $stream;
    public string $mimeContentType;

    public function __construct(StreamInterface $stream, string $mimeContentType) {
        $this->stream = $stream;
        $this->mimeContentType = $mimeContentType;
    }
}
