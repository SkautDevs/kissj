<?php

namespace kissj\FlashMessages;


interface FlashMessagesInterface {
	public function info($message);
	
	public function success($message);
	
	public function warning($message);
	
	public function error($message);
	
	public function dumpMessagesIntoArray(): array;
}