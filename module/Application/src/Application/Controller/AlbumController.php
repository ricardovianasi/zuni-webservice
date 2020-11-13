<?php

namespace Application\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\AlbumVisibility;
use Application\Entity\Tag;
use Application\Entity\Location;
use Application\Entity\Author;

class AlbumController extends AbstractRestfulJsonController {
    /**
     * The default action - show the home page
     */
    public function getList() {

        //Verifica se tem permissão de super(admin)
        if($this->getAuthService()->authorize('album', 'getListSuper'))
            $list = $this->getAlbumRepository()->findAll();
        else
            $list = $this->getAlbumRepository()->findAll($this->getAuthService()->getIdentity()->getId());

        if(!$list)
            $this->getJsonModel()->albums = array();
        else $this->getJsonModel()->albums = $this->arrayLisToJson($list, 1);

        return $this->getJsonModel();
    }

    public function get($id) {
        if(empty($id))
            $this->applicationError('Id não informado!');

        //Verifica se tem permissão para acessar o album específico
        if($this->getAuthService()->authorize('album', 'getSuper')) {
            $album = $this->getAlbumRepository()->find($id);
        } else {
            $album = $this->getAlbumRepository()->find($id, $this->getAuthService()->getIdentity()->getId());
        }
        if($album) {
            $albumArray = $album->toArray(2);
            $this->getJsonModel()->albums = $albumArray;
        } else {
            $this->getJsonModel()->error = true;
            $this->getJsonModel()->menssage = 'Album não foi encontrado ou você não tem acesso ao mesmo.';
            return $this->getJsonModel();
        }

        return $this->getJsonModel();
    }

    public function getAlbumExternalShareAction() {

        $user = $this->getAuthService()->getIdentity();

        //Verifica se o usuário logado é de acesso externo
        if(!$user->getProfiles()->getExternalProfile()) {
            $this->getJsonModel()->albums = array();
            return $this->getJsonModel();
        }

        //Verifica a hash de acesso externo e recupera o album de acesso
        if(!$share = $this->getRepository('Application\Entity\Share')->validateHashExternalAcess($user->getAccessKey())) {
            $this->getJsonModel()->albums = array();
            return $this->getJsonModel();
        }

        $album = $this->getRepository('Application\Entity\Album')->find($share->getAlbum()->getId());
        if($album)
            $this->getJsonModel()->albums = $album->toArray(2);
        else $this->getJsonModel()->albums = array();

        return $this->getJsonModel();
    }

    public function create($data) {
        return $this->processUpdate($data);
    }

    public function update($id, $data) {
        return $this->processUpdate($data, $id);
    }

    protected function processUpdate($data, $id=null) {
        try {

            $this->getEntityManager()->beginTransaction();

            //Busca um album existente ou cria um novo álbum
            if(isset($data['album'])) {
                $album = $this->save($data['album'], $id);
            } elseif(!empty($id)) {
                $album = $this->getAlbumRepository()->find($id);
            }

            $images = array();
            if(isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $img) {

                    if(isset($img['id'])) {
                        $entity = $this->getImageRepository()->find($img['id']);
                        if(!$entity) {
                            throw new \Exception('Imagem não encontrada');
                        }
                    } else {
                        $imgPath = $this->getImageFilesService()->getTmpDirImage($img['filename']);
                        if(!is_writable($imgPath)){
                            throw new \Exception('O arquivo não pode ser acessado');
                        }

                        $entity = new \Application\Entity\Image();
                        $entity->setAlbum($album);
                        $entity->setFilename($img['filename']);
                        $entity->setExtension($this->getImageFilesService()->getFileExtension($imgPath));
                        $entity->setWidth($this->getImageFilesService()->getImageSize($imgPath)['width']);
                        $entity->setHeight($this->getImageFilesService()->getImageSize($imgPath)['height']);
                    }


                    if(!empty($img['date'])) $entity->setDate(\DateTime::createFromFormat('d/m/Y', $img['date']));
                    $entity->setDescription($img['description']);

                    if(isset($img['location']) && !empty($img['location'])) {
                        $location = $this->getLocationRepository()->findOneBy(array('name'=>$img['location']));
                        if(!$location) {
                            $location = new Location();
                            $location->setName($img['location']);
                        }
                        $entity->setLocation($location);
                    }

                    $entity->setUser($this->getAuthService()->getIdentity());

                    $ownerName = empty($img['owner']) ? null : $img['owner'];
                    if($ownerName) {
                        $owner = $this->getAuthorRepository()->findOneBy(array('name'=>$ownerName));
                        if(!$owner) {
                            $owner = new Author();
                            $owner->setName($ownerName);
                        }
                        $entity->setOwner($owner);
                    }

                    //Tags
                    if(isset($img['tags'])) {

                        if(!is_array($img['tags'])) {
                            $img['tags'] = explode(',', $img['tags']);
                        }

                        //Limpa dados duplicados do array
                        $img['tags'] = array_unique($img['tags']);

                        $collTags = new ArrayCollection();
                        foreach ($img['tags'] as $tag_name) {
                            if(!empty($tag_name)) {
                                $tag = $this->getRepository('\Application\Entity\Tag')->findOneBy(array('tag' => $tag_name));
                                if(!$tag) {
                                    $tag = new Tag();
                                    $tag->setTag($tag_name);
                                }
                                $collTags->add($tag);
                            }
                        }
                        $entity->setTags($collTags);
                    } /* else {
                        $entity->setTags(new ArrayCollection());
                    } */

                    if(!isset($img['id'])) {

                        //Limpa os metadados da imagem
                        //$this->getImageFilesService()->setDefaultMetada($imgPath);

                        //Move o arquivo para o diretório definitivo
                        $this->getImageFilesService()->moveDefinitiveDirectory($img['filename']);
                    }

                    //Salva a imagem no banco
                    $this->getImageRepository()->update($entity);

                    //Monta o json de retorno
                    $images[] = $entity->toArray();
                }
            }

            $this->getEntityManager()->commit();

            $this->getJsonModel()->album = $album->toArray();
            $this->getJsonModel()->images = $images;
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            $this->getJsonModel()->error = array(
                'message' => $e->getMessage()
            );
            return $this->getJsonModel();
        }

        return $this->getJsonModel();
    }

    public function delete($id) {
        try {
            $this->getEntityManager()->beginTransaction();

            $album = $this->getAlbumRepository()->find($id);
            if(!$album) {
                throw new \Exception('Album não encontrado');
            } elseif($album->getVisibility() == AlbumVisibility::STATUS_PUBLIC && $album->getImageCount() > 0) {
                throw new \Exception('Album público só pode ser deletado se estiver vazio');
            } elseif($album->getVisibility() == AlbumVisibility::STATUS_PRIVATE && $album->getUser()->getId() != $this->getAuthService()->getIdentity()->getId()) {
                //Album for privado, somente o autor ou o perfis master podem deletar
                if(!$this->getAuthService()->authorize('album', 'deleteSuper')) {
                    throw new \Exception('Você não tem permissão para deletar este album.');
                }
            }

            $this->getAlbumRepository()->delete($album);
            $this->getEntityManager()->commit();

            $this->getJsonModel()->deleted = true;
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            return $this->formatErrorMessage($e->getMessage());
        }

        return $this->getJsonModel();
    }

    /**
     * @param array $data
     * @param null $id
     * @return \Application\Entity\Album|null|object
     * @throws \Exception
     */
    public function save(array $data, $id=null) {
        if(empty($data))
            $this->applicationError('Dados não informados');

        $user = $this->getAuthService()->getIdentity();

        if(empty($id)) {
            //Album público não pode ficar com nome duplicados
            if((!isset($data['visibility']) || (isset($data['visibility']) && $data['visibility'] == AlbumVisibility::STATUS_PUBLIC))
                && $this->getAlbumRepository()->findPublicByName($data['name'])) {
                $this->applicationError("O nome do álbum já está sendo usado.");
            }

            if(!isset($data['name']) || empty($data['name'])) {
                $this->applicationError("O nome do álbum não pode ficar em branco.");
            }

            $album = new \Application\Entity\Album($data);
            $album->setUser($this->getAuthService()->getIdentity());
            $album->setCreatedAt(new \DateTime());
        } else {
            if($this->getAuthService()->authorize('album', 'updateSuper')) {
                $album = $this->getAlbumRepository()->find($id);
                if(empty($album)) {
                    $this->applicationError("O album não foi encontrado.");
                }

                //Caso o administrador tente alterar a visibilidado do album público para privado
                if(isset($data['visibility'])
                        && $data['visibility'] == AlbumVisibility::STATUS_PRIVATE
                        && $album->getVisibility() == AlbumVisibility::STATUS_PUBLIC) {

                    $album->setUser($this->getAuthService()->getIdentity());
                    //TODO: Limpar todos os compartilhamentos
                }
            } else {
                $album = $this->getAlbumRepository()->find($id, $this->getAuthService()->getIdentity()->getId());
                if(empty($album)) {
                    $this->applicationError("O album não foi encontrado ou você não tem acesso ao mesmo.");
                }
            }

            //Validar Formulário
            if(isset($data['name']) && empty($data['name'])) {
                $this->applicationError("O nome do álbum não pode ficar em branco.");
            }

            if($album->getVisibility() == AlbumVisibility::STATUS_PUBLIC && $album->getName() != $data['name'] && $this->getAlbumRepository()->findPublicByName($data['name'])) {
                $this->applicationError("O nome do álbum já está sendo usado.");
            }

            $album->setData($data);
        }

        //Location
        if(isset($data['location']) && !empty($data['location'])) {
            $location = $this->getLocationRepository()->findOneBy(array('name'=>$data['location']));
            if(!$location) {
                $location = new Location();
                $location->setName($data['location']);
            }
            $album->setLocation($location);
        }

        //Se o album for público, não pode haver outro album de mesmo nome
        if(isset($data['tags'])) {

            if(!is_array($data['tags'])) {
                $data['tags'] = explode(',', $data['tags']);
            }

            //Limpa dados duplicados do array
            $data['tags'] = array_unique($data['tags']);

            $collTags = new ArrayCollection();
            foreach ($data['tags'] as $tag_name) {
                if(!empty($tag_name)) {
                    $tag = $this->getRepository('\Application\Entity\Tag')->findOneBy(array('tag' => $tag_name));
                    if(!$tag) {
                        $tag = new Tag();
                        $tag->setTag($tag_name);
                    }
                    $collTags->add($tag);
                }
            }
            $album->setTags($collTags);
        } /* else {
            $album->setTags(new ArrayCollection());
        } */

        if(isset($data['share']) && !empty($data['share']) && $album->getVisibility() == AlbumVisibility::STATUS_PRIVATE) {

            if($album->getVisibility() == AlbumVisibility::STATUS_PUBLIC) {
                throw new \Exception('Albuns públicos não precisam ser compartilhados.');
            }

            /* if($album->getUser()->getId() != $this->getAuthService()->getIdentity()->getId() && !$this->getAuthService()->getIdentity()->hasAdministratorProfile()) {
                throw new \Exception('Você não pode alterar o compartilhamento deste album.');
            } */

            if(isset($data['share']) && is_array($data['share'])) {
                $collUsers = new ArrayCollection();
                if(key_exists('users', $data['share']) && is_array($data['share']['users'])) {
                    foreach ($data['share']['users'] as $id) {
                        $collUsers->add($this->getRepository('Application\Entity\User')->find($id));
                    }
                }
                $album->setShareUsers($collUsers);

                $collGroups = new ArrayCollection();
                if(key_exists('groups', $data['share']) && is_array($data['share']['groups'])) {
                    foreach ($data['share']['groups'] as $id) {
                        $collGroups->add($this->getRepository('Application\Entity\Group')->find($id));
                    }
                }
                $album->setShareGroups($collGroups);
            }
        }

        $this->getRepository('\Application\Entity\Album')->update($album);

        return $album;
    }

    public function changeVisibilityAction() {
        $idAlbum = $this->params()->fromQuery('idAlbum', null);

        if($this->getAuthService()->authorize('album', 'changeVisibilitySuper')) {
            $album = $this->getRepository('Application\Entity\Album')->find($idAlbum);
        } else {
            $album = $this->getRepository('Application\Entity\Album')->find($idAlbum, $this->getAuthService()->getIdentity()->getId());
        }

        if(empty($album)) {
            $this->getJsonModel()->error = true;
            $this->getJsonModel()->menssage = 'Album não encontrado';
            return $this->getJsonModel();
        }

        //Somente album privado podem virar publico
        //Somente o dono do album ou niveis mais altos podem transformar albuns privadas em publicas

        if($album->getVisibility() == AlbumVisibility::STATUS_PUBLIC) {
            if($this->getAuthService()->authorize('album', 'changeVisibilitySuper')) {
                $user = $this->getRepository('Application\Entity\User')->find($this->getAuthService()->getIdentity()->getId());
                $album->setUser($user);
            } else {
                //transformar em privado
                $this->getJsonModel()->error = true;
                $this->getJsonModel()->menssage = 'Álbuns públicos não podem virar privados';
                return $this->getJsonModel();
            }
        }

        //Muda a visibilidade do album
        //Verifica se existe um album publico com o mesmo nome
        if($this->getAlbumRepository()->findPublicByName($album->getName())) {
            $this->getJsonModel()->error = true;
            $this->getJsonModel()->menssage = 'Já existe um album público com o mesmo nome';
            return $this->getJsonModel();
        }

        $album->setVisibility(AlbumVisibility::STATUS_PUBLIC);
        $this->getAlbumRepository()->update($album);

        //Remove os compartilhamentos
        $shares = $this->getRepository('Application\Entity\Share')->findShareByAlbum($album->getId());
        if($shares) {
            foreach ($shares as $share) {
                if(empty($share->getExternal())) {
                    $this->getRepository('Application\Entity\Share')->delete($share);
                } else {
                    $share->setUsers(new ArrayCollection());
                    $share->setGroups(new ArrayCollection());
                    $this->getRepository('Application\Entity\Share')->update($share);
                }
            }
        }

        $this->getJsonModel()->albumUpdated = true;
        $this->getJsonModel()->album = $album->toArray();
        return $this->getJsonModel();
    }

    /**
     *
     * @return \Application\Repository\Album
     */
    protected function getAlbumRepository() {
        return $this->getRepository('Application\Entity\Album');
    }

    public function getImageRepository() {
        return $this->getRepository('Application\Entity\Image');
    }

    /**
     * @return \Application\Repository\Location
     */
    public function getLocationRepository() {
        return $this->getRepository('Application\Entity\Location');
    }

    /**
     * @return \Application\Repository\Author
     */
    public function getAuthorRepository() {
        return $this->getRepository('Application\Entity\Author');
    }

    /**
     *
     * @return \Application\Repository\Share
     */
    public function getShareRepository() {
        return $this->getRepository('Application\Entity\Share');
    }
}