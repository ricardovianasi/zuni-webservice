<?php
namespace Application\Repository;

class Share extends AbstractRepository {

	public function findUsersOrGroupsSharedAlbum($idProvider, $idAlbum) {
	}

	/**
	 * Verifica se a hash é válida
	 * @return bool
	 * @param string $hash
	 */
	public function validateHashExternalAcess($hash) {
		$hash = (string) $hash;
		$qb = $this->createQueryBuilder('p')
			->innerJoin('p.external', 'pe')
			->where('pe.hashAcess = :hash and pe.validUntil >= :dataHoje')
			->setParameter('hash', $hash)
			->setParameter('dataHoje', date('Y-m-d'));
		return $qb->getQuery()->getOneOrNullResult();
	}

	/**
	 *
	 * @param int $idAlbum
	 * @return array
	 */
	public function findShareByAlbum($idAlbum) {
		return $this->findBy(array('album'=>$idAlbum));
	}
}