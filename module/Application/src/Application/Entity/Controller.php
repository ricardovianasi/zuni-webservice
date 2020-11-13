<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="controllers")
 * @ORM\Entity(repositoryClass="Application\Repository\Controller")
 */
class Controller extends AbstractEntity {
	/**
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=50, nullable=false)
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
	 * @ORM\Column(name="alias", type="string", length=45, nullable=false)
	 */
	private $alias;

	/**
	 * @ORM\OneToMany(targetEntity="Application\Entity\Action", mappedBy="controller", cascade={"persist"})
	 */
	private $actions;

	public function __construct(array $data=null) {
		$this->actions = new ArrayCollection();

		parent::__construct($data);
	}

	public function setActions($actions) {
		$this->actions = $actions;
	}

	public function getActions() {
		return $this->actions;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setStatus($status) {
		$this->status = $status;
	}

	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return the $alias
	 */
	public function getAlias() {
		return $this->alias;
	}

	/**
	 * @param string $alias
	 */
	public function setAlias($alias) {
		$this->alias = $alias;
	}
}