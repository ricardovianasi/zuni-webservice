<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ignored_words")
 * @ORM\Entity(repositoryClass="Application\Repository\IgnoredWords")
 */
class IgnoredWords extends AbstractEntity {

	/**
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="value", type="string", nullable=false)
	 */
	private $value;

	public function getId() {
		return $this->id;
	}

	public function getValue() {
		return $this->value;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setValue($value) {
		$this->value = $value;
	}
}