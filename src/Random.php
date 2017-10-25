<?php

/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 2017-10-25
 * Time: 21:08
 */
class Random {

	public static function generateToken(): string {
		return md5(random_int(PHP_INT_MIN, PHP_INT_MAX));
	}
}