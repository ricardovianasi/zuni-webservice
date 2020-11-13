<?php
namespace Application\Validator;

/**
 * @author Ricardo
 * Validator que testa se o email está no formato correto e/ou está cadastrado no banco
 *
 */
class Email extends \Zend\Validator\EmailAddress {
	
	const EM_UNDEFINED = 'emailDoctrineEntityManagerInstanceUndefined';
	const ENTITY_NAME_UNDEFINED = 'emailEntityNameUndefined';
	const NOT_EXISTS = 'emailNotExists';
	
	protected $em;
	protected $entityName;
	
	public function __construct($options = array(), $em=null, $entityName=null) {
		if(!empty($em)) {
			if($em instanceof \Doctrine\ORM\EntityManager) {
				$this->em = $em;
			}
			
			if(!empty($entityName)) {
				$this->entityName = $entityName;
			}
		}
		
		$this->messageTemplates[self::EM_UNDEFINED] = "Doctrine Orm Manager is undefined";
		$this->messageTemplates[self::ENTITY_NAME_UNDEFINED] = "Entity name is undefined";
		$this->messageTemplates[self::NOT_EXISTS] = "Este usuário não exitiste";
		
		parent::__construct($options);
	}
	
	public function isValid($value, $validObjectExists=false) {
		
		if(!parent::isValid($value)) {
			return false;
		}
		
		if($validObjectExists) {
			if(empty($this->em)) {
				$this->error(self::EM_UNDEFINED);
				return false;
			}
			
			if(empty($this->entityName)) {
				$this->error(self::ENTITY_NAME_UNDEFINED);
				return false;
			}
			
			$objectExistsValidator = new \DoctrineModule\Validator\ObjectExists(array(
				'object_repository' => $this->em->getRepository($this->entityName),
				'fields' => array('email')
			));
			if(!$objectExistsValidator->isValid($value)) {
				$this->error(self::NOT_EXISTS);
				return false;
			}
		}
		
		return true;
	}
	
	public function setEm(\Doctrine\ORM\EntityManager $em) {
		$this->em = $em;
	}
	
	public function setEntityName($name) {
		$this->entityName = $name;
	}
}