<?php
namespace Application\Entity;

use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Json\Json;
use Doctrine\Common\Collections\ArrayCollection;

class AbstractEntity {
	public function __construct(array $data=null) {
		if(!empty($data)) {
			$this->setData($data);
		}
	}

	/**
	 * Transforma uma entidade e todas as suas relação em um array
	 * @param number $depth
	 * @return array
	 */
	public function toArray($depth=1, $blackList=array()) {

		$nextDepth = $depth-1;

		$hydrator = new ClassMethods();
		$arrayProp = $hydrator->extract($this);

		if($nextDepth<0) {
			foreach ($arrayProp as $key=>$obj) {
				if(is_object($obj)) {
					$arrayProp[$key] = array();
				}
			}
			return $this->removeBlackListAttributes($arrayProp, $blackList);
		}

		foreach ($arrayProp as $key=>$prop) {
			$newArray = array();

			if(is_object($prop)) {
				if($prop instanceof \Application\Entity\AbstractEntity) {
					$arrayProp[$key] = $prop->toArray($nextDepth);
				} elseif(($prop instanceof \Doctrine\ORM\PersistentCollection) || $prop instanceof ArrayCollection) {
					foreach ($prop as $p) {
						$newArray[] = $p->toArray($nextDepth);
					}
					$arrayProp[$key] = $newArray;
				}
			}
		}

		if(!empty($blackList)) {
			foreach ($blackList as $item) {
				if(key_exists($item, $arrayProp)) {
					$arrayProp[$item] = null;
				}
			}
		}

		return $this->removeBlackListAttributes($arrayProp, $blackList);
	}

	protected function removeBlackListAttributes($arrayClear, $blackList=array()) {
		if(!empty($blackList)) {
			foreach ($blackList as $item) {
				if(key_exists($item, $arrayClear)) {
					unset($arrayClear[$item]);
				}
			}
		}
		return $arrayClear;
	}

	public function toJson() {
		return Json::encode($this->toArray());
	}

	public function setData(array $data) {
		if(!empty($data)) {
			$hydrator = new ClassMethods();
			$hydrator->hydrate($data, $this);
		}
	}

	/**
	 *
	 * @return array
	 */
	protected function _getApplicationConfig() {
		$file = dirname(__DIR__) . '/../../../../config/autoload/global.php';
		if(!is_file($file))
			return null;

		$config = \Zend\Config\Factory::fromFile($file);
		if(empty($config))
			return null;

		if(!key_exists('zuni', $config))
			return null;

		return $config['zuni'];
	}

	/**
	 *
	 * @return \ImageManipulation\Service\ImageFiles
	 */
	protected function _getImageFilesService() {
		return new \ImageManipulation\Service\ImageFiles($this->_getApplicationConfig());
	}

	public function parseData($date, &$property) {
		if($date instanceof \DateTime) {
			$property = $date;
		} elseif (preg_match ('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date)) {
			$property = \DateTime::createFromFormat ('d/m/Y', $date);
		} elseif (preg_match ( '/^\d{4}\-\d{1,2}\-\d{1,2}$/', $date )) {
			$property = new \DateTime($date);
		} else {
			$property = new \DateTime();
		}
	}
}