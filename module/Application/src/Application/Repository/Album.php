<?php
namespace Application\Repository;

use Application\Entity\AlbumVisibility;
class Album extends AbstractRepository {

    /**
     * Busca lista de todos os albuns que o usuario tem acesso. Se o idUser for omitido, todos os albuns serão retornados
     * @param int $idUser (opicional)
     */
    public function findAll($idUser=null) {
        $sql = $this->createQueryBuilder('a');
        $sql->select('a');
         /*if(!empty($idUser)) {
            $sql->leftJoin('Application\Entity\Share', 'sh', 'WITH', 'sh.album = a.id')
                ->leftJoin('sh.users', 'shu')
                ->leftJoin('sh.groups', 'shg')
                ->where('a.visibility = :public OR (a.visibility = :private AND a.user = :idUser) OR shu.id = :idUser')
                ->setParameter('idUser', $idUser)
                ->setParameter('public', "public")
                ->setParameter('private', "private");
        }*/

        if(!empty($idUser)) {
            $sql->leftJoin('a.shareUsers', 'shu')
                //->leftJoin('a.shareGroups', 'shg')
                ->where('a.visibility = :public OR (a.visibility = :private AND a.user = :idUser) OR shu.id = :idUser')
                ->setParameter('idUser', $idUser)
                ->setParameter('public', "public")
                ->setParameter('private', "private");
        }

        $sql->orderBy('a.name', 'ASC');
        $query  = $sql->getQuery()->getSQL();
        return $sql->getQuery()->getResult();
    }

    public function findPublicByName($name) {
        $sql = $this->createQueryBuilder('a');
        $sql->select('count(a)')
            ->andWhere('a.visibility = :visibility')
            ->setParameter('visibility', AlbumVisibility::STATUS_PUBLIC)
            ->andWhere('a.name = :name')
            ->setParameter('name', $name);

        return $sql->getQuery()->getSingleScalarResult();
    }

    /**
     * Busca um album especifico
     * @return NULL | \Application\Entity\Album
     */
    public function find($id, $idUser=null) {
        $sql = $this->createQueryBuilder('a');
        $sql->select('a')
            ->andWhere('a.id = :idAlbum')
            ->setParameter('idAlbum', $id);

        /*if(!empty($idUser)) {
            $sql->leftJoin('Application\Entity\Share', 'sh', 'WITH', 'sh.album = a.id')
                ->leftJoin('sh.users', 'shu')
                ->leftJoin('sh.groups', 'shg')
                ->andWhere('(a.visibility = :public OR (a.visibility = :private AND a.user = :idUser) OR shu.id = :idUser)')
                ->setParameter('idUser', $idUser)
                ->setParameter('public', "public")
                ->setParameter('private', "private");
        }*/

        if(!empty($idUser)) {
            $sql->leftJoin('a.shareUsers', 'shu')
                //->leftJoin('a.shareGroups', 'shg')
                ->andWhere('(a.visibility = :public OR (a.visibility = :private AND a.user = :idUser) OR shu.id = :idUser)')
                ->setParameter('idUser', $idUser)
                ->setParameter('public', "public")
                ->setParameter('private', "private");
        }

        return $sql->getQuery()->getOneOrNullResult();
    }
}