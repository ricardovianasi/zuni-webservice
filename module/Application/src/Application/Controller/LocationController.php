<?php
namespace Application\Controller;

class LocationController extends AbstractRestfulJsonController
{
    public function getList() {
        $locations = $this->getLocationRepository()->findAll();
        if(empty($locations)) {
            $this->getJsonModel()->locations = array();
        }
        else {
            $this->getJsonModel()->locations = $this->arrayLisToJson($locations);
        }
        return $this->getJsonModel();
    }

    /**
	 * @return \Application\Repository\Location
	 */
	public function getLocationRepository() {
	    return $this->getRepository('Application\Entity\Location');
	}

}