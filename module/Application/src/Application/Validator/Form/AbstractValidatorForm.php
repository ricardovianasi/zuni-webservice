<?php
namespace Application\Validator\Form;

use Zend\InputFilter\InputFilter;
use Doctrine\ORM\EntityManager;
abstract class AbstractValidatorForm {
	
	protected $inputFilter;
	protected $em;
	
	public function __construct(EntityManager $em=null) {
		if(!empty($em)) {
			if($em instanceof EntityManager) {
				$this->em = $em;
			}
		}
		
		$this->inputFilter = new InputFilter();
		$this->setValidators();
	}
	
	public function validate($data) {
		$this->inputFilter->setData($data);
		return $this->inputFilter->isValid();
	}
	
	public function getInputFilter() {
		return $this->inputFilter;
	}
	
	abstract function setValidators();
}