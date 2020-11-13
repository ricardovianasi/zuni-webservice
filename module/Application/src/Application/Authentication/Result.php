<?php
namespace Application\Authentication;

use Application\Security\JWT\JWT;
use Application\Security\Crypt;
class Result extends \Zend\Authentication\Result
{
	protected $token;
	protected $serial;

	public function __construct($code, $identity, array $messages = array()) {
		parent::__construct($code, $identity, $messages);
		$this->createAuthToken();
	}

	public function getToken() {
		return $this->token;
	}

	public function setToken($token) {
		$this->token = $token;
	}

	public function getSerial() {
		return $this->serial;
	}

	public function createAuthToken() {

		$this->serial = Crypt::generateRandomToken();

		$jwt = new JWT();
		$token = $jwt->encode($this->getSerial(), Crypt::SALT);
		$this->setToken($token);
	}
}