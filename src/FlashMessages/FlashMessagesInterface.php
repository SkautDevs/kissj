<?php

namespace kissj\FlashMessages;


interface FlashMessagesInterface {
    public function info($message): void;

    public function success($message): void;

    public function warning($message): void;

    public function error($message): void;

    public function dumpMessagesIntoArray(): array;
}
