<?php
namespace Application\Validator\Form;

use Zend\InputFilter\Input;

class Album extends AbstractValidatorForm {
	public function setValidators() {
		$name = new Input('name');
		$name->setRequired(true);
		$this->inputFilter->add($name);

	}
}