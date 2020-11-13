<?php
namespace Application\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Security\Crypt;
class UserController extends AbstractRestfulJsonController {

	public function getList() {
		$repository = $this->getRepository('Application\Entity\User');
		$list = $repository->findAll();

		if(!$list)
			$this->getJsonModel()->users = array();
		else $this->getJsonModel()->users = $this->arrayLisToJson($list, 2, array('password'));

		return $this->getJsonModel();
	}

	public function get($id) {
		if(empty($id))
			$this->applicationError('Id não informado!');

		$repository = $this->getRepository('Application\Entity\User');
		$user = $repository->find($id);
		if($user)
			$this->getJsonModel()->users = $user->toArray(2, array('password'));
		else $this->getJsonModel()->users = array();

		return $this->getJsonModel();
	}

	public function create($data) {
		$user = $this->processUpdate($data);

		//Enviar Hash por email para que o usuário possa atualizar a senha
		$hash = Crypt::generateHashToUrl();

		$this->getJsonModel()->userCreated = true;
		$this->getJsonModel()->user = $user->toArray(1, array());
		$this->getJsonModel()->usersHash = $hash;

		return $this->getJsonModel();
	}

	public function update($id, $data) {
		$user = $this->processUpdate($data, $id);
		$this->getJsonModel()->userUpdated = true;
		$this->getJsonModel()->user = $user->toArray(1, array());
		return $this->getJsonModel();
	}

	public function delete($id) {
		if(empty($id))
			$this->applicationError('Id não informado!');

		$user = $this->getRepository('Application\Entity\User')->find($id);
		if(!$user) {
			$this->applicationError('Id não encontrado!');
		}

		$user->setStatus(0);
		$this->getRepository('Application\Entity\User')->update($user);
		$this->getJsonModel()->userDeleted = true;
		$this->getJsonModel()->users = $user->toArray(2, array('password'));

		return $this->getJsonModel();
	}

	public function activeAction() {
		$status = (boolean) $this->params()->fromQuery('status', false);
		$id = $this->params()->fromQuery('id');
		$user = $this->getRepository('Application\Entity\User')->find($id);
		if(!$user) {
			$this->getJsonModel()->error = true;
			$this->getJsonModel()->message = 'Usuário não encontrado';
			return $this->getJsonModel();
		}

		$user->setStatus($status);
		$this->getRepository('Application\Entity\User')->update($user);

		$this->getJsonModel()->userStatus = $status;
		$this->getJsonModel()->user = $user->toArray();
		return $this->getJsonModel();
	}

	public function processUpdate(array $data, $id=null) {
		if(empty($data))
			$this->applicationError('Dados não informados');

		if(!empty($id)) {
			$userEntity = $this->getRepository('Application\Entity\User')->find($id);
			if(key_exists('password', $data)) {
				unset($data['password']);
			}
			$userEntity->setData($data);

		} else {
			$userEntity = new \Application\Entity\User($data);
			$userEntity->setPassword(Crypt::encryptPassword($data['password']));
			$userEntity->setStatus(TRUE);
		}

		if(!empty($data['profiles'])) {
			$collProfile = new ArrayCollection();
			foreach ($data['profiles'] as $id) {
				$entity = $this->getRepository('Application\Entity\Profile')->find($id);

				if(!empty($entity))
					$collProfile->add($entity);
			}
			$userEntity->setProfiles($collProfile);
		}

		if(!empty($data['groups'])) {
			$collGroups = new ArrayCollection();
			foreach ($data['groups'] as $id) {
				$entity = $this->getRepository('Application\Entity\Group')->find($id);
				$collGroups->add($entity);
			}
			$userEntity->setGroups($collGroups);
		}

		$this->getRepository('Application\Entity\User')->update($userEntity);
		return $userEntity;
	}

	public function updateAvatarAction() {
		$image = $this->params()->fromPost('image', null);
		$id = $this->params()->fromPost('id', null);
		if(empty($image) || empty($id)) {
			$this->applicationError('Imagem ou id não informados!');
		}

		$user = $this->getRepository('Application\Entity\User')->find($id);
		if(!$user) {
			$this->applicationError('Usuário não encontrado!');
		}

		$tempFile = $this->getImageFilesService()->getTmpDirImage($image);
		$newFile = $this->getImageFilesService()->getUploadDirImage($image);
		if(rename($tempFile, $newFile)) {
			if(!empty($user->getAvatar())) {
				$this->getImageFilesService()->remove($user->getAvatar());
			}
			$user->setAvatar($image);
			$this->getRepository('Application\Entity\User')->update($user);

			$this->getJsonModel()->avatar_url = $this->getImageFilesService()->getThumbnailUrlImage($image);
			return $this->getJsonModel();
		} else {
			$this->applicationError('Erro ao salvar o arquivo!');
		}
	}

