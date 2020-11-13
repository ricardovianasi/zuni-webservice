<?php
namespace Application\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Tag;

class ImageController extends AbstractRestfulJsonController {

	private $blackListAttributes = array('user', 'extension', 'geolocation', 'created_at', 'width', 'height', 'replace_owner_name', 'updated_at');

	public function get($id) {
		if(empty($id))
			$this->applicationError('Id não informado!');

		$user = $this->getAuthService()->getIdentity();
		if($user->isExternal()) {
			$this->getJsonModel()->externalAccess = true;

			//Confirma se o acesso à imagem é permito ao usuário externo
			$share = $this->getRepository('Application\Entity\Share')->validateHashExternalAcess($this->getAuthService()->getIdentity()->getAccessKey());
			$image = $this->getImageRepository()->search($id, $share->getAlbum()->getId());
			if(is_array($image) && count($image)>0) {
				$image = $image[0];
			} else {
				$this->getJsonModel()->error = true;
				$this->getJsonModel()->mensage = "O usuário não tem acesso ao recurso";
			}
		} else {
			if($this->getAuthService()->authorize('image', 'getSuper')) {
				$image = $this->getImageRepository()->find($id);
			} else {
				$image = $this->getImageRepository()->search($id, null, $user->getId());
				if(!empty($image)) {
					$image = $image[0];
				}
			}
		}

		if($image) {
			$this->getJsonModel()->images = $image->toArray(1, $this->blackListAttributes);
		} else $this->getJsonModel()->images = array();

		return $this->getJsonModel();
	}

	public function getList() {
		$query = $this->params()->fromQuery('query', null);
		$idAlbum = $this->params()->fromQuery('idAlbum', null);

		//Verifica se o usuário logado é de acesso externo
		$user = $this->getAuthService()->getIdentity();
		if($user->isExternal()) {
			if(!$share = $this->getRepository('Application\Entity\Share')->validateHashExternalAcess($user->getAccessKey())) {
				$this->getJsonModel()->images = array();
				return $this->getJsonModel();
			}
			$images = $this->getImageRepository()->getAllByAlbum($share->getAlbum()->getId());
			$idAlbum = $share->getAlbum()->getId();
			$this->getJsonModel()->externalAccess = true;
		} else {
			if($this->getAuthService()->authorize('image', 'getListSuper')) {
				$images = $this->processSearch($query, $idAlbum);
			} else {
				$images = $this->processSearch($query, $idAlbum, $this->getAuthService()->getIdentity()->getId());
			}
		}

		if(!empty($idAlbum)) {
			$this->getJsonModel()->album = $this->getRepository('Application\Entity\Album')->find($idAlbum)->toArray();
		} else {
			if(($key = array_search('album', $this->blackListAttributes)) !== false) {
				unset($this->blackListAttributes[$key]);
			}
		}

		$this->getJsonModel()->images = $this->arrayLisToJson($images,1, $this->blackListAttributes);
		return $this->getJsonModel();
	}

	protected function processSearch($query, $idAlbum=null, $idUser=null) {
		$filter = new \Application\Filter\QuerySearch($this->getRepository('Application\Entity\IgnoredWords'));
		$query = $filter->filter($query);

		return $this->getImageRepository()->search($query, $idAlbum, $idUser);
	}

	public function update($id, $data) {
		if(empty($data))
			$this->applicationError('Dados não informados');

		if(empty($id))
			$this->applicationError('Id não informado');

		$img = $this->processImageUpdate($data, $id);

		if($img) {
			$this->getJsonModel()->imagesUpdated = true;
			$this->getJsonModel()->images = $img->toArray();
		} else {
			$this->getJsonModel()->imagesUpdated = false;
			$this->getJsonModel()->error = "A imagem não foi encotrada ou você não tem acesso à mesma";
		}
		return $this->getJsonModel();
	}

	public function processImageUpdate($data, $id) {

		if($this->getAuthService()->authorize('image', 'updateSuper')) {
			$img = $this->getImageRepository()->find($id);
		} else {
			$img = $this->getImageRepository()->search($id, null, $this->getAuthService()->getIdentity()->getId());
			if(!empty($img)) {
				$img = $img[0];
			} else return null;

			//Verifica a propriedade
			if(!($img->getUser()->getId() == $this->getAuthService()->getIdentity()->getId())) {
				return null;
			}
		}

		if(!$img) return null;

		if(isset($data['date'])) {
			$img->setDate($data['date']);
		}

		if(isset($data['description'])) {
			$img->setDescription($data['description']);
		}

		if(isset($data['owner'])) {
			$img->setOwner($data['owner']);
		}

		//Tags
		if(isset($data['tags'])) {

			if(!is_array($data['tags'])) {
				$data['tags'] = explode(',', $data['tags']);
			}

			//Limpa dados duplicados do array
			$data['tags'] = array_unique($data['tags']);

			$collTags = new ArrayCollection();
			foreach ($data['tags'] as $tag_name) {
				$tag = $this->getRepository('\Application\Entity\Tag')->findOneBy(array('tag' => $tag_name));
				if(!$tag) {
					$tag = new Tag();
					$tag->setTag($tag_name);
				}
				$collTags->add($tag);
			}
			$img->setTags($collTags);
		} else {
			$img->setTags(new ArrayCollection());
		}

		if(isset($data['location'])) {
			$img->setLocation($data['location']);
		}

		$this->getImageRepository()->update($img);
		return $img;
	}

	public function replaceList($data) {
		if(empty($data)) {
			$this->getJsonModel()->images = array();
			return $this->getJsonModel();
		}

		$arrayUpdate = array();
		foreach ($data[image] as $img) {
			$entity = $this->processUpdate($img, $img['id']);
			if($entity) {
				$arrayUpdate[] = $entity->toArray();
			} else {
				$arrayUpdate[] = "A imagem de id " . $img['id'] . " não foi atualizada";
			}
		}

		$this->getJsonModel()->updated = true;
		$this->getJsonModel()->imagens = $arrayUpdate;
		return $this->getJsonModel();
	}

	public function moveAction() {
		$idAlbum = $this->params()->fromPost('idAlbum');
		$idImage = $this->params()->fromPost('idImage');
		$images  = $this->params()->fromPost('images');

		if(empty($idAlbum) || (empty($idImage) && empty($images)))
			$this->applicationError('Ids não informados!');

		if(!is_array($images))
			$images = array($idImage);

		foreach($images as $idImage)
			$return = $this->move($idAlbum, $idImage);

		return $return;
	}

	public function move($idAlbum, $idImage) {
		if(empty($idAlbum) || empty($idImage))
			$this->applicationError('Ids não informados!');

		$img = $this->getImageRepository()->find($idImage);
		$album = $this->getRepository('Application\Entity\Album')->find($idAlbum);

		$img->setAlbum($album);
		$this->getImageRepository()->update($img);

		$this->getJsonModel()->moved = true;
		return $this->getJsonModel();
	}

	public function copyAction() {
		$idAlbum = $this->params()->fromPost('idAlbum');
		$idImage = $this->params()->fromPost('idImage');
		$images  = $this->params()->fromPost('images');

		if(empty($idAlbum) || (empty($idImage) && empty($images)))
			$this->applicationError('Ids não informados!');

		if(!is_array($images))
			$images = array($idImage);

		foreach($images as $idImage)
			$return = $this->copy($idAlbum, $idImage);

		return $return;
	}

	public function copy($idAlbum, $idImage) {
		if(empty($idAlbum) || empty($idImage))
			$this->applicationError('Ids não informados!');

		$img = $this->getImageRepository()->find($idImage);
		$album = $this->getRepository('Application\Entity\Album')->find($idAlbum);

		$newImg = clone $img;
		$newImg->setId(null);
		$newImg->setAlbum($album);
		$this->getImageRepository()->update($newImg);

		$this->getJsonModel()->copied = true;
		return $this->getJsonModel();
	}

	public function delete($id) {
		$delete = $this->processDelete($id);
		if($delete) {
			$this->getJsonModel()->deleted = true;
			$this->getJsonModel()->images = $delete;
			return $this->getJsonModel();
		} else {
			$this->getJsonModel()->deleted = false;
		}
		return $this->getJsonModel();
	}

	public function deleteList($data=null) {
		$data = $this->processBodyContent($this->getRequest());

		$imagesId = $data['images'];
		if(empty($imagesId)) {
			$this->getJsonModel()->error = true;
			$this->getJsonModel()->message = 'Nenhum parametro informado';
			return $this->getJsonModel();
		}
		$imgDeleted = array();
		foreach ($imagesId as $id) {
			$imgDeleted[] = $this->processDelete($id);
		}

		$this->getJsonModel()->deleted = true;
		$this->getJsonModel()->images = $imgDeleted;
		return $this->getJsonModel();
	}

	public function processDelete($idImage) {
		if(empty($idImage))
			$this->applicationError('Id não informado!');

		$img = $this->getImageRepository()->find($idImage);

		if($img) {
			$this->getImageRepository()->delete($img);

			//Remove a imagem do diretorio
			$find = $this->getRepository('\Application\Entity\Image')->findOneBy(array('filename' => $img->getFileName()));
			if(!$find)
				$this->getImageFilesService()->remove($img->getFileName());

			return $img->toArray(0);
		} else
			return null;
	}

	public function downloadAction() {
		$getParans = $this->params()->fromQuery();
		if(empty($getParans)) {
			$this->getJsonModel()->error = true;
			$this->getJsonModel()->message = 'Nenhum album ou foto informado';
			return $this->getJsonModel();
		}

		$files = array();
		if(key_exists('albumId', $getParans)) {
			//Busca as imagens do usuário visíveis no album
			$images = $this->getRepository('Application\Entity\Image')->getAllByAlbumUser($this->getAuthService()->getIdentity()->getId(), $getParans['albumId']);
		} elseif(key_exists('imagesId', $getParans)) {
			if(is_array($getParans['imagesId'])) {
				foreach ($getParans['imagesId'] as $imgId) {
					$images[] = $this->getRepository('Application\Entity\Image')->find($imgId);
				}
			} elseif(!empty((int) $imgId=$getParans['imagesId'])) {
				$images[] = $this->getRepository('Application\Entity\Image')->find($imgId);
			}
		} else {
			$this->getJsonModel()->error = true;
			$this->getJsonModel()->message = 'Nenhum album ou foto informado';
			return $this->getJsonModel();
		}

		$download = $this->getImageFilesService()->prepareFilesToDownload($images);
		//Log de download
		foreach ($images as $img) {
			$this->getLoggerService()->downloadLogger($img, $this->getAuthService()->getIdentity());
		}

		$this->getJsonModel()->downloadFile = $download;
		return $this->getJsonModel();
	}

	/**
	 *
	 * @return \Application\Repository\Image
	 */
	public function getImageRepository() {
		return $this->getRepository('Application\Entity\Image');
	}

	/**
	 * @return \Application\Controller\AlbumController
	 */
	public function getAlbumController() {
		$albumController  = new AlbumController();
		$albumController->setServiceLocator($this->getServiceLocator());
		return $albumController;
	}
}
