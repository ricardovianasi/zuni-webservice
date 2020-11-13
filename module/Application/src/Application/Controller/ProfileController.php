<?php
namespace Application\Controller;

use Doctrine\Common\Collections\ArrayCollection;
class ProfileController extends AbstractRestfulJsonController {

	public function getList() {
		$repository = $this->getRepository('Application\Entity\Profile');
		$list = $repository->findAll();

		if(!$list)
			$this->getJsonModel()->profiles = array();
		else $this->getJsonModel()->profiles = $this->arrayLisToJson($list, 1);

		return $this->getJsonModel();
	}

	public function get($id) {
		if(empty($id))
			$this->applicationError('Id não informado!');

		$repository = $this->getRepository('Application\Entity\Profile');
		$perfil = $repository->find($id);
		if($perfil)
			$this->getJsonModel()->profiles = $perfil->toArray(2);
		else $this->getJsonModel()->profiles = array();

		return $this->getJsonModel();
	}

	public function create($data) {
		$profile = $this->save($data);
		$this->getJsonModel()->profiles = $profile->toArray();
		$this->getJsonModel()->profileCreated = true;
		return $this->getJsonModel();
	}

	public function update($id, $data) {
		$profile = $this->save($data, $id);
		$this->getJsonModel()->profiles = $profile->toArray();
		$this->getJsonModel()->profileUpdated = true;
		return $this->getJsonModel();
	}

	public function delete($id) {
		if(empty($id))
			$this->applicationError('Id não informado!');

		$profile = $this->getRepository('Application\Entity\Profile')->find($id);
		if(!$profile) {
			$this->applicationError('Id não encontrado!');
		}

		$profile->setStatus(0);
		$this->getRepository('Application\Entity\Profile')->update($profile);
		$this->getJsonModel()->profiles = array(
				'deleted' => true,
				$profile->toArray()

		);
		return $this->getJsonModel();
	}

	public function save(array $data, $id=null) {
		if(empty($data))
			$this->applicationError('Dados não informados');

		if(!empty($id)) {
			$profile = $this->getRepository('Application\Entity\Profile')->find($id);
			$profile->setDate($data);
		} else {
			$profile = new \Application\Entity\Profile($data);
		}

		if($data['actions']) {
			$collActions = new ArrayCollection();
			foreach ($data['actions'] as $idAction) {
				$action = $this->getRepository('Application\Entity\Action')->find($idAction);
				$collActions->add($action);
			}
			$profile->setActions($collActions);
		}

		$this->getRepository('\Application\Entity\Profile')->update($profile);
		return $profile;
	}

	public function activeAction() {
		$status = (boolean) $this->params()->fromQuery('status', false);
		$id = $this->params()->fromQuery('id');
		$profile = $this->getRepository('Application\Entity\Profile')->find($id);
		if(!$profile) {
			$this->getJsonModel()->error = true;
			$this->getJsonModel()->message = 'Profile não encontrado';
			return $this->getJsonModel();
		}

		$profile->setStatus($status);
		$this->getRepository('Application\Entity\Profile')->update($profile);

		$this->getJsonModel()->profileStatus = $status;
		$this->getJsonModel()->profile = $profile->toArray();
		return $this->getJsonModel();
	}
}
