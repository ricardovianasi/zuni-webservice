<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;

class AbstractRepository extends EntityRepository {

	public function update($entity) {
		$this->getEntityManager()->persist($entity);
		$this->getEntityManager()->flush();
		return $entity;
	}

	public function delete($model) {
		//Verifica se existe
		$entityNome = get_class($model);
		$repository = $this->getEntityManager()->getRepository($entityNome);
		$find = $repository->find($model->getId());
		if($find) {
			$entity = $this->getEntityManager()->getReference($entityNome, $model->getId());
			if($entity) {
				$this->getEntityManager()->remove($model);
				$this->getEntityManager()->flush();
			}
		}
		return $model->getId();
	}
}