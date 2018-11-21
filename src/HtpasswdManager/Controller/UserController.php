<?php

/**
 *
 * @copyright Steven Bühner
 * @license MIT
 */
namespace HtpasswdManager\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController {
    private $htpasswdService = NULL;
    private $userService = NULL;

    /**
     * Check Login
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::onDispatch()
     */
    public function onDispatch(\Zend\Mvc\MvcEvent $e) {
        $username = $this->getUserService()->getCurrentUser();

        if (false === $this->getUserService()->isUserAllowedToManageUsers($username)) {
            $this->getResponse()->setStatusCode(401);

            return;
        }

        return parent::onDispatch($e);
    }

    public function indexAction() {
        $htpasswdService = $this->getHtpasswdService();
        $userList        = $htpasswdService->getUserList();

        $result = array();
        foreach ($userList as $username => $pass) {

            $result [] = array(
                'username'    => $username,
                'paswd'       => $pass,
                'isAdmin'     => $this->getUserService()->isUserAllowedToManageUsers($username),
                'isDeletable' => $this->getUserService()->isUserDeleteable($username)
            );
        }

        $userService = $this->getServiceLocator()->get('HtpasswdManager\Service\UserService');

        $model = new ViewModel (array(
            'userList'    => $result,
            'currentUser' => $userService->getCurrentUser()
        ));

        return $model;
    }

    public function deleteAction() {
        $user     = $this->params('user');
        $messages = array();

        if (true === $this->getUserService()->isUserDeleteable($user)) {
            $this->getHtpasswdService()->deleteUser($user);
            $messages [] = "Der Benutzer '{$user}' wurde gelöscht.";
        } else {
            $messages [] = "Der Benutzer '{$user}' steht auf einer Sperrliste und kann nicht gelöscht werden.";
        }

        $model = $this->indexAction();
        $model->setTemplate('htpasswd-manager/user/index');
        $model->setVariable('messages', $messages);

        return $model;
    }

    public function editAction() {
        $user     = $this->params('user');
        $messages = array();
        $htpasswd = $this->getHtpasswdService();

        $model = new ViewModel ();
        $model->setVariable('user', $user);

        $post = $this->getRequest()->getPost();
        if (!isset ($post ['password'])) {
            // Formular initialisieren
            if (false === $htpasswd->userExists($user)) {
                $this->redirect()->toRoute('htpasswdmanager', array(
                    'action' => 'index'
                ));
            }
        } else {
            // Formular speichern
            $password = $post ['password'];
            $htpasswd->updateUser($user, $password);
            $messages [] = "Passwort für '{$user}' wurde aktualisiert.";

            $model = $this->indexAction();
            $model->setTemplate('htpasswd-manager/user/index');
        }

        $model->setVariable('messages', $messages);

        return $model;
    }

    public function addAction() {
        $username = $this->getRequest()->getPost('username', '');
        $password = $this->getRequest()->getPost('password', '');
        $messages = array();
        $model    = new ViewModel ();

        $post = $this->getRequest()->getPost();
        if (!isset ($post ['username'])) {
            return new ViewModel (array(
                'username' => $username,
                'password' => $password,
                'messages' => $messages
            ));
        }

        $model->setVariable('username', $username);
        $uValid = $this->isUsernameValid($username);
        if (true !== $uValid) {
            $messages [] = $uValid;
        }

        $model->setVariable('password', $password);
        $pValid = $this->isPasswordValid($password);
        if (true !== $pValid) {
            $messages [] = $pValid;
        }

        if (true === $uValid && true === $pValid) {
            if (true === $this->getHtpasswdService()->userExists($username)) {
                $this->getHtpasswdService()->updateUser($username, $password);
                $messages [] = "Benutzer '{$username}' wurde aktualisiert.";
            } else {
                $this->getHtpasswdService()->addUser($username, $password);
                $messages [] = "Benutzer '{$username}' wurde hinzugefügt.";
            }

            $model = $this->indexAction();
            $model->setTemplate('htpasswd-manager/user/index');
        }

        $model->setVariable('messages', $messages);

        return $model;
    }

    /**
     * Returns true if valid, of not it returns a string with information about the reason
     *
     * @param string $username
     * @return boolean string
     */
    private function isUsernameValid($username) {
        if (strlen($username) <= 2)
            return "Benutzername ist zu kurz.";
        else if (preg_match_all('~[a-z][a-z_0-9-]+~i', $username) !== 1)
            return "Benutzername enthält ungültige Zeichen";
        else if (strpos($username, ' ') !== false)
            return "Leerzeichen sind im Benutzernamen nicht erlaubt";

        return true;
    }

    /**
     * Returns true if valid, of not it returns a string with information about the reason
     *
     * @param string $password
     * @return boolean string
     */
    private function isPasswordValid($password) {
        return true;
    }

    private function getHtpasswdService() {
        if ($this->htpasswdService === NULL) {
            $sl                    = $this->getServiceLocator();
            $this->htpasswdService = $sl->get('HtpasswdManager\Service\HtpasswdService');
        }

        return $this->htpasswdService;
    }

    private function getUserService() {
        if ($this->userService === NULL) {
            $sl                = $this->getServiceLocator();
            $this->userService = $sl->get('HtpasswdManager\Service\UserService');
        }

        return $this->userService;
    }

}
