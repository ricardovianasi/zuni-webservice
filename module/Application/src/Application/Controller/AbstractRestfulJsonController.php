<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class AbstractRestfulJsonController extends AbstractRestfulController
{
    protected $em;
    protected $jsonModel;

    /**
     * @return \Doctrine\Orm\EntityManager
     */
    public function getEntityManager()
    {
        if ($this->em === null)
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        return $this->em;
    }

    /**
     * @param string $entity
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository($entity)
    {
        if (empty($entity))
            return null;

        if (is_object($entity)) {
            $entityName = get_class($entity);
        } else {
            $entityName = $entity;
        }

        return $this->getEntityManager()->getRepository($entityName);
    }

    /**
     * @param $data
     * @param int $depth
     * @param array $blackList
     *
     * @return array|string
     */
    public function arrayLisToJson($data, $depth = 1, $blackList = [])
    {
        if (empty($data))
            return '';

        $jsonData = [];

        foreach ($data as $d) {
            $jsonData[] = $d->toArray($depth, $blackList);
        }

        return ($jsonData);
    }

    /**
     * @throws \Exception
     */
    protected function methodNotAllowed()
    {
        $this->response->setStatusCode(405);

        throw new \Exception('Method '.$this->getRequest()->getMethod().' Not Allowed');
    }

    /**
     * @param $message
     * @param null $statusCode
     *
     * @throws \Exception
     */
    protected function applicationError($message, $statusCode = null)
    {
        if (!empty($statusCode)) {
            $this->response->setStatusCode(\Zend\Http\Response::STATUS_CODE_500);
        }

        $message = empty($message) ? 'Application Error' : $message;

        throw new \Exception($message);
    }

    /**
     * Override default actions as they do not return valid JsonModels
     *
     * @param mixed $data
     *
     * @throws \Exception
     *
     * @return null
     */
    public function create($data)
    {
        $this->methodNotAllowed();
    }

    /**
     * @param mixed $id
     *
     * @throws \Exception
     *
     * @return null
     */
    public function delete($id)
    {
        $this->methodNotAllowed();
    }

    /**
     * @param $data
     *
     * @return mixed|void
     * @throws \Exception
     */
<<<<<<< HEAD
    public function deleteList($data)
=======
    public function deleteList($data=null)
>>>>>>> develop
    {
        $this->methodNotAllowed();
    }

    /**
     * @param mixed $id
     *
     * @throws \Exception
     *
     * @return null
     */
    public function get($id)
    {
        $this->methodNotAllowed();
    }

    /**
     * @throws \Exception
     */
    public function getList()
    {
        $this->methodNotAllowed();
    }

    /**
     * @param null $id
     *
     * @throws \Exception
     *
     * @return null
     */
    public function head($id = null)
    {
        $this->methodNotAllowed();
    }

    /**
     * @return JsonModel
     */
    public function options()
    {
        //$this->methodNotAllowed();
        $this->getJsonModel()->ok = 'ok';

        return $this->getJsonModel();
    }

    /**
     * @param $id
     * @param $data
     *
     * @throws \Exception
     *
     * @return null
     */
    public function patch($id, $data)
    {
        $this->methodNotAllowed();
    }

    /**
     * @param mixed $data
     *
     * @throws \Exception
     *
     * @return null
     */
    public function replaceList($data)
    {
        $this->methodNotAllowed();
    }

    /**
     * @param mixed $data
     *
     * @throws \Exception
     *
     * @return null
     */
    public function patchList($data)
    {
        $this->methodNotAllowed();
    }

    /**
     * @param mixed $id
     * @param mixed $data
     *
     * @throws \Exception
     *
     * @return null
     */
    public function update($id, $data)
    {
        $this->methodNotAllowed();
    }

    /**
     * @param $url
     *
     * @return bool
     */
    protected function loadPageCache($url)
    {
        if (empty($url)) {
            return false;
        }

        return true;
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getResponseWithHeader()
    {
        $response = $this->getResponse();
        $response->getHeaders()
            //make can accessed by *
            ->addHeaderLine('Access-Control-Allow-Origin', '*')
            //set allow methods
            ->addHeaderLine('Access-Control-Allow-Methods', 'POST PUT DELETE GET');

        return $response;
    }

    /**
     * @return JsonModel
     */
    public function getJsonModel()
    {
        if (!$this->jsonModel)
            $this->jsonModel = new JsonModel();
        if ($this->getAuthService()->hasIdentity()) {
            $this->jsonModel->user = $this->getAuthService()->getIdentity()->toArray(1, []);
        }

        return $this->jsonModel;
    }

    /**
     * @return \Application\Service\Auth
     */
    public function getAuthService()
    {
        return $this->getServiceLocator()->get('auth');
    }

    /**
     * @return \ImageManipulation\Service\ImageFiles
     */
    public function getImageFilesService()
    {
        return $this->getServiceLocator()->get('imageFiles');
    }

    /**
     * @param null $name
     *
     * @return array|null
     */
    public function getZuniConfig($name = null)
    {
        $config = $this->getServiceLocator()->get('Configuration');

        if (!isset($config['zuni'])) {
            return [];
        }

        if (!empty($name)) {
            if (isset($config['zuni'][$name])) {
                return $config['zuni'][$name];
            }

            return null;
        }

        return $config['zuni'];
    }

    /**
     * @param $error
     *
     * @return JsonModel
     * @throws \Exception
     */
    public function formatErrorMessage($error)
    {
        if (is_string($error)) {
            $this->getJsonModel()->error = [
                'message' => $error
            ];
        } elseif ($error instanceof \Exception) {
            $this->getJsonModel()->error = [
                'message' => $error->getMessage()
            ];
        } else {
            $this->applicationError('Error');
        }

        return $this->getJsonModel();
    }

    /**
     *
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
    }

    /**
     * @return \Application\Log\Logger
     */
    public function getLoggerService()
    {
        return $this->getServiceLocator()->get('logger');
    }
}
