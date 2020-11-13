<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="groups")
 * @ORM\Entity(repositoryClass="Application\Repository\Group")
 */
class Group extends AbstractEntity {

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=45, nullable=false)
	 */
	private $name;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="status", type="boolean", nullable=false)
	 */
	private $status = '1';


	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="string", length=45, nullable=true)
	 */
	private $description;

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return boolean
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param boolean $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

	public function getType() {
		return 'group';
	}
}