<?php
namespace Application\Repository;

class Profile extends AbstractRepository {
	public function findExternalProfile() {
		return $this->findOneBy(array('externalProfile'=>1));
	}

	public function has($controller, $action, $idUser=null, $idProfile=null) {
		$sql = $this->createQueryBuilder('P');
		$sql->select('count(P)');

		if(!empty($idUser)) {
			$sql->join('P.users', 'U')
				->andWhere('U.id = :idUser')
				->setParameter('idUser', $idUser);
		}

		$sql->innerJoin('P.actions', 'PA')
			->innerJoin('PA.controller', 'PC')
			->andWhere('PA.name = LOWER(:action)')
			->andWhere('PC.name = LOWER(:controller)')
			->setParameter('action', $action)
			->setParameter('controller', strtolower($controller));

		if(!empty($idProfile)) {
			$sql->andWhere('P.id = :idProfile')
				->setParameter('idProfile', $idProfile);
		}

		return $sql->getQuery()->getSingleScalarResult();
	}
}