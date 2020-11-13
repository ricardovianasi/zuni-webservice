<?php
namespace Application\Controller;

class AuthorController extends AbstractRestfulJsonController
{
    public function getList() {
        $author = $this->geAuthorRepository()->findAll();
        if(empty($author)) {
            $this->getJsonModel()->authors = array();
        }
        else {
            $this->getJsonModel()->authors = $this->arrayLisToJson($author);
        }
        return $this->getJsonModel();
    }

    /**
	 * @return \Application\Repository\Author
	 */
	public function geAuthorRepository() {
	    return $this->getRepository('Application\Entity\Author');
	}

}