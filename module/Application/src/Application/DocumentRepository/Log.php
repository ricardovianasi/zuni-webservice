<?php
namespace Application\DocumentRepository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Application\Document\Log;

class Log extends DocumentRepository
{
	public function getImageDownloadCont($imageId) {
		return $this->createQueryBuilder()
			->addAnd(array('type'=>Log::TYPE_DOWNLOAD))
			->addAnd(array('targetId'=>$imageId))
			->getQuery()
			->getSingleResult();
	}
}