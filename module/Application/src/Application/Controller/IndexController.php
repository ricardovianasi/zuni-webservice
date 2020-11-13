<?php
namespace Application\Controller;

use Zend\View\Model\JsonModel;
use Application\Document\Log;
use Doctrine\ODM\MongoDB\Types\TimestampType;
use Doctrine\ODM\MongoDB\Types\DateType;

class IndexController extends AbstractRestfulJsonController {

	public function indexAction() {
		return $this->getList();
	}

	public function getList() {
		$home = $this->url()->fromRoute('home');

		$controllers = $this->getRepository('Application\Entity\Controller')->findAll();
		$metodosEnabled = array();

		foreach ($controllers as $c) {
			$actions = array();
			foreach ($c->getActions() as $a) {
				$actions[] = $a->getName();
			}
			$metodosEnabled[$c->getName()] = $actions;
		}

		return new JsonModel(
			array('Zuni'=> array(
				'welcome'=>'Voce esta conectado ao webserver do sistema Zuni',
				'Methods Enabled' => $metodosEnabled
			))
		);
	}

	public function testeAction() {
		$this->applicationError('isso é um teste');
	}

	public function testeMongoAction() {
		$log = new Log();
		$log->setDate(new \MongoTimestamp());
		$log->setIp('159.164.180.61');
		$log->setMessage('Teste');
		$log->setTarget(Log::TARGET_ALBUM);
		$log->setType(Log::TYPE_UPDATE);
		$log->setUserId(5);

		$this->getDocumentManager()->persist($log);
		$this->getDocumentManager()->flush();

		return new JsonModel(array(
			'log' => $log->toArray()
		));
	}
}