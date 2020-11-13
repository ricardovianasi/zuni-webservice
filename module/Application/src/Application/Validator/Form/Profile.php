<?php
namespace Application\Validator\Form;

use Zend\InputFilter\Input;

class Profile extends AbstractValidatorForm {
	public function setValidators() {
		$name = new Input('name');
		$name->setRequired(true);
		$this->inputFilter->add($name);
		
		$status = new Input('status');
		$status->setRequired(true);
		$this->inputFilter->add($status);
		
	}
}