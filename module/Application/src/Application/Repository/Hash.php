<?php
namespace Application\Repository;

class Hash extends AbstractRepository {
	public function isValid($hash) {
		$sql = $this->createQueryBuilder('p');
		$sql->select('p')
			->andWhere('p.validUntil >= :dataAtual')
			->setParameter('dataAtual', new \DateTime('now'));
		
		return $sql->getQuery()->getOneOrNullResult();
	}
}