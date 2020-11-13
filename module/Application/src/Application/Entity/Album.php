<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="albums")
 * @ORM\Entity(repositoryClass="Application\Repository\Album")
 * @ORM\HasLifecycleCallbacks()
 */
class Album extends AbstractEntity {
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
	 * @ORM\Column(name="name", type="string", length=150, nullable=false)
	 */
	private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="text", nullable=true)
	 */
	private $description;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="visibility", type="string", nullable=false)
	 */
	private $visibility = \Application\Entity\AlbumVisibility::STATUS_PUBLIC;

	/**
	 * @var \Users
	 *
	 * @ORM\ManyToOne(targetEntity="Application\Entity\User")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_user", referencedColumnName="id")
	 * })
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
	 * @ORM\Column(name="updated_at", type="datetime", nullable=true)
	 */
	private $updatedAt;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="Tag", cascade={"persist"} )
	 * @ORM\JoinTable(name="albums_tags",
	 *   joinColumns={
	 *     @ORM\JoinColumn(name="id_album", referencedColumnName="id")
	 *   },
	 *   inverseJoinColumns={
	 *     @ORM\JoinColumn(name="id_tag", referencedColumnName="id")
	 *   }
	 * )
	 */
	private $tags;

	/**
	 * @ORM\ManyToOne(targetEntity="Application\Entity\Location", cascade={"persist"})
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_location", referencedColumnName="id")
	 * })
	 */
	private $location;

	private $cover;

	private $imageCount;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="Application\Entity\User", cascade={"persist"})
	 * @ORM\JoinTable(name="albums_share_users",
	 *   	joinColumns={@ORM\JoinColumn(name="id_album", referencedColumnName="id")},
	 *   	inverseJoinColumns={@ORM\JoinColumn(name="id_user", referencedColumnName="id")}
	 * )
	 */
	private $shareUsers;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="Application\Entity\Group", cascade={"persist"})
	 * @ORM\JoinTable(name="albums_share_groups",
	 *   	joinColumns={@ORM\JoinColumn(name="id_album", referencedColumnName="id")},
	 *   	inverseJoinColumns={@ORM\JoinColumn(name="id_group", referencedColumnName="id")}
	 * )
	 */
	private $shareGroups;

// 	private $share;

	/**
	 * Constructor
	 */
	public function __construct(array $data = null)
	{
		$this->tags = new \Doctrine\Common\Collections\ArrayCollection();
		$this->shareGroups = new \Doctrine\Common\Collections\ArrayCollection();
		$this->shareUsers = new \Doctrine\Common\Collections\ArrayCollection();

		parent::__construct($data);
	}

	public function getShare() {
		$users = $this->shareUsers->toArray();
		$groups = $this->shareGroups->toArray();
		$teste = array_merge($users, $groups);

		return new ArrayCollection($teste);
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return ($this->createdAt == null) ? null : $this->createdAt->format('d/m/Y');
		//return $this->createdAt;
	}

	/**
	 * @param \DateTime $createdAt
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 * @param \Application\Entity\Location
	 */
	public function setLocation($locations)
	{
		if(empty($locations)) {
			$this->location = null;
		} else {
			$this->location = $locations;
		}

	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return boolean
	 */
	public function getVisibility()
	{
		return $this->visibility;
	}

	/**
	 * @param boolean $name
	 */
	public function setVisibility($visibility)
	{
		$this->visibility = (string) $visibility;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getTags()
	{
		return $this->tags;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $tags
	 */
	public function setTags($tags)
	{
		$this->tags = $tags;
	}

	/**
	 * @return \DateTime
	 */
	public function getUpdatedAt()
	{
		return ($this->updatedAt == null) ? null : $this->updatedAt->format('d/m/Y');
		//return $this->updatedAt;
	}

	/**
	 * @param \DateTime $updatedAt
	 */
	public function setUpdatedAt($updatedAt)
	{
		$this->updatedAt = $updatedAt;
	}

	/**
	 *
	 * @return \Application\Entity\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	public function setUser($user)
	{
		$this->user = $user;
	}

	/*public function getImages()
	{
		return $this->images;
	}

	public function setImages($images)
	{
		$this->images = $images;
	}*/

	/**
	 * @ORM\PostLoad
	 */
	public function setCover(LifecycleEventArgs $event) {
		try {
			$entityManager = $event->getEntityManager();
			$repository    = $entityManager->getRepository('Application\Entity\Image');
			$img = $repository->getLastImageAlbum($this->getId());

			if(!$img) {
				$this->cover = '';
				return;
			}

			$this->cover = $img->getThumbnailUrl();

		} catch (\Exception $e) {
			$this->cover = null;
			return;
		}
	}

	public function getCover() {
		return $this->cover;
	}

	/**
	 * @ORM\PostLoad
	 */
	public function setImageCount(LifecycleEventArgs $event) {
		try {
			$entityManager = $event->getEntityManager();
			$repository    = $entityManager->getRepository('Application\Entity\Image');
			$this->imageCount = $repository->contImagesAlbum($this->getId());
		} catch (\Exception $e) {
			$this->imageCount = 0;
		}
	}

	public function getImageCount() {
		return $this->imageCount;
	}

	/**
	 * @ORM\PostLoad

	public function getShare(LifecycleEventArgs $event) {
		try {
			$entityManager = $event->getEntityManager();
			$repository    = $entityManager->getRepository('Application\Entity\Share');
			$this->share = $repository->findShareByAlbum($this->getId());
		} catch (\Exception $e) {
			$this->share = null;
		}
	}*/
	public function getShareUsers()
	{
		return $this->shareUsers;
	}

	public function setShareUsers($shareUsers)
	{
		$this->shareUsers = $shareUsers;
	}

	public function getShareGroups()
	{
		return $this->shareGroups;
	}

	public function setShareGroups($shareGroups)
	{
		$this->shareGroups = $shareGroups;
	}


}