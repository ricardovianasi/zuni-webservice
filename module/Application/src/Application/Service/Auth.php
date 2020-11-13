<?php
namespace Application\Service;

use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Http\Header\SetCookie;
use Zend\Json\Json;
use Application\Security\Crypt;
use Application\Entity\User;
use Application\Security\JWT\JWT;

class Auth extends AuthenticationService implements ServiceManagerAwareInterface {

	private $serviceManager;
	private $jsonModelReturn;

	/**
	 * Verifica se o usuário está logado e tem acesso ao recurso solicitado
	 * @param string $controller
	 * @param string $action
	 * @return bool
	 */
	public function authorize($controller, $action='index') {

		$idProfile = null;
		$idUser = null;

		//Se for perfil de administrador, tem acesso a tudo
		if($this->getIdentity()->hasAdministratorProfile()) {
			return true;
		}

		if($this->getIdentity()->getId()) {
			$idUser = $this->getIdentity()->getId();
		} elseif($this->getIdentity()->getProfiles()->getExternalProfile()) {
			$idProfile = $this->getIdentity()->getProfiles()->getId();
		}

		return $this->getEntityManager()->getRepository('Application\Entity\Profile')->has($controller, $action, $idUser, $idProfile);
	}

	/**
	 * Verifica se uma hash de acesso externo é válida
	 * @param string $hash
	 * @return boolean
	 */
	public function authenticateExternalAccess($hash) {
		if(!$share = $this->getEntityManager()->getRepository('Application\Entity\Share')->validateHashExternalAcess($hash)) {
			return false;
		}

		$this->generateLoginForExternalAccess($share);
		$this->getIdentity();

		return true;
	}

	protected function generateLoginForExternalAccess(\Application\Entity\Share $shareData) {
		$login = new User();
		$login->setName($shareData->getExternal()->getDestination());
		$login->setProfiles($this->getEntityManager()->getRepository('Application\Entity\Profile')->findExternalProfile());
		$login->setAccessKey($shareData->getExternal()->getHashAcess());
		$login->setExternal(true);
		$this->getStorage()->write($login);
	}

	public function logout() {
		$this->clearIdentity();
		$this->deleteAuthToken();
	}

	public function deleteAuthToken() {
		if($this->hasIdentity())
			$this->getEntityManager()->getRepository('Application\Entity\PersistentLogin')->deleteByUser($this->getIdentity()->getId());
	}

	public function setServiceManager(ServiceManager $serviceManager) {
		$this->serviceManager = $serviceManager;
	}

	public function getServiceManager() {
		return $this->serviceManager;
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager() {
		return $this->getServiceManager()->get('Doctrine\ORM\EntityManager');
	}

	public function createAuthCookie() {

		$this->clearAuthCookie();

		$cookieEntity = $this->getEntityManager()->getRepository('Application\Entity\PersistentLogin')->create($this->getIdentity());
		$dados = array(
			'token' => $cookieEntity->getToken(),
			'serial_number' => $cookieEntity->getSerialIdentifier(),
			'user_id' => $cookieEntity->getUser()->getId()
		);

		$cookie = new SetCookie('zuni', Json::encode($dados), time() + 365 * 60 * 60 * 24, '/');
		$response = $this->getServiceManager()->get('Response');
		$response->getHeaders()->addHeader($cookie);
	}

	public function clearAuthCookie() {
		$cookie = new SetCookie('zuni', null, 0, '/');
		$cookie2 = new SetCookie('zuni2', null, 0, '/');
		$response = $this->getServiceManager()->get('Response');
		$response->getHeaders()->addHeader($cookie);
		$response->getHeaders()->addHeader($cookie2);
	}

	public function updateTokenAuthCookie($cookieEntity) {
		$cookieEntity->setToken(Crypt::generateRandomToken());
		$dados = array(
			'token' => $cookieEntity->getToken(),
			'serial_number' => $cookieEntity->getSerialIdentifier(),
			'user_id' => $cookieEntity->getUser()->getId()
		);
		$this->getEntityManager()->getRepository('Application\Entity\PersistentLogin')->update($cookieEntity);

		$cookie = new SetCookie('zuni', Json::encode($dados), time() + 365 * 60 * 60 * 24, '/');
		$response = $this->getServiceManager()->get('Response');
		$response->getHeaders()->addHeader($cookie);
	}

	public function isAuthenticated() {
		try {
			if($tokenAuthHeader = $this->getRequest()->getHeader('authorization', null)) {
				$decodeToken = JWT::decode($tokenAuthHeader->getFieldValue(), Crypt::SALT);
				$entityPersistentLogin = $this->getEntityManager()->getRepository('Application\Entity\PersistentLogin')->validate($decodeToken);

				if(!$entityPersistentLogin)
					return false;

				if(!$entityPersistentLogin->isValid())
					return false;

				if($entityPersistentLogin->getUser()) {
					$this->getStorage()->write($entityPersistentLogin->getUser());
					return true;
				}

				return false;

			} elseif($this->hasIdentity()) {
				//return false;
			}
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	/**
	 * Retorna um usuario válido no sistema
	 * @see \Zend\Authentication\AuthenticationService::getIdentity()
	 * TODO: Método foi sobrescrito para ser usado como teste. Remover para fazer testes de login ou quando o sistema estiver em produção
	 * @return \Application\Entity\User
	 */
	/* public function getIdentity() {
		$user = $this->getEntityManager()->getRepository('Application\Entity\User')->find(5);
		return $user;
	} */

	/**
	 *
	 * @return \Zend\Http\Request
	 */
	public function getRequest() {
		return $request = $this->getServiceManager()->get('Request');;
	}
}