	public function updatePasswordAction() {

		$hash = $this->params()->fromPost('hash', null);

		//Testar se o usuário está logado e recuperar o id
		if($this->getAuthService()->hasIdentity()) {
			$user = $this->getRepository('Application\Entity\User')->find($this->getAuthService()->getIdentity()->getId());
			$oldPass = $this->params()->fromPost('oldPass', null);
		} elseif(!empty($hash)) {
			if(!$entity = $this->getRepository('Application\Entity\Hash')->isValid($hash)) {
				$this->getJsonModel()->updatePassword = array(
					'error' => true,
					'message' => 'A hash não é válida'
				);
				return $this->getJsonModel();
			}
			$user = $entity->getUser();
		} else {
			$this->applicationError('Hash ou usuario não informado!');
		}

		if(!$this->getRequest()->isPost()) {
			$this->getJsonModel()->updatePassword = array(
					'error' => true,
					'message' => 'Dados não informados'
			);
			return $this->getJsonModel();
		}

		$pass = $this->params()->fromPost('pass', null);
		$confirmPass = $this->params()->fromPost('confirmPass', null);

		//Verifica se a senha antiga é válida
		if(!empty($oldPass)) {
			$validate = new \Zend\Validator\Identical($user->getPassword());
			if(!$validate->isValid(Crypt::encryptPassword($oldPass))) {
				$this->getJsonModel()->updatePassword = array(
					'error' => true,
					'message' => 'A senha antiga não confere'
				);
				return $this->getJsonModel();
			}
		}

		//Verifica se as senhas são iguais
		$validate = new \Zend\Validator\Identical($pass);
		if(!$validate->isValid($confirmPass)) {
			$this->getJsonModel()->updatePassword = array(
				'error' => true,
				'message' => 'As senhas não são identicas'
			);
			return $this->getJsonModel();
		}

		$user->setPassword(Crypt::encryptPassword($pass));
		$this->getRepository('Application\Entity\User')->update($user);
		//Exclui a hash, caso ela exista
		if($hash) {
			$this->getRepository('Application\Entity\Hash')->delete($entity);
		}

		$this->getJsonModel()->updatePassword = array(
			'update' => true,
			'message' => 'Senha alterada com sucesso'
		);
		return $this->getJsonModel();
	}

	public function forgetPasswordAction() {
		$request = $this->getRequest();
		$email = $request->getPost('email', null);
		if(empty($email)) {
			$this->getJsonModel()->forgetPassword = array(
				'error' => true,
				'message' => 'Email não informado'
			);
			return $this->getJsonModel();
		}

		//valida o formato do email e se ele está cadastrado no banco
		$emailValidator = new \Application\Validator\Email();
		$emailValidator->setEm($this->getEntityManager());
		$emailValidator->setEntityName('Application\Entity\User');
		if(!$emailValidator->isValid($email, true)) {
			$this->getJsonModel()->forgetPassword = array(
				'error' => true,
				'message' => $emailValidator->getMessages()
			);
			return $this->getJsonModel();
		}

		$user = $this->getRepository('Application\Entity\User')->findOneBy(array('email'=>$email));

		$entityHash = new \Application\Entity\Hash();
		$entityHash->setUser($user);
		$entityHash->setHash(Crypt::generateHashToUrl());
		$this->getRepository('Application\Entity\Hash')->update($entityHash);

		//Enviar o email
		//@TODO: Enviar o email para recuperar senha
		$url = '/user/update-password/' . $entityHash->getHash();
		$msg = "Prezado, acesse seu email e clique na url " . $url;

		$this->getJsonModel()->forgetPassword = array(
			'sentEmail' => true,
			'mensage' => 'E-mail enviado com sucesso!',
			'msg' => $msg
		);
		return $this->getJsonModel();
	}

	public function getPermissionsAction() {

	}
}