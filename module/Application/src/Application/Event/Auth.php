<?php
namespace Application\Event;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractRestfulController;

class Auth {

	private $e;
	private $resourcesWithoutAuthentication = array(
		'index' => array('index', 'getList'),
		'auth' => array('login', 'logout', 'validateExternalAcess')
	);
	private $resourcesWithoutAuthorization = array(
		'share' => array('getList'),
		'tag' => array('getList', 'get'),
	    'author' => array('getList'),
	    'location' => array('getList'),
	);

	public function preDispatch(MvcEvent $e) {

		$this->e = $e;

		$controller = strtolower($e->getRouteMatch()->getParam('__CONTROLLER__'));
		$action = $e->getRouteMatch()->getParam('action', null);
		if(empty($action)) {
			$action = $this->getMethod();
		}

		if(strtolower($e->getRequest()->getMethod()) == 'options' || strtolower($e->getRequest()->getMethod() == 'optionsList')) {
			$e->getResponse()->setStatusCode(Response::STATUS_CODE_200);
			$model = new JsonModel(array('OK'=>'OK'));
			$e->setViewModel($model);
			$e->stopPropagation(true);
			return;
		}

		//Controllers liberados sem autenticacao
		if($this->testResourcesWithoutAuthentication($controller, $action)) {
			return;
		}

		//Verifica se está autenticado
		if(!$this->getAuthService($e)->isAuthenticated()) {
			$e->getResponse()->setStatusCode(Response::STATUS_CODE_401);
			$model = new JsonModel(array('errors'=>array(
				'httpCode' => $e->getResponse()->getStatusCode(),
				'title' => $e->getResponse()->getReasonPhrase(),
				'message' => 'Login necessário'
			)));
			$e->setViewModel($model);
			$e->stopPropagation(true);
			return $model;
		}

		if($this->testResourcesWithoutAuthorization($controller, $action)) {
			return;
		}

		$action = $this->getMethodFromAction($action);

		//Verifica se tem permissão para acesso ao recurso
		if(!$this->getAuthService($e)->authorize($controller, $action)) {
			$e->getResponse()->setStatusCode(Response::STATUS_CODE_403);
			$model = new JsonModel(array('errors'=>array(
				'httpCode' => $e->getResponse()->getStatusCode(),
				'title' => $e->getResponse()->getReasonPhrase(),
				'message' => 'Usuáio sem acesso ao recurso'
			)));
			$e->setViewModel($model);
			$e->stopPropagation(true);
			return $model;
		}

		return;
	}

	/**
	 * Retorna a action baseado no método HTTP
	 * @return string|NULL
	 */
	protected function getMethod() {
		$routeMatch = $this->getE()->getRouteMatch();
		$method = strtolower($this->getE()->getRequest()->getMethod());
		$id = $routeMatch->getParam('id', false);
		$action = null;

		/* if($method === 'put') {
			if ($id !== false) {
				return 'update';
			} else {
				return 'replaceList';
			}
		} else {
			if($id !== false) {
				return $method;
			} else {
				return $method . 'List';
			}
		} */

		switch ($method) {
			// DELETE
			case 'delete':
				if ($id !== false) {
					$action = 'delete';
					return $action;
					break;
				}

				$action = 'deleteList';
				return $action;
				break;
				// GET
			case 'get':
				if ($id !== false) {
					$action = 'get';
					return $action;
					break;
				}
				$action = 'getList';
				return $action;
				break;
				// HEAD
			case 'options':
				$action = 'options';
				return $action;
				break;
				// POST
			case 'post':
				$action = 'create';
				return $action;
				break;
				// PUT
			case 'put':
				if ($id !== false) {
					$action = 'update';
					return $action;
					break;
				}

				$action = 'replaceList';
				return $action;
				break;
				// All others...
		}
		return null;
	}


	/**
	 * Verifica se um recurso pode ser acessado sem autenticação
	 * @param string $controller
	 * @param string $action
	 * @return boolean
	 */
	protected function testResourcesWithoutAuthentication($controller, $action) {
		if(array_key_exists($controller, $this->resourcesWithoutAuthentication)) {
			if(in_array ($action, $this->resourcesWithoutAuthentication[$controller])) {
				return true;
			}
			return false;
		}
		return false;
	}

	protected function testResourcesWithoutAuthorization($controller, $action) {
		if(array_key_exists($controller, $this->resourcesWithoutAuthorization)) {
			if(in_array ($action, $this->resourcesWithoutAuthorization[$controller])) {
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 *
	 * @param unknown $e
	 * @return \Application\Service\Auth
	 */
	private function getAuthService($e) {
		return $e->getApplication()->getServiceManager()->get('auth');
	}

	/**
	 *
	 * @return MvcEvent
	 */
	public function getE() {
		return $this->e;
	}

	public function getMethodFromAction($action)
	{
		$method  = str_replace(array('.', '-', '_'), ' ', $action);
		$method  = ucwords($method);
		$method  = str_replace(' ', '', $method);
		$method  = lcfirst($method);

		return $method;
	}
}