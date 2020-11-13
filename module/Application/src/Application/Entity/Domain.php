<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Table(name="domains", indexes={@ORM\Index(name="fk_domains_users1_idx", columns={"id_user"}), @ORM\Index(name="domain", columns={"domain"})})
 * @ORM\Entity
 */
class Domain extends AbstractEntity {
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="string", length=50, nullable=false)
     */
    private $domain;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = '1';

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     * })
     */
    private $user;

	/**
	 * @return string
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * @param string $domain
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;
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
	 * @return boolean
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param boolean $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * @return \Users
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param \Users $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}


}
