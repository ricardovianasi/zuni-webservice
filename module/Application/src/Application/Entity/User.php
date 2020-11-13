<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Application\Repository\User")
 */
class User extends AbstractEntity {
	/**
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="email", type="string", length=60, nullable=false)
	 */
	private $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=100, nullable=false)
	 */
	private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="password", type="string", length=150, nullable=false)
	 */
	private $password;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="phone", type="string", length=150, nullable=true)
	 */
	private $phone;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="status", type="boolean", nullable=false)
	 */
	private $status = '0';

	/**
	 * @var string
	 *
	 * @ORM\Column(name="origin", type="string", length=255, nullable=false)
	 */
	private $origin;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
	 */
	private $avatar;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="expires_at", type="datetime", nullable=true)
	 */
	private $expiresAt;

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
	 * @var string
	 *
	 * @ORM\Column(name="access_key", type="string", length=128, nullable=true)
	 */
	private $accessKey;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="Application\Entity\Profile", cascade={"persist"})
	 * @ORM\JoinTable(name="users_profiles",
	 *   	joinColumns={@ORM\JoinColumn(name="id_user", referencedColumnName="id")},
	 *   	inverseJoinColumns={@ORM\JoinColumn(name="id_profile", referencedColumnName="id")}
	 * )
	 */
	private $profiles;

	/**
	 * @ORM\ManyToMany(targetEntity="Application\Entity\Group", cascade={"persist"})
	 * @ORM\JoinTable(name="users_groups",
	 * 		joinColumns={@ORM\JoinColumn(name="id_user", referencedColumnName="id")},
	 * 		inverseJoinColumns={@ORM\JoinColumn(name="id_group", referencedColumnName="id", unique=true)}
	 * )
	 **/
	private $groups;

	/**
	 * @var \ArrayCollection
	 * @ORM\OneToMany(targetEntity="Hash", mappedBy="hash")
	 */
	private $hashes;

	private $external;

	/**
	 * Constructor
	 */
	public function __construct($data=null) {
		$this->profiles = new \Doctrine\Common\Collections\ArrayCollection();
		$this->groups = new \Doctrine\Common\Collections\ArrayCollection();
		$this->hashes = new \Doctrine\Common\Collections\ArrayCollection();

		if(!empty($data)) {
			parent::__construct($data);
		}
	}

	/**
	 * @param string $accessKey
	 */
	public function setAccessKey($accessKey) {
		$this->accessKey = $accessKey;
	}

	/**
	 * @return string
	 */
	public function getAccessKey() {
		return $this->accessKey;
	}

	/**
	 * @param \DateTime $createdAt
	 */
	public function setCreatedAt($createdAt) {
		$this->createdAt = $createdAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt() {
		if($this->createdAt instanceof \DateTime) {
			return $this->createdAt->format('d-m-Y');
		}
		return $this->createdAt;
	}

	/**
	 * @param \DateTime $expiresAt
	 */
	public function setExpiresAt($expiresAt) {
		$this->expiresAt = $expiresAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getExpiresAt() {
		return $this->expiresAt;
	}

	public function getFormattedExpiresAt() {
		if($this->expiresAt instanceof \DateTime) {
			return $this->expiresAt->format('d-m-Y');
		}
		return $this->expiresAt;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $origin
	 */
	public function setOrigin($origin) {
		$this->origin = $origin;
	}

	/**
	 * @return string
	 */
	public function getOrigin() {
		return $this->origin;
	}

	/**
	 * @param string $avatar
	 */
	public function setAvatar($avatar) {
		$this->avatar = $avatar;
	}

	/**
	 * @return string
	 */
	public function getAvatar() {
		return $this->avatar;
	}

	/**
	 * @return string
	 */
	public function getAvatarUrl() {
		if(!empty($this->getAvatar()))
			return $this->_getImageFilesService()->getThumbnailUrlImage($this->avatar);
		else
			return null;
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $profiles
	 */
	public function setProfiles($profiles) {
		$this->profiles = $profiles;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getProfiles() {
		return $this->profiles;
	}

	/**
	 * @param boolean $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * @return boolean
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param \DateTime $updatedAt
	 */
	public function setUpdatedAt($updatedAt) {
		$this->updatedAt = $updatedAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getUpdatedAt() {
		if($this->updatedAt instanceof \DateTime) {
			return $this->updatedAt->format('d/m/Y');
		}
		return $this->updatedAt;
	}

	public function getGroups() {
		return $this->groups;
	}

	public function setGroups($groups) {
		$this->groups = $groups;
	}

	/* public function getPersistentLogin() {
		return $this->persistentLogin;
	}

	public function setPersistentLogin($persistentLogin) {
		$this->persistentLogin = $persistentLogin;
	} */

	public function getPhone()
	{
		return $this->phone;
	}

	public function setPhone($phone)
	{
		$this->phone = $phone;
	}

	public function setExternal($var) {
		$this->external = $var;
	}

	public function isExternal() {
		return $this->external;
	}

	public function hasAdministratorProfile() {
		foreach ($this->getProfiles() as $p) {
			if($p->isAdministrator()) {
				return true;
			}
		}
		return false;
	}

	public function toArray($depth=1, $blackList=array('has_administrator_profile', 'list_permissions', 'access_key', 'created_at', 'expires_at', 'profiles', 'groups', 'is_external', 'formatted_expires_at')) {
		$black = array_merge($blackList, array('password'));
		return parent::toArray($depth, $black);
	}

	public function getListPermissions() {
		$permissions = array();
		foreach ($this->getProfiles() as $p) {
			//$p = new Profile();
			foreach ($p->getActions() as $a) {
				//$a = new Action();
				//$permissions[$a->getController()->getName()] = $a->getName();
				if(!key_exists($a->getController()->getName(), $permissions))
					$permissions[$a->getController()->getName()] = array();

				if(!in_array($a->getName(), $permissions[$a->getController()->getName()])) {
					$permissions[$a->getController()->getName()][] = array('value'=>$a->getName(), 'alias'=>$a->getAlias());
				}
			}
		}

		return $permissions;
	}

	public function getType() {
		return 'user';
	}
}