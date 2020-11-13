<?php
namespace Application\Authentication\Adapter;

use Application\Authentication\Result;
/**
 *
 * @author Ricardo
 *
 */
class Zuni extends \DoctrineModule\Authentication\Adapter\ObjectRepository {

	//Efetua as validações no perfil do usuário
	protected function validateIdentity($identity) {
		if(method_exists($identity, 'getStatus')) {

			if(!$identity->getStatus()) {
				$this->authenticationResultInfo['code'] = \Zend\Authentication\Result::FAILURE_CREDENTIAL_INVALID;
				$this->authenticationResultInfo['messages'][] = 'O usuário está inativo no sistema';

				return $this->createAuthenticationResult();
			}

		} else {
			throw new \Zend\Authentication\Adapter\Exception\UnexpectedValueException(
					sprintf(
							'Property (%s) in (%s) is not accessible. You should implement %s::%s()',
							get_class($identity),
							get_class($identity),
							'getStatus',
							$this->options->getCredentialProperty()
					)
			);
		}

		return parent::validateIdentity($identity);
	}

	/**
	 * Creates a Zend\Authentication\Result object from the information that has been collected
	 * during the authenticate() attempt.
	 *
	 * @return \Zend\Authentication\Result
	 */
	protected function createAuthenticationResult()
	{
		return new Result(
			$this->authenticationResultInfo['code'],
			$this->authenticationResultInfo['identity'],
			$this->authenticationResultInfo['messages']
		);
	}
}