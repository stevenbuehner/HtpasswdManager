<?php

/**
 * @author Steven Bühner
 * @license MIT
 */
namespace HtpasswdManager\Service;

use Zend\Http\Request;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class UserService implements EventManagerAwareInterface {
	protected $request;
	protected $not_deletable_users;
	protected $user_with_management_permission;
	protected $eventManager;

	public function __construct(Request $request, $not_deletable_users, $user_with_management_permission) {
		$this->request = $request;
		$this->not_deletable_users = $not_deletable_users;
		$this->user_with_management_permission = $user_with_management_permission;
	}

	/**
	 * Return the current used user
	 *
	 * @return string
	 */
	public function getCurrentUser() {
		return $this->request->getServer ()->get ( 'REMOTE_USER', null );
	}

	public function isUserDeleteable($username) {
		$eResult = $this->getEventManager ()->trigger ( 'pre_' . __FUNCTION__, $this, array( 
				$username 
		) );
		
		if ($eResult->stopped ())
			return $eResult->last ();
		
		return ! in_array ( $username, $this->not_deletable_users );
	}

	public function isUserAllowedToManageUsers($username) {
		$eResult = $this->getEventManager ()->trigger ( 'pre_' . __FUNCTION__, $this, array( 
				'user' => $username 
		) );
		
		if ($eResult->stopped ())
			return $eResult->last ();
			
			/* If parameter is an array => allow only users in list. IF parameter is boolean, return boolean value */
		if (is_array ( $this->user_with_management_permission )) {
			$accessAllowed = in_array ( $username, $this->user_with_management_permission );
		} else {
			$accessAllowed = ($this->user_with_management_permission === true);
		}
		
		return $accessAllowed;
	}

	public function setEventManager(EventManagerInterface $eventManager) {
		$eventManager->setIdentifiers ( array( 
				__CLASS__,
				get_class ( $this ) 
		) );
		$this->eventManager = $eventManager;
	}

	public function getEventManager() {
		return $this->eventManager;
	}

}
