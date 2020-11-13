<?php
namespace Application\Repository;

class Group extends AbstractRepository {
	public function getAllActive() {
		return $this->findBy(array('status'=>1));
	}
}