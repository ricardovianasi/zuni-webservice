<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="share_external", indexes={@ORM\Index(name="fk_share_external_share1_idx", columns={"id_share"})})
 * @ORM\Entity(repositoryClass="Application\Repository\ShareExternal")
 */
class ShareExternal extends AbstractEntity {

	/**
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @ORM\OneToOne(targetEntity="Share", inversedBy="external")
	 * @ORM\JoinColumn(name="id_share", referencedColumnName="id")
	 */
	private $share;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="hash_access", type="string", length=100, nullable=false)
	 */
	private $hashAcess;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="destination", type="string", length=45, nullable=true)
	 */
	private $destination;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="email", type="string", length=60, nullable=true)
	 */
	private $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="observations", type="string", length=200, nullable=true)
	 */
	private $observations;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="valid_until", type="date", nullable=true)
	 */
	private $validUntil;

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return \Application\Entity\Share
	 */
	public function getShare()
	{
		return $this->share;
	}

	public function setShare($share)
	{
		$this->share = $share;
	}

	public function getHashAcess()
	{
		return $this->hashAcess;
	}

	public function setHashAcess($hashAcess)
	{
		$this->hashAcess = $hashAcess;
	}

	public function getDestination()
	{
		return $this->destination;
	}

	public function setDestination($destination)
	{
		$this->destination = $destination;
	}

	public function getObservations()
	{
		return $this->observations;
	}

	public function setObservations($observations)
	{
		$this->observations = $observations;
	}
	public function getValidUntil()
	{
		return $this->validUntil;
	}

	public function setValidUntil($validUntil)
	{
		$this->validUntil = $validUntil;
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

	public function getEmail()
	{
		return $this->email;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}
}