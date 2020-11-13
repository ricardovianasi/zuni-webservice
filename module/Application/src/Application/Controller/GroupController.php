<?php
namespace Application\Controller;

use Doctrine\Common\Collections\ArrayCollection;

class GroupController extends AbstractRestfulJsonController {
	public function getList() {
		$repositorio = $this->getRepository('Application\Entity\Group');
		$list = $repositorio->findAll();

		if(!$list)
			$this->getJsonModel()->groups = array();
		else $this->getJsonModel()->groups = $this->arrayLisToJson($list, 2);

		return $this->getJsonModel();
	}

	public function get($id) {
		if(empty($id))
			$this->applicationError('Id não informado!');

		$group = $this->getGroupRepository()->find($id);
		if($group)
			$this->getJsonModel()->groups = $group->toArray();
		else $this->getJsonModel()->groups = array();

		return $this->getJsonModel();
	}

	public function create($data) {
		if(empty($data))
			$this->applicationError('Dados não informados');

		$group = $this->save($data);
		$this->getJsonModel()->groups = $group->toArray();
		$this->getJsonModel()->groupCreated = true;
		return $this->getJsonModel();
	}

	public function update($id, $data) {
		if(empty($data))
			$this->applicationError('Dados não informados');

		if(empty($data))
			$this->applicationError('Dados não informados');

		$group = $this->save($data, $id);
		$this->getJsonModel()->groups = $group->toArray();
		$this->getJsonModel()->groupUpdated = true;
		return $this->getJsonModel();
	}

	public function delete($id) {
		if(empty($id))
			$this->applicationError('Id não informado!');

		$group = $this->getGroupRepository()->find($id);
		if(!$group) {
			$this->applicationError('Id não encontrado!');
		}

		$group->setStatus(0);
		$this->getGroupRepository()->update($group);

		$this->getJsonModel()->profiles = array(
			'deleted' => true,
			$group->toArray()
		);
		return $this->getJsonModel();
	}

	protected function save($data, $id=null) {
		if(!empty($id)) {
			$group = $this->getGroupRepository()->find($id);
		} else {
			$group = new \Application\Entity\Group();
		}

		if(isset($data['name'])) {
			$group->setName($data['name']);
		}

		if(isset($data['description'])) {
			$group->setDescription($data['description']);
		}

		if(isset($data['users'])) {
			$collUsers = new ArrayCollection();
			foreach ($data['users'] as $idUser) {
				$user = $this->getRepository('Application\Entity\User')->find($idUser);
				if($user)
					$collUsers->add($user);
			}
			$group->setUsers($collUsers);
		}

		$this->getGroupRepository()->update($group);
		return $group;
	}

	public function activeAction() {
		$status = (boolean) $this->params()->fromQuery('status', false);
		$id = $this->params()->fromQuery('id');
		$group = $this->getGroupRepository()->find($id);
		if(!$group) {
			$this->getJsonModel()->error = true;
			$this->getJsonModel()->message = 'Grupo não encontrado';
			return $this->getJsonModel();
		}

		$group->setStatus($status);
		$this->getGroupRepository()->update($group);

		$this->getJsonModel()->groupStatus = $status;
		$this->getJsonModel()->group = $group->toArray();
		return $this->getJsonModel();
	}

	/**
	 *
	 * @return \Application\Repository\Group
	 */
	protected function getGroupRepository() {
		return $this->getRepository('Application\Entity\Group');
	}
}