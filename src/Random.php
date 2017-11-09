<?php

// TODO move into container

namespace kissj;

class Random {

	public static function generateToken(): string {
		return md5(random_int(PHP_INT_MIN, PHP_INT_MAX));
	}
}