<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Table(name="images", indexes={@ORM\Index(name="fk_tb_imagens_tb_usuarios1_idx", columns={"id_user"}), @ORM\Index(name="fk_tb_imagens_tb_usuarios2_idx", columns={"id_owner"}), @ORM\Index(name="fk_tb_imagens_tb_albuns1_idx", columns={"id_album"})})
 * @ORM\Entity(repositoryClass="Application\Repository\Image")
 */
class Image extends AbstractEntity {
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
	 * @ORM\Column(name="geolocation", type="string", length=40, nullable=true)
	 */
	private $geolocation;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="replace_owner_name", type="string", length=150, nullable=true)
	 */
	private $replaceOwnerName;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="text", nullable=true)
	 */
	protected $description;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="date", type="date", length=10, nullable=true)
	 */
	protected $date;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="filename", type="string", length=255, nullable=false)
	 */
	private $filename;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="extension", type="string", length=10, nullable=false)
	 */
	private $extension;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="width", type="integer", length=4, nullable=false)
	 */
	protected $width;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="height", type="integer", length=4, nullable=false)
	 */
	protected $height;

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
	 * @var \Users
	 *
	 * @ORM\ManyToOne(targetEntity="Application\Entity\User")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_user", referencedColumnName="id")
	 * })
	 */
	protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Application\Entity\Author", cascade={"persist"})
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_author", referencedColumnName="id")
	 * })
	 */
	protected $owner;

	/**
	 * @var \Albums
	 *
	 * @ORM\ManyToOne(targetEntity="Application\Entity\Album", inversedBy="images", fetch="EAGER")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_album", referencedColumnName="id")
	 * })
	 */
	private $album;

	/**
	 * @var \Users
	 *
	 * @ORM\ManyToOne(targetEntity="Application\Entity\Location", cascade={"persist"})
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_location", referencedColumnName="id")
	 * })
	 */
	private $location;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="Application\Entity\Tag", cascade={"persist"})
	 * @ORM\JoinTable(name="images_tags",
	 *   joinColumns={
	 *     @ORM\JoinColumn(name="id_image", referencedColumnName="id")
	 *   },
	 *   inverseJoinColumns={
	 *     @ORM\JoinColumn(name="id_tag", referencedColumnName="id")
	 *   }
	 * )
	 */
	private $tags;

	private $downloadCounter = null;

	/**
	 * Constructor
	 */
	public function __construct($data=null)
	{
		$this->tags = new \Doctrine\Common\Collections\ArrayCollection();
		// $teste = $this->getApplicationConfig();
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		if($this->createdAt instanceof \DateTime) {
			return $this->createdAt->format('d/m/Y');
		}
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
	 * @return string
	 */
	public function getDate()
	{
		return $this->date;
	}

	public function getFormattedDate() {
		if($this->date instanceof \DateTime) {
			return $this->date->format('d/m/Y');
		}
		return $this->date;
	}

	/**
	 * @param string $date
	 */
	public function setDate($date) {
		if($date instanceof \DateTime) {
			$this->date = $date;
		} elseif (preg_match ('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date)) {
			$this->date = \DateTime::createFromFormat ('d/m/Y', $date);
		} elseif (preg_match ( '/^\d{4}\-\d{1,2}\-\d{1,2}$/', $date )) {
			$this->date = new \DateTime($date);
		} else {
			$this->date = null;
		}
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
	 * @return string
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * @param string $extension
	 */
	public function setExtension($extension)
	{
		$this->extension = $extension;
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @param string $filename
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
	}

	/**
	 * @return string
	 */
	public function getGeolocation()
	{
		return $this->geolocation;
	}

	/**
	 * @param string $geolocation
	 */
	public function setGeolocation($geolocation)
	{
		$this->geolocation = $geolocation;
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
	 * @return \Albums
	 */
	public function getAlbum()
	{
		return $this->album;
	}

	/**
	 * @param \Albums $idAlbum
	 */
	public function setAlbum($album)
	{
		$this->album = $album;
	}

	/**
	 * @return string
	 */
	public function getOwner()
	{
		return $this->owner;
	}

	/**
	 * @param string
	 */
	public function setOwner($owner)
	{
		if(empty($owner)) {
			$this->owner = null;
		} else {
			$this->owner = $owner;
		}

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

	public function getLocation()
	{
		return $this->location;
	}

	public function setLocation($locations)
	{
		if(empty($locations)) {
			$this->location = null;
		} else {
			$this->location = $locations;
		}
	}

	public function getWidth()
	{
		return $this->width;
	}

	public function setWidth($width)
	{
		$this->width = $width;
	}

	public function getHeight()
	{
		return $this->height;
	}

	public function setHeight($height)
	{
		$this->height = $height;
	}

	/**
	 * @return string
	 */
	public function getReplaceOwnerName()
	{
		return $this->replaceOwnerName;
	}

	/**
	 * @param string $replaceOwnerName
	 */
	public function setReplaceOwnerName($replaceOwnerName)
	{
		$this->replaceOwnerName = $replaceOwnerName;
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
		if($this->updatedAt instanceof \DateTime) {
			return $this->updatedAt->format('d/m/Y');
		}
		return $this->updatedAt;
	}

	/**
	 * @param \DateTime $updatedAt
	 */
	public function setUpdatedAt($updatedAt)
	{
		$this->updatedAt = $updatedAt;
	}

	public function getUploadUrl() {
		return $this->_getImageFilesService()->getUploadUrlImage($this->getFilename());
	}

	public function getThumbnailUrl() {
		return $this->_getImageFilesService()->getThumbnailUrlImage($this->getFilename());
	}

	public function getSize() {
		if(!(empty($this->width) && empty($this->height)))
			return $this->width.'x'.$this->height;
		else null;
	}

	/**
	 * @ORM\PostLoad
	 */
	public function getDownloadCounter() {

	}
}
