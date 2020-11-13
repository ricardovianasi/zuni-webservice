<?php
namespace Application\Filter;

use Zend\Filter\FilterInterface;

class QuerySearch implements FilterInterface {

	private $repository;

	public function __construct($repository=null) {
		if(!empty($repository))
			$this->repository = $repository;
	}

	/* (non-PHPdoc)
	 * @see \Zend\Filter\FilterInterface::filter()
	 */
	public function filter($value) {
		if (!is_string($value)) {
			return $value;
		}
		$value = (string) $value;

		//Remove excesso de espaços em branco
		$value = trim(preg_replace('/ +/',' ', $value));

		//Transforma em array
		$arrayValue = explode(' ', $value);

		//Limpa palavras
		$this->clean($arrayValue);
		return $arrayValue;
	}

	public function clean(&$values=array()) {
		if(empty($values))
			return $values;

		foreach ($values as $v) {
			if($this->getRepository()->findByValue($v)) {
				unset($values[$v]);
			}
		}
		return;
	}

	public function getRepository() {
		return $this->repository;
	}

}