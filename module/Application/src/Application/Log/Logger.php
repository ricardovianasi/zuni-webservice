<?php
namespace Application\Log;

use Doctrine\Common\EventSubscriber;
use Application\Document\Log;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;

class Logger extends AbstractLogger implements EventSubscriber
{
	private $serviceManager;
	private $blackList = array(
		'Application\Entity\PersistentLogin',
	);

	public function getSubscribedEvents()
	{
		return array('postPersist', 'postUpdate', 'postRemove');
	}

	public function postPersist($event)
	{
		$em = $event->getEntityManager();
		$uow = $em->getUnitOfWork();
		$entity = $event->getEntity();

		if($this->testBlackList(get_class($entity)))
			return;

		$changeSet = $uow->getEntityChangeSet($entity);
		$collUpdate = $uow->getScheduledCollectionUpdates();
		foreach ($collUpdate as $coll) {
			$field = $coll->getMapping()['fieldName'];
			$changeSet[$field] = array(null, $this->prepareCollections($coll));
		}

		//Esconde as senhas
		if(key_exists('password', $changeSet))
			$changeSet['password'][1] = str_pad('', strlen($changeSet['password'][1]), '*');

		$log = new Log();
		$log->setDate(new \MongoTimestamp());
		$log->setTarget($this->identifyResource(get_class($entity)));
		$log->setType(Log::TYPE_CREATE);
		$log->setIp($this->getIpUser());
		$log->setMessage(json_encode($changeSet));
		$log->setUserId($this->getAuthService()->getIdentity()->getId());
		$log->setUser(json_encode($this->getAuthService()->getIdentity()->toArray()));
		$this->insertLogger($log);
	}

	public function postUpdate($event)
	{
		$em = $event->getEntityManager();
		$uow = $em->getUnitOfWork();
		$entity = $event->getEntity();

		if($this->testBlackList(get_class($entity)))
			break;

		$changeSet = $uow->getEntityChangeSet($entity);
		foreach ($changeSet as $key=>$value) {
			if($value instanceof PersistentCollection) {
				$newColl = $this->identifyNewCollection($value->getTypeClass()->getName(), $uow);
				$changeSet[$key] = array($this->prepareCollections($value), $this->prepareCollections($newColl));
			}
		}

		if(key_exists('password', $changeSet)) {
			$changeSet['password'][0] = str_pad('', strlen($changeSet['password'][0]), '*');
			$changeSet['password'][1] = str_pad('', strlen($changeSet['password'][1]), '*');
		}

		$log = new Log();
		if(method_exists($entity, 'getId')) {
			$log->setTargetId($entity->getId());
		}
		$log->setDate(new \MongoTimestamp());
		$log->setTarget($this->identifyResource(get_class($entity)));
		$log->setType(Log::TYPE_UPDATE);
		$log->setIp($this->getIpUser());
		$log->setMessage(json_encode($changeSet, 0, 5));
		$log->setUserId($this->getAuthService()->getIdentity()->getId());
		$log->setUser(json_encode($this->getAuthService()->getIdentity()->toArray()));
		$this->insertLogger($log);
	}

	public function postRemove($event)
	{
		$em = $event->getEntityManager();
		$uow = $em->getUnitOfWork();
		$entity = $event->getEntity();

		if($this->testBlackList(get_class($entity)))
			break;

		$log = new Log();
		if(method_exists($entity, 'getId')) {
			$log->setTargetId($entity->getId());
		}
		$log->setDate(new \MongoTimestamp());
		$log->setTarget($this->identifyResource(get_class($entity)));
		$log->setType(Log::TYPE_REMOVE);
		$log->setIp($this->getIpUser());
		$log->setMessage(json_encode($entity->toArray()));
		$log->setUserId($this->getAuthService()->getIdentity()->getId());
		$log->setUser(json_encode($this->getAuthService()->getIdentity()->toArray()));
		$this->insertLogger($log);
	}

	public function onFlush($event)
	{
		$em = $event->getEntityManager();
		$uow = $em->getUnitOfWork();

		//Insert
		foreach ($uow->getScheduledEntityInsertions() as $entity) {
			//$this->debug('Inserindo entidade ' . get_class($entity) . '. Campos: ' . json_encode($uow->getEntityChangeSet($entity)));

			if($this->testBlackList(get_class($entity)))
				break;


			$log = new Log();
			$log->setDate(new \MongoTimestamp());
			$log->setTarget($this->identifyResource(get_class($entity)));
			$log->setType(Log::TYPE_CREATE);
			$log->setIp($this->getIpUser());
			$log->setMessage(json_encode($uow->getEntityChangeSet($entity)));
			$log->setUserId($this->getAuthService()->getIdentity()->getId());
			$this->insertLogger($log);
		}

		//Update
		foreach ($uow->getScheduledEntityUpdates() as $entity) {

			if($this->testBlackList(get_class($entity)))
				break;

			$log = new Log();
			if(method_exists($entity, 'getId')) {
				$log->setTargetId($entity->getId());
			}
			$log->setDate(new \MongoTimestamp());
			$log->setTarget($this->identifyResource(get_class($entity)));
			$log->setType(Log::TYPE_UPDATE);
			$log->setIp($this->getIpUser());
			$log->setMessage(json_encode($uow->getEntityChangeSet($entity)));
			$log->setUserId($this->getAuthService()->getIdentity()->getId());
			$this->insertLogger($log);
		}

		//Delete
		foreach ($uow->getScheduledEntityDeletions() as $entity) {

			if($this->testBlackList(get_class($entity)))
				break;

			$log = new Log();
			if(method_exists($entity, 'getId')) {
				$log->setTargetId($entity->getId());
			}
			$log->setDate(new \MongoTimestamp());
			$log->setTarget($this->identifyResource(get_class($entity)));
			$log->setType(Log::TYPE_REMOVE);
			$log->setIp($this->getIpUser());
			$log->setMessage(json_encode($uow->getEntityChangeSet($entity)));
			$log->setUserId($this->getAuthService()->getIdentity()->getId());
			$this->insertLogger($log);
		}
	}

	public function testBlackList($entity)
	{
		return in_array($entity, $this->blackList, false);
	}

	public function identifyNewCollection($targetEntity, UnitOfWork $uow) {
		foreach ($uow->getScheduledCollectionUpdates() as $c) {
			if($c->getTypeClass()->getName() === $targetEntity) {
				return $c;
			}
		}
		return null;
	}

	public function prepareCollections($coll) {
		$data = array();
		if(!empty($coll)) {
			foreach ($coll->toArray() as $obj) {
				if(method_exists($obj, 'getId')) {
					$data[] = $obj->getId();
				} else {
					$data[] = serialize($obj);
				}
			}
		}
		return $data;
	}

	private function insertLogger($logDocument) {
		try {
			$this->getDocumentManager()->persist($logDocument);
			$this->getDocumentManager()->flush();
		} catch (\Exception $e) {
			//Prever erro
		}
	}

	public function getIpUser() {
		$remote = new \Zend\Http\PhpEnvironment\RemoteAddress;
		return $remote->getIpAddress();
	}

	public function identifyResource($className) {
		switch ($className) {
			case 'Application\Entity\Image':
				return 'image';
				break;
			case 'Application\Entity\Album':
				return 'album';
				break;
			case 'Application\Entity\User':
				return 'user';
				break;
			case 'Application\Entity\Profile':
				return 'profile';
				break;
			case 'Application\Entity\Group':
				return 'profile';
				break;
			default:
				return $className;
				break;
		}
	}

	public function loginLogger($user) {
		$log = new Log();
		$log->setDate(new \MongoTimestamp());
		$log->setType(Log::TYPE_LOGIN);
		$log->setIp($this->getIpUser());
		$log->setMessage(json_encode($user->toArray()));
		$log->setUserId($user->getId());
		$log->setUser(json_encode($user->toArray()));
		$this->insertLogger($log);
	}

	public function logoutLogger($user) {
		$log = new Log();
		$log->setDate(new \MongoTimestamp());
		$log->setType(Log::TYPE_LOGOUT);
		$log->setIp($this->getIpUser());
		$log->setMessage(json_encode($user->toArray()));
		$log->setUserId($user->getId());
		$log->setUser(json_encode($user->toArray()));
		$this->insertLogger($log);
	}

	public function downloadLogger($image, $user) {
		$log = new Log();
		$log->setDate(new \MongoTimestamp());
		$log->setTargetId($image->getId());
		$log->setType(Log::TYPE_DOWNLOAD);
		$log->setIp($this->getIpUser());
		$log->setMessage(json_encode($image->toArray()));
		$log->setUserId($user->getId());
		$log->setUser(json_encode($user->toArray()));
		$this->insertLogger($log);
	}

	public function getServiceManager() {
		return $this->serviceManager;
	}

	public function setServiceManager($sm) {
		$this->serviceManager = $sm;
	}

	/**
	 * @return \Doctrine\ODM\MongoDB\DocumentManager
	 */
	public function getDocumentManager() {
		return $this->getServiceManager()->get('doctrine.documentmanager.odm_default');
	}

	/**
	 * @return \Application\Service\Auth
	 */
	public function getAuthService() {
		return $this->getServiceManager()->get('auth');
	}
}