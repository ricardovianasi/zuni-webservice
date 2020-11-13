<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="actions", indexes={@ORM\Index(name="fk_tb_acao_tb_controladores1_idx", columns={"id_controller"})})
 * @ORM\Entity(repositoryClass="Application\Repository\Action")
 */
class Action extends AbstractEntity {
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
	 * @ORM\Column(name="name", type="string", length=50, nullable=false)
	 */
	private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="alias", type="string", length=45, nullable=false)
	 */
	private $alias;

	/**
	 * @ORM\ManyToOne(targetEntity="Application\Entity\Controller", inversedBy="actions", fetch="EAGER")
	 * @ORM\JoinColumn(name="id_controller", referencedColumnName="id")
	 */
	private $controller;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="string", length=50, nullable=false)
	 */
	private $description;

	public function setController($controller) {
		$this->controller = $controller;
	}

	/**
	 * @return \Application\Entity\Controller
	 */
	public function getController() {
		return $this->controller;
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

	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

}