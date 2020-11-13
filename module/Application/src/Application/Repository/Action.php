<?php
namespace Application\Repository;

class Action extends AbstractRepository {

	public function findByController($idController=null, $actionName=null) {

		if(empty($idController))
			return null;

		$sql = $this->createQueryBuilder('p');
			$sql->innerJoin('p.controller', 'c')
			->andWhere('c.id = :idController')
			->andWhere('p.name = :actionName')
			->setParameters(array(
				'idController' => $idController,
				'actionName' => $actionName
			));

		return $sql->getQuery()->getOneOrNullResult();
	}

}