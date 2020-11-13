<?php
namespace Application\Security;

final class Crypt {
	private static $code;
	private static $n;
	private static $i;
	private static $crypto;
	private static $block;
	private static $text;
	const SALT = 'zuni#ufmg@cedecom?web[2014]';

	private static function randomizar($length)
	{
		self::$code = '';
		while ($length-- > 0) {
			self::$code .= chr(mt_rand() & 0xff);
		}
		return self::$code;
	}

	public static function encode($text, $length = 16, $salt="") {

		if(empty($salt)) {
			$salt = self::SALT;
		}

		$text .= "\x13";
		self::$n = strlen($text);
		if (self::$n % 16) $text .= str_repeat("\0", 16 - (self::$n % 16));
		self::$i = 0;
		self::$crypto = self::randomizar($length);
		self::$code = substr($salt^ self::$crypto, 0, 512);
		while (self::$i < self::$n) {
			self::$block = substr($text, self::$i, 16) ^ pack('H*', md5(self::$code));
			self::$crypto .= self::$block;
			self::$code = substr(self::$block . self::$code, 0, 512) ^ $salt;
			self::$i += 16;
		}
		return strrev(base64_encode(self::$crypto));
	}

	public static function decode($crypto, $salt="", $length = 16) {

		if(empty($salt)) {
			$salt = self::SALT;
		}

		$crypto = base64_decode(strrev($crypto));
		self::$n = strlen($crypto);
		self::$i = $length;
		self::$text = '';
		self::$code = substr($salt^ substr($crypto, 0, $length), 0, 512);
		while (self::$i < self::$n) {
			self::$block = substr($crypto, self::$i, 16);
			self::$text .= self::$block ^ pack('H*', md5(self::$code));
			self::$code = substr(self::$block . self::$code, 0, 512) ^ $salt;
			self::$i += 16;
		}
		return preg_replace('/\\x13\\x00*$/', '', self::$text);
	}

	public static function encryptPassword($str) {
		$bcrypt = new \Zend\Crypt\Password\Bcrypt();
		$bcrypt->setSalt(self::SALT);
		return $bcrypt->create($str);
	}

	public static function testPassword($pass, $hash) {
		$bcrypt = new \Zend\Crypt\Password\Bcrypt();
		return $bcrypt->verify($pass, $hash);
	}

	/**
	 * generates a hash to send via url parameter
	 * @return string
	 */
	public static function generateHashToUrl() {
		return hash('sha1', self::encode(time()));
	}

	/**
	 * Cria um identificador de 32 caracteres(a 128 bit hex number)
	 * @return string
	 */
	public static function generateRandomToken() {
		return md5(uniqid(rand(), true));
	}
}