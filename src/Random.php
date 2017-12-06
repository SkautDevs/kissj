<?php

namespace kissj;

class Random {
	public function generateToken(): string {
		return md5(random_int(PHP_INT_MIN, PHP_INT_MAX));
	}
	
	public function generateVariableSymbol(string $prefix): string {
		return $prefix.mt_rand(1000000, 9999999);
	}
}