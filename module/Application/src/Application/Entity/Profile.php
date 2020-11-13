<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="profiles")
 * @ORM\Entity(repositoryClass="Application\Repository\Profile")
 */
class Profile extends AbstractEntity {

	/**
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=20, nullable=false)
	 */
	private $name;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="status", type="boolean", nullable=false)
	 */
	private $status = '1';

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="external_profile", type="boolean", nullable=false)
	 */
	private $externalProfile = 0;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="Action", fetch="EAGER")
	 * @ORM\JoinTable(name="profiles_actions",
	 * 		joinColumns={@ORM\JoinColumn(name="id_profile", referencedColumnName="id")},
	 * 		inverseJoinColumns={@ORM\JoinColumn(name="id_action", referencedColumnName="id")}
	 * )
	 */
	private $actions;

	/**
	 * @ORM\ManyToMany(targetEntity="User", mappedBy="profiles")
	 */
	private $users;

	private $acl;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="administrator", type="boolean", nullable=false)
	 */
	private $administrator = 0;

	/**
	 * Constructor
	 */
	public function __construct($data=null) {
		$this->actions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->acl = new \Zend\Permissions\Acl\Acl();

		if(!empty($data)) {
			parent::__construct($data);
		}
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

	public function getExternalProfile()
	{
		return $this->externalProfile;
	}

	public function setExternalProfile($externalProfile)
	{
		$this->externalProfile = $externalProfile;
	}

	public function setAdministrator($administrator) {
		$this->administrator = (bool) $administrator;
	}

	public function isAdministrator() {
		return $this->administrator;
	}
}