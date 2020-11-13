<?php
namespace Application\Repository;

class IgnoredWords extends AbstractRepository {

	public function findByValue($str) {
		$sql = $this->createQueryBuilder('p');
		$sql->select('count(p)')
			->andWhere('p.value = :value')
			->setParameter('value', $str);

		if($sql->getQuery()->getScalarResult())
			return true;

		return false;
	}
}