<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="metadata")
 * @ORM\Entity
 */
class Metadata extends AbstractEntity
{
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
     * @var boolean
     *
     * @ORM\Column(name="type", type="boolean", nullable=true)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="default", type="integer", nullable=true)
     */
    private $default = '0';

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Image", inversedBy="idMetadat")
     * @ORM\JoinTable(name="images_metadata",
     *   joinColumns={
     *     @ORM\JoinColumn(name="id_metadat", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="id_image", referencedColumnName="id")
     *   }
     * )
     */
    private $images;

    /**
     * Constructor
     */
    public function __construct(array $data=null)
    {
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();

		parent::__construct($data);
    }

	/**
	 * @return int
	 */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * @param int $default
	 */
	public function setDefault($default)
	{
		$this->default = $default;
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
	public function getImages()
	{
		return $this->images;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $images
	 */
	public function setImages($images)
	{
		$this->images = $images;
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
	public function isType()
	{
		return $this->type;
	}

	/**
	 * @param boolean $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

}
