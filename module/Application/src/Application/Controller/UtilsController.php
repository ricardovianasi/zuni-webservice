<?php
namespace Application\Controller;

use Application\Controller\AbstractRestfulJsonController;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\View\Helper\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

class UtilsController extends AbstractRestfulJsonController {

	private $restMethods = array(
		'get',
		'getList',
		'update',
		'create',
		'replaceList',
		'delete',
		'deleteList'
	);

	/**
	 * Metodo responsável por montar a lista de todos os recuros da aplicação para ser usado no controle de permissões
	 */
	public function descobrirMetodosDosControladoresAction() {

		//classes habilitadas
		$config = $this->getServiceLocator()->get('config');
		$controllers = $config['controllers']['invokables'];

		$arrayControllers = array();

		foreach ($controllers as $alias=>$class) {
			$actions = $this->getActtions($class);

			$controller = explode('\\', $alias);
			$controller = strtolower(end($controller));

			//Verifica se o controller já existe
			$controllerEntity = $this->getEntityManager()->getRepository('Application\Entity\Controller')->findOneBy(array('name'=>$controller));
			if(!$controllerEntity) {
				$controllerEntity = new \Application\Entity\Controller();
				$controllerEntity->setName($controller);
			}

			$collActions = new ArrayCollection();
			foreach ($actions as $action) {

				$actionEntity = $this->getRepository('Application\Entity\Action')->findByController($controllerEntity->getId(), $action);
				if($actionEntity) {
					continue;
				} else {
					$actionEntity = new \Application\Entity\Action();
				}

				$actionEntity->setName($action);
				$actionEntity->setController($controllerEntity);
				$controllerEntity->getActions()->add($actionEntity);
			}

			$this->getEntityManager()->persist($controllerEntity);
			$this->getEntityManager()->flush();

			$arrayControllers[] = $controllerEntity;
		}

		$this->getJsonModel()->ok = true;
		$this->getJsonModel()->controllers = $this->arrayLisToJson($arrayControllers);
		return $this->getJsonModel();
	}

	private function getActtions($class) {
		if(!class_exists($class)) {
			return;
		}

		$f = new \ReflectionClass($class);
		$methods = $f->getMethods();
		$actions = array();
		foreach ($methods as $key=>$method) {
			if(($method->class == $class) && $this->validMethod($method)) {
				$actionName = str_replace('Action', '', $method->getName());
				$actions[] = $actionName;
			}
		}

		return $actions;
	}

	private function validMethod($method) {
		//Verifica se o metodo termina em Action
		if(strstr($method->getName(), 'Action')) {
			return true;
		}

		//Verifica se é um rest method
		if(in_array($method->getName(), $this->restMethods)) {
			return true;
		}

		return false;
	}

	public function indexAction() {
		$controllers = $this->getRepository('Application\Entity\Controller')->findAll();
		return new \Zend\View\Model\ViewModel(array(
			'controllers' => $controllers
		));
	}

	public function acaoAction() {
		$idController = $this->params()->fromQuery('idController', null);
		$controller = $this->getRepository('Application\Entity\Controller')->find($idController);
		return new \Zend\View\Model\ViewModel(array(
			'controller' => $controller
		));
	}
}