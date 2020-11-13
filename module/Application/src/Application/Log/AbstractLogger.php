<?php
namespace Application\Log;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

abstract class AbstractLogger extends Logger
{
	/**
	 * Directório onde o log será salvo
	 *
	 * @var string
	 */
	private $_logDir;

	/**
	 * Nome do arquivo de log
	 *
	 * @var string
	 */
	private $_logFile;

	private $serviceManager;

	/**
	 * Construtor
	 *
	 * Define o logDir e logFile e cria o writter. Se o logDir for nulo
	 * irá usar o diretório temporário do sistema
	 *
	 * @param string $logFile
	 * @param string $logDir
	 */
	public function __construct($logFile='zuni.log', $logDir = null, $sm=null)
	{
		parent::__construct();

		/*if(!empty($sm)) {
			$this->serviceManager = $sm;
		}

		if (empty($logDir)) {
			$logDir = realpath(__DIR__ . '\..\..\..\..\..\data\log');
		}

		$this->setLogDir($logDir);

		$this->setLogFile($logFile);

		$writer = new Stream($logDir . DIRECTORY_SEPARATOR . $logFile);
		$this->addWriter($writer);*/
	}

	/**
	 * Retorna o logDir
	 *
	 * @return string
	 */
	public function getLogDir()
	{
		return $this->_logDir;
	}

	/**
	 * Define o logDir
	 *
	 * @param string $logDir
	 * @throws \InvalidArgumentException
	 */
	public function setLogDir($logDir)
	{
		$logDir = trim($logDir);
		if (!file_exists($logDir) || !is_writable($logDir)) {
			throw new \InvalidArgumentException("Diretório $logDir inválido!");
		}

		$this->_logDir = $logDir;
	}
	/**
	 * @return the $_logFile
	 */
	public function getLogFile()
	{
		return $this->_logFile;
	}

	/**
	 * @param string $_logFile
	 */
	public function setLogFile($logFile)
	{
		$logFile = trim($logFile);
		if (null === $logFile || '' == $logFile) {
			throw new \InvalidArgumentException("Arquivo inválido!");
		}
		$this->_logFile = $logFile;
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