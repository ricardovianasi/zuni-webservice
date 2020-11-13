<?php
namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Application\Entity\AbstractEntity;

/**
 * @ODM\Document(repositoryClass="Application\DocumentRepository\Log")
 *
 */
class Log extends AbstractEntity
{
	//
	const TYPE_CREATE = 'create';
	const TYPE_UPDATE = 'update';
	const TYPE_REMOVE = 'remove';
	const TYPE_LOGIN = 'login';
	const TYPE_LOGOUT = 'logout';
	const TYPE_DOWNLOAD = 'download';

	const TARGET_ALBUM = 'album';
	const TARGET_IMAGE = 'image';
	const TARGET_USER = 'user';
	const TARGET_PROFILE = 'profile';
	const TARGET_GROUP = 'group';

	/**
	 * @ODM\Id
	 */
	private $id;

	/**
	 * @ODM\String
	 */
	private $message;

	/**
	 * @ODM\String
	 */
	private $type;

	/**
	 * @ODM\String
	 */
	private $target;

	/**
	 * @ODM\Int
	 */
	private $targetId;

	/**
	 * @ODM\Int
	 */
	private $userId;

	/**
	 * @ODM\String
	 */
	private $user;

	/**
	 * @ODM\Timestamp
	 */
	private $date;

	/**
	 * @ODM\String
	 */
	private $ip;

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function setMessage($message)
	{
		$this->message = $message;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function getTarget()
	{
		return $this->target;
	}

	public function setTarget($target)
	{
		$this->target = $target;
	}

	public function getTargetId()
	{
		return $this->targetId;
	}

	public function setTargetId($targetId)
	{
		$this->targetId = $targetId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setUser($user)
	{
		$this->user = $user;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function setDate($date)
	{
		$this->date = $date;
	}

	public function getIp()
	{
		return $this->ip;
	}

	public function setIp($ip)
	{
		$this->ip = $ip;
	}
}