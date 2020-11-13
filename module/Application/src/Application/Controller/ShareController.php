<?php
namespace Application\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Security\Crypt;
class ShareController extends AbstractRestfulJsonController {

	public function getList() {
		$users = $this->getRepository('Application\Entity\User')->getAllActive();
		$groups = $this->getRepository('Application\Entity\Group')->findAll();

		$this->getJsonModel()->users = $this->arrayLisToJson($users, 1, array('access_key', 'created_at', 'expires_at', 'expires_at', 'formatted_expires_at', 'origin', 'avatar', 'avatar_url', 'profiles', 'status', 'updated_at', 'groups', 'phone', 'is_external', 'has_administrator_profile', 'list_permissions'));
		$this->getJsonModel()->groups = $this->arrayLisToJson($groups);

		return $this->getJsonModel();
	}

	public function getAllList() {
	}

	public function create($data) {
		$provider = $this->getAuthService()->getIdentity();

		$collUsers = new ArrayCollection();
		if(key_exists('users', $data)) {
			foreach ($data['users'] as $id) {
				$collUsers->add($this->getRepository('Application\Entity\User')->find($id));
			}
		}

		$collGroups = new ArrayCollection();
		if(key_exists('groups', $data)) {
			foreach ($data['groups'] as $id) {
				$collGroups->add($this->getRepository('Application\Entity\Group')->find($id));
			}
		}

		$arrayShare = array();
		if(key_exists('albums', $data)) {
			foreach ($data['albums'] as $id) {
				$album = $this->getRepository('Application\Entity\Album')->find($id);

				$share = new \Application\Entity\Share();
				$share->setAlbum($album);
				$share->setProvider($provider);
				$share->setGroups($collGroups);
				$share->setUsers($collUsers);
				$this->getRepository('Application\Entity\Share')->update($share);
				$arrayShare[] = $share;
			}
		}

		$this->getJsonModel()->share = $this->arrayLisToJson($arrayShare);
		$this->getJsonModel()->shareCreated = true;

		return $this->getJsonModel();
	}

	public function createExternalAction() {

		$album = $this->getRepository('Application\Entity\Album')->find($this->params()->fromPost('album'));

		$share = new \Application\Entity\Share();
		$share->setProvider($this->getAuthService()->getIdentity());
		$share->setAlbum($album);
		$this->getRepository('Application\Entity\Share')->update($share);

		$externalShare = new \Application\Entity\ShareExternal();
		$externalShare->setShare($share);
		$externalShare->setDestination($this->params()->fromPost('name'));
		$externalShare->setEmail($this->params()->fromPost('email'));
		$externalShare->setObservations($this->params()->fromPost('observations'));
		$externalShare->setValidUntil($this->params()->fromPost('validUntil'));
		$externalShare->setHashAcess(Crypt::generateHashToUrl());
		$this->getRepository('Application\Entity\ShareExternal')->update($externalShare);

		//Envia as emails
		//TODO: Enviar email com a hash de acesso

		$this->getJsonModel()->externalAccess = $externalShare->toArray();
		$this->getJsonModel()->externalAccessCreated = true;
		return $this->getJsonModel();
	}
}