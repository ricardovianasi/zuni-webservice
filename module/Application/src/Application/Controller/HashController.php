<?php
namespace Application\Controller;

class HashController extends AbstractRestfulJsonController {
	
	public function validateAction() {
		$hash = $this->params('hash', null);
		if(!$this->getRepository('Application\Entity\Hash')->isValid($hash)) {
			$this->getJsonModel()->hash = array(
					'error' => true,
					'message' => 'A hash não é válida'
			);
			return $this->getJsonModel();
		}
		
		$this->getJsonModel()->hash = true;
		return $this->getJsonModel();
	}
}