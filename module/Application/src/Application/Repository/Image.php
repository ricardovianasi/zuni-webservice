<?php
namespace Application\Repository;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Application\Entity\AlbumVisibility;

class Image extends AbstractRepository {

    /**
     * Retorna a lista das imagens que um usuário tem acesso em um determinado álbum
     * @param int $userId
     * @param int $albumId
     * @return array
     */
    public function getAllByAlbumUser($idUser, $idAlbum) {

        $sql = $this->createQueryBuilder('p');
        $sql->select('p')
            ->andWhere('p.user = :idUser')
            ->andWhere('p.album = :idAlbum')
            //->orWhere('p.visibility = ' . ImageVisibility::STATUS_PUBLIC)
            ->setParameters(array(
                'idUser' => $idUser,
                'idAlbum' => $idAlbum
            ));

        return $sql->getQuery()->getResult();
    }

    public function getAllByAlbum($idAlbum) {
        return $this->findBy(array('album'=>$idAlbum));
    }

    public function contImagesAlbum($idAlbum, $idUser=null) {
        return count($this->search(null, $idAlbum, $idUser));
    }

    /**
     * Busca fotos baseaodo nos parâmentros informados
     * @param int|string|array $str
     * @param int $idAlbum
     * @param int $idUser
     * @return array
     */
    public function search($str=null, $idAlbum=null, $idUser=null, $limit=null, $aleatory=false) {

        $sql = "SELECT I.* from images I ";

        //Album
        $sql.= "inner join albums AL on AL.id = I.id_album
                left join albums_tags ALTA on ALTA.id_album = AL.id
                left join tags TAG_ALBUM on TAG_ALBUM.id = ALTA.id_tag ";

        //tags
        $sql.= "left join images_tags IMTA on IMTA.id_image = I.id
                left join tags TAG on TAG.id = IMTA.id_tag OR TAG.id = ALTA.id_tag ";

        //localização
        //$sql.= "left join images_locations IMLO on IMLO.images_id = I.id ";

        $whereUser = "";
        if(!empty($idUser)) {
            /* $sql.= "inner join users USER on USER.id = I.id_user
                    left join `share` SHA on SHA.id_album = AL.id
                    left join share_users SHUS on SHUS.id_share = SHA.id
                    left join share_groups SHUG on SHUG.id_share = SHA.id
                    left join users_groups USGR on USGR.id_group = SHUG.id_group ";

            $whereUser = "USER.id = $idUser
                        OR AL.visibility = 0
                        OR (AL.visibility = 1 AND AL.id_user = $idUser)
                        OR SHUS.id_user = $idUser
                        OR USGR.id_user = $idUser ";*/
            /*$sql.= "inner join users USER on USER.id = I.id_user
                    left join `share` SHA on SHA.id_album = AL.id
                    left join share_users SHUS on SHUS.id_share = SHA.id
                    left join share_groups SHUG on SHUG.id_share = SHA.id
                    left join users_groups USGR on USGR.id_group = SHUG.id_group  ";*/

            $sql.= "inner join users USER on USER.id = I.id_user
                    left join albums_share_users SHUS on SHUS.id_album = AL.id
                    left join albums_share_groups SHUG on SHUG.id_album = AL.id
                    left join users_groups USGR on USGR.id_group = SHUG.id_group ";

            $whereUser = "USER.id = $idUser
                    OR AL.visibility = '".AlbumVisibility::STATUS_PUBLIC."'
                    OR (AL.visibility = '".AlbumVisibility::STATUS_PRIVATE."' AND AL.id_user = $idUser)
                    OR SHUS.id_user = $idUser
                    OR USGR.id_user = $idUser ";
        }

        $sql.= "where 1=1 ";

        if(is_numeric($str)) {
            $sql.= " AND I.id = $str";
        } elseif(is_array($str) || is_string($str)) {
            //palavras
            $sql.= 'AND (' . $this->generateAndStatement('I.description', $str);
            $sql.= 'OR ' . $this->generateAndStatement('I.owner', $str);
            $sql.= 'OR ' . $this->generateAndStatement('TAG.tag', $str);
            $sql.= 'OR ' . $this->generateAndStatement('TAG_ALBUM.tag', $str);
            $sql.= 'OR ' . $this->generateAndStatement('I.location', $str);
            $sql.= 'OR ' . $this->generateAndStatement('AL.name', $str);
            $sql.= 'OR ' . $this->generateAndStatement('AL.description', $str) . ') ';
        }

        if(!empty($whereUser)) {
            $sql.= " AND ($whereUser) ";
        }

        if(!empty($idAlbum)) {
            $sql.= " AND AL.id = $idAlbum ";
        }

        if($aleatory) {
            $sql.= ' ORDER BY RAND() ';
        }

        if(!empty($limit)) {
            $limit = (int) $limit;
            $sql.= " LIMIT $limit ";
        }

        $sql.= "GROUP BY I.id";

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('Application\Entity\Image', 'I');
        $nativeQuery = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        return $nativeQuery->getResult();
    }

    public function generateAndStatement($columnName, $words) {
        $sql = "";

        if (is_array($words)) {
            foreach ($words as $w) {
                if(!empty($sql)) {
                    $sql.= ' and ';
                }
                $sql.= $columnName." like '%$w%'";
            }
        } else {
            $words = (string) $words;
            $sql.= $columnName." like '%$words%'";
        }
        return $sql;
    }

    public function getImageAleatory($idAlbum) {
        $sql = sprintf('select * from images where id_album = %d ORDER by RAND() LIMIT 1', $idAlbum);
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('Application\Entity\Image', 'I');
        $nativeQuery = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        return $nativeQuery->getOneOrNullResult();
    }

	public function getLastImageAlbum($idAlbum)
	{
		return $this
			->getEntityManager()
			->getRepository('Application\Entity\Image')
			->findOneBy(['album'=>$idAlbum], ['createdAt'=>'DESC']);
	}
}