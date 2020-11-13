<?php
namespace Application\Controller;

class ActionController extends AbstractRestfulJsonController {

	public function getList() {
		$actions = $this->getRepository('Application\Entity\Action')->findAll();
		if(empty($actions)) {
			$this->getJsonModel()->actions = array();
		}
		else {
			$this->getJsonModel()->actions = $this->arrayLisToJson($actions);
		}
		return $this->getJsonModel();
	}
}