<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="hashes")
 * @ORM\Entity(repositoryClass="Application\Repository\Hash")
 */
class Hash extends AbstractEntity {
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="hash", type="string")
     */
    private $hash;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="hashes", fetch="EAGER")
	 * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
	 *
	 */
	private $user;

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
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @param \DateTime $createdAt
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param mixed $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return \DateTime
	 */
	public function getValidUntil()
	{
		return $this->validUntil;
	}

	/**
	 * @param \DateTime $validUntil
	 */
	public function setValidUntil($validUntil)
	{
		if($validUntil instanceof \DateTime) {
			$this->validUntil = $validUntil;
		} elseif (preg_match ('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $validUntil)) {
			$this->validUntil = \DateTime::createFromFormat ('d/m/Y', $validUntil);
		} elseif (preg_match ( '/^\d{4}\-\d{1,2}\-\d{1,2}$/', $validUntil )) {
			$this->validUntil = new \DateTime($validUntil);
		} else {
			$this->validUntil = $validUntil;
		}
	}

	public function getHash() {
		return $this->hash;
	}

	public function setHash($hash) {
		$this->hash = $hash;
	}
}
