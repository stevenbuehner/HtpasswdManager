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
	private $htpasswdService = null;

	/**
	 * Check Login
	 *
	 * @see \Zend\Mvc\Controller\AbstractActionController::onDispatch()
	 */
	public function onDispatch(\Zend\Mvc\MvcEvent $e) {
		$username = $this->request->getServer ()->get ( 'REMOTE_USER', 'NoUser' );
		
		if (false === $this->getHtpasswdService ()->isUserAllowedToManageUsers ( $username )) {
			
			$this->getResponse ()->setStatusCode ( 401 );
			return;
		}
		
		return parent::onDispatch ( $e );
	}

	function indexAction() {
		$htpasswd = $this->getHtpasswdService ();
		$userList = $htpasswd->getUserList ();
		
		$result = array ();
		foreach ( $userList as $username => $pass ) {
			$result [] = array (
					'username' => $username,
					'paswd' => $pass,
					'isAdmin' => $htpasswd->isUserAllowedToManageUsers ( $username ),
					'isDeletable' => $htpasswd->isUserDeleteable ( $username ) 
			);
		}
		
		$model = new ViewModel ( array (
				'userList' => $result 
		) );
		
		return $model;
	}

	public function deleteAction() {
		$user = $this->params ( 'user' );
		$messages = array ();
		
		if (true === $this->getHtpasswdService ()->isUserDeleteable ( $user )) {
			$this->getHtpasswdService ()->deleteUser ( $user );
			$messages [] = "Der Benutzer '{$user}' wurde gelöscht.";
		} else {
			$messages [] = "Der Benutzer '{$user}' steht auf einer Sperrliste und kann nicht gelöscht werden.";
		}
		
		$model = $this->indexAction ();
		$model->setTemplate ( 'htpasswd-manager/user/index' );
		$model->setVariable ( 'messages', $messages );
		
		return $model;
	}

	public function editAction() {
		$user = $this->params ( 'user' );
		$messages = array ();
		$htpasswd = $this->getHtpasswdService ();
		
		$model = new ViewModel ();
		$model->setVariable ( 'user', $user );
		
		$post = $this->getRequest ()->getPost ();
		if (! isset ( $post ['password'] )) {
			// Formular initialisieren
			if (false === $htpasswd->userExists ( $user )) {
				$this->redirect ()->toRoute ( 'htpasswdmanager', array (
						'action' => 'index' 
				) );
			}
		} else {
			// Formular speichern
			$password = $post ['password'];
			$htpasswd->updateUser ( $user, $password );
			$messages [] = "Passwort für '{$user}' wurde aktualisiert.";
			
			$model = $this->indexAction ();
			$model->setTemplate ( 'htpasswd-manager/user/index' );
		}
		
		$model->setVariable ( 'messages', $messages );
		return $model;
	}

	public function addAction() {
		$username = $post = $this->getRequest ()->getPost ( 'username', '' );
		$password = $post = $this->getRequest ()->getPost ( 'password', '' );
		$messages = array ();
		$model = new ViewModel ();
		
		$post = $this->getRequest ()->getPost ();
		if (! isset ( $post ['username'] )) {
			return new ViewModel ( array (
					'username' => $username,
					'password' => $password,
					'messages' => $messages 
			) );
		}
		
		$model->setVariable ( 'username', $username );
		$uValid = $this->isUsernameValid ( $username );
		if (true !== $uValid) {
			$messages [] = $uValid;
		}
		
		$model->setVariable ( 'password', $password );
		$pValid = $this->isPasswordValid ( $password );
		if (true !== $pValid) {
			$messages [] = $pValid;
		}
		
		if (true === $uValid && true === $pValid) {
			if (true === $this->getHtpasswdService ()->userExists ( $username )) {
				$this->getHtpasswdService ()->updateUser ( $username, $password );
				$messages [] = "Benutzer '{$username}' wurde aktualisiert.";
			} else {
				$this->getHtpasswdService ()->addUser ( $username, $password );
				$messages [] = "Benutzer '{$username}' wurde hinzugefügt.";
			}
			
			$model = $this->indexAction ();
			$model->setTemplate ( 'htpasswd-manager/user/index' );
		}
		
		$model->setVariable ( 'messages', $messages );
		return $model;
	}

	/**
	 * Returns true if valid, of not it returns a String with information about the reason
	 *
	 * @param string $username        	
	 * @return boolean string
	 */
	private function isUsernameValid($username) {
		if (strlen ( $username ) <= 2)
			return "Benutzername ist zu kurz.";
		else if (preg_match ( '~[a-zäöo][a-zäöu_0-9-]+~i', $username ) !== 1)
			return "Benutzername enthält ungültige Zeichen";
		else if (strpos ( $username, ' ' ) !== false)
			return "Leerzeichen sind im Benutzernamen nicht erlaubt";
		return true;
	}

	/**
	 * Returns true if valid, of not it returns a String with information about the reason
	 *
	 * @param string $username        	
	 * @return boolean string
	 */
	private function isPasswordValid($password) {
		return true;
	}

	private function getHtpasswdService() {
		if ($this->htpasswdService === null) {
			$sl = $this->getServiceLocator ();
			$this->htpasswdService = $sl->get ( 'HtpasswdManager\Service\HtpasswdService' );
		}
		
		return $this->htpasswdService;
	}

}
