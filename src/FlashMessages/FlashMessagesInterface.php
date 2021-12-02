<?php

namespace kissj\FlashMessages;


interface FlashMessagesInterface {
    public function info(string $message): void;

    public function success(string $message): void;

    public function warning(string $message): void;

    public function error(string $message): void;

    public function dumpMessagesIntoArray(): array;
}
