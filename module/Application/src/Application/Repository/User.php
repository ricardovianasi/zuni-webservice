<?php
namespace Application\Repository;

class User extends AbstractRepository {

	public function getAllActive() {
		return $this->findBy(array('status'=>1));
	}

}