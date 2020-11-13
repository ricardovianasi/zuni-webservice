<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="tags", indexes={@ORM\Index(name="tag", columns={"tag"})})
 * @ORM\Entity(repositoryClass="Application\Repository\Tag")
 * 
 */
class Tag extends AbstractEntity
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
     * @ORM\Column(name="tag", type="string", length=45, nullable=false)
     */
    private $tag;

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
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}

	/**
	 * @param string $tag
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;
	}

}
