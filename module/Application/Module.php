<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface;

class Module {
    public function onBootstrap(MvcEvent $e) {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'mvcPreDispatch'), 100);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), 0);
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'onRenderError'), 0);

        $sm = $e->getApplication()->getServiceManager();

        /* $entityLogger = new \Application\Log\Logger();
        $entityLogger->setServiceManager($sm);

        $doctrineEntityManager = $sm->get('doctrine.entitymanager.orm_default');
        $doctrineEventManager  = $doctrineEntityManager->getEventManager();
        $doctrineEventManager->addEventListener(
            array(
                \Doctrine\ORM\Events::postUpdate,
                \Doctrine\ORM\Events::postRemove,
                \Doctrine\ORM\Events::postPersist,
            ),
            $entityLogger
        ); */
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function init() {

    }

    public function onDispatchError($e) {
    return $this->getJsonModelError($e);
    }

    public function onRenderError($e) {
    return $this->getJsonModelError($e);
    }

    public function getJsonModelError(MvcEvent $e) {
    $error = $e->getError();
    if (!$error) {
        return;
    }

    // if we have a JsonModel in the result, then do nothing
    $currentModel = $e->getResult();
    if ($currentModel instanceof JsonModel) {
        return;
    }

    // create a new JsonModel - use application/api-problem+json fields.
    $response = $e->getResponse();
    $errorArray = array(
        'errors' => array(
        'httpCode' => $response->getStatusCode(),
        'title' => $response->getReasonPhrase(),
        )
    );

    // Find out what the error is
    if ($currentModel instanceof ModelInterface && $currentModel->reason) {
        switch ($currentModel->reason) {
        case 'error-controller-cannot-dispatch':
            $errorArray['errors']['detail'] = 'The requested controller was unable to dispatch the request.';
            break;
        case 'error-controller-not-found':
            $errorArray['errors']['detail'] = 'The requested controller could not be mapped to an existing controller class.';
            break;
        case 'error-controller-invalid':
            $errorArray['errors']['detail'] = 'The requested controller was not dispatchable.';
            break;
        case 'error-router-no-match':
            $errorArray['errors']['detail'] = 'The requested URL could not be matched by routing.';
            break;
        default:
            $errorArray['errors']['detail'] = $currentModel->message;
            break;
        }
    }

    $exception = $e->getParam('exception');
    if ($exception) {
        if ($code = $exception->getCode()) {

        //Verifica se � um c�digo v�lido
        $const = '\Zend\Http\Response' . '::STATUS_CODE_' . $code;
        if (!is_numeric($code) || !defined($const)) {
            $e->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_500);
        } else {
            $e->getResponse()->setStatusCode($code);
        }
        }
        $errorArray['errors']['exception']['message'] = $exception->getMessage();
        $errorArray['errors']['exception']['file'] = $exception->getFile();
        $errorArray['errors']['exception']['line'] = $exception->getLine();

        // find the previous exceptions
        $messages = array();
        while ($exception = $exception->getPrevious()) {
        $messages[] = "* " . $exception->getMessage();
        };
        if (count($messages)) {
        $exceptionString = implode("n", $messages);
        $errorArray['errors']['exception'][]['messages']  = $exceptionString;
        }
    }

    $model = new JsonModel($errorArray);
    $e->setResult($model);
    return $model;
    }

    public function mvcPreDispatch(MvcEvent $e) {
    $auth = $e->getApplication()->getServiceManager()->get('Application\Event\Auth');
    return $auth->preDispatch($e);
    }
}
