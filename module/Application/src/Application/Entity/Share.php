<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="share")
 * @ORM\Entity(repositoryClass="Application\Repository\Share")
 */
class Share extends AbstractEntity
{
	/**
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private $status = '1';

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="share_users",
     *      joinColumns={@ORM\JoinColumn(name="id_share", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="id_user", referencedColumnName="id")}
     * 		)
     **/
    private $users;

    /**
     * @var \Album
     *
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Album")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_album", referencedColumnName="id")
     * })
     */
    private $album;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_provider", referencedColumnName="id")
     * })
     */
    private $provider;

    /**
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(name="share_groups",
     *      joinColumns={@ORM\JoinColumn(name="id_share", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="id_group", referencedColumnName="id")}
     * )
     **/
    private $groups;

    /**
     * @ORM\OneToOne(targetEntity="ShareExternal", mappedBy="share")
     */
    private $external;

    public function __construct() {
		$this->users = new ArrayCollection();
		$this->groups = new ArrayCollection();
    }

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return \Album
	 */
	public function getAlbum()
	{
		return $this->album;
	}

	/**
	 * @param \Album $album
	 */
	public function setAlbum($album)
	{
		$this->album = $album;
	}

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
	 * @return \User
	 */
	public function getProvider()
	{
		return $this->provider;
	}

	/**
	 * @param \User $provider
	 */
	public function setProvider($provider)
	{
		$this->provider = $provider;
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
	 * @return \DateTime
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	/**
	 * @param \DateTime $updatedAt
	 */
	public function setUpdatedAt($updatedAt)
	{
		$this->updatedAt = $updatedAt;
	}

	/**
	 * @return \Users
	 */
	public function getUsers()
	{
		return $this->user;
	}

	/**
	 * @param \Users $user
	 */
	public function setUsers($user)
	{
		$this->users = $user;
	}

	public function getGroups()
	{
		return $this->group;
	}

	public function setGroups($group)
	{
		$this->groups = $group;
	}

	/**
	 * @return \Application\Entity\ShareExternal
	 */
	public function getExternal()
	{
		return $this->external;
	}

	public function setExternal($external)
	{
		$this->external = $external;
	}
}