<?php
namespace Application\Repository;

use Application\Security\Crypt;
class PersistentLogin extends AbstractRepository {

	public function create($identity) {

		//Primeiro limpa todos os registros de logins persistentes
		$this->removeAllByUser($identity->getId());

		//Cria um novo
		$entity = new \Application\Entity\PersistentLogin(array(
			'serialIdentifier' => Crypt::generateRandomToken(),
			'token' => Crypt::generateRandomToken(),
			'user' => $identity
		));

		$this->getEntityManager()->persist($entity);
		$this->getEntityManager()->flush();
		return $entity;
	}

	public function removeAllByUser($idUser) {
		$qb = $this->createQueryBuilder('p');
		$qb->delete('Application\Entity\PersistentLogin', 'p')
			->where('p.user = :idUser')
			->setParameter('idUser', $idUser);

		$qb->getQuery()->execute();
		$this->getEntityManager()->flush();
	}

	/**
	 *
	 * @param int $idUser
	 * @return \Application\Entity\PersistentLogin
	 */
	public function findByUser($idUser) {
		return $this->findOneBy(array('user'=>$idUser));
	}

	/**
	 *
	 * @param id $idUser
	 * @param string $token
	 * @param string $serialIdentifier
	 * @return Ambigous <Null, \Application\Entity\PersistentLogin>
	 */
	public function validate($serialIdentifier) {

		$qb = $this->createQueryBuilder('p');
		$qb->andWhere('p.token = :identifier')->setParameter('identifier', $serialIdentifier);

		return $qb->getQuery()->getOneOrNullResult();
	}

	public function deleteByUser($idUser) {
		$qb = $this->createQueryBuilder('p');
		$qb->delete('Application\Entity\PersistentLogin', 'p')
			->andWhere('p.user = :idUser')
			->setParameter('idUser', $idUser);
		$qb->getQuery()->execute();
	}

}