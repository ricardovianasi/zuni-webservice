<?php
namespace Application\Controller;

use Application\Entity\PersistentLogin;
use Zend\View\Model\JsonModel;

class AuthController extends AbstractRestfulJsonController {

    public function getList() {
        if($this->getAuthService()->hasIdentity()) {
            $this->getJsonModel()->user = $this->getAuthService()->getIdentity()->toArray();
        }

        return $this->getJsonModel();
    }

    public function loginAction() {
        $request = $this->getRequest();
        if(!$request->isPost()) {
            $this->getJsonModel()->login = array(
                'error' => 'true',
                'message' => 'Login ou senha não informado.'
            );
            return $this->getJsonModel();
        }

        $login = $this->params()->fromPost('email', null);
        $pass = $this->params()->fromPost('password', null);
        if(empty($login) || empty($pass)) {
            $this->getJsonModel()->login = array(
                'error' => 'true',
                'message' => 'Login ou senha não informado.'
            );
            return $this->getJsonModel();
        }

        $this->getAuthService()->getAdapter()->setIdentityValue($login);
        $this->getAuthService()->getAdapter()->setCredentialValue($pass);

        //Efetua a validação do usuário
        $result = $this->getAuthService()->authenticate();
        if(!$result->isValid()) {
            $jsonModel = new JsonModel();
            $jsonModel->login = array(
                'error' => true,
                'message' => $result->getMessages()
            );
            return $jsonModel;
        }

        //Exclui todos os tokens já criados pelo usuário
        $this->getRepository('Application\Entity\PersistentLogin')->deleteByUser($result->getIdentity()->getId());

        $persistentLogin = new PersistentLogin();
        $persistentLogin->setToken($result->getSerial());
        $persistentLogin->setUser($result->getIdentity());
        $validUntil = new \DateTime();
        $remenberSection = $this->params()->fromPost('remenberSession', null);
        if($remenberSection) {
            $validUntil->add(new \DateInterval('P'.$this->getZuniConfig('max_time_persistent_connection').'H'));
        } else {
            $validUntil->add(new \DateInterval('PT'.$this->getZuniConfig('max_time_connection').'H'));
            //$validUntil->add(new \DateInterval('P89D'));
        }

        $persistentLogin->setValidUntil($validUntil);
        $this->getEntityManager()->persist($persistentLogin);
        $this->getEntityManager()->flush();

        $this->getJsonModel()->login = true;
        $this->getJsonModel()->token = $result->getToken();
        $this->getJsonModel()->user = $result->getIdentity()->toArray(1, array());

        //Log
        //$this->getLoggerService()->loginLogger($result->getIdentity());

        return $this->getJsonModel();
    }

    public function logoutAction() {

        if(!$this->getAuthService()->isAuthenticated()) {
            $this->getJsonModel()->externalAccess = array(
                'error' => 'true',
                'message' => 'Nenhum usuário logado'
            );
            return $this->getJsonModel();
        }

        $user = $this->getAuthService()->getIdentity();

        $this->getAuthService()->logout();
        $this->getJsonModel()->logout = true;

        //Log
        //$this->getLoggerService()->loginLogger($user);

        $this->getJsonModel()->logout = 'ok';
        return $this->getJsonModel();
    }

    public function testAction() {
        return $this->getJsonModel();
    }

    public function validateExternalAcessAction() {
        $hash = $this->params()->fromQuery('hash');
        if(empty($hash)) {
            $this->getJsonModel()->externalAccess = array(
                'error' => 'true',
                'message' => 'Hash não informada'
            );
            return $this->getJsonModel();
        }

        if(!$this->getAuthService()->authenticateExternalAccess($hash)) {
            $this->getJsonModel()->externalAccess = array(
                'error' => true,
                'message' => 'Acesso Negado. A hash não é válida'
            );
            return $this->getJsonModel();
        }

        $this->getJsonModel()->externalAcess = true;
        return $this->getJsonModel();
    }
}