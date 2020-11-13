<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="persistent_login", indexes={@ORM\Index(name="fk_persistent_login_users1_idx", columns={"id_user"})})
 * @ORM\Entity(repositoryClass="Application\Repository\PersistentLogin")
 *
 */
class PersistentLogin extends AbstractEntity {
	/**
	 * @var integer
	 *
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="token", type="string", length=50, nullable=false)
	 */
	private $token;


	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created_at", type="datetime", nullable=true)
	 */
	private $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="valid_until", type="datetime", nullable=true)
	 */
	private $validUntil;

	/**
	 * @var \Application\Entity\PersistentLogin
	 *
	 * @ORM\OneToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
	 */
	private $user;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getUser() {
		return $this->user;
	}

	public function setUser($user) {
		$this->user = $user;
	}

	public function getToken()
	{
		return $this->token;
	}

	public function setToken($token)
	{
		$this->token = $token;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
	}

	public function getValidUntil()
	{
		return $this->validUntil;
	}

	public function setValidUntil($validUntil)
	{
		$this->validUntil = $validUntil;
	}

	public function isValid() {
		if($this->getValidUntil() > $this->getCreatedAt()) {
			return true;
		}
		return false;
	}
}