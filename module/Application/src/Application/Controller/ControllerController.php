<?php
namespace Application\Controller;

class ControllerController extends AbstractRestfulJsonController {

	public function getList() {
		$controller = $this->getRepository('Application\Entity\Controller')->findAll();
		if(empty($controller)) {
			$this->getJsonModel()->controllers = array();
		}
		else {
			$this->getJsonModel()->controllers = $this->arrayLisToJson($actions);
		}
		return $this->getJsonModel();
	}
}