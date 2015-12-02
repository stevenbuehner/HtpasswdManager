<?php

/**
 * @source http://innvo.com/1311865299-htpasswd-manager 
 * @author steven
 *
 */
namespace HtpasswdManager\Service;

class HtpasswdService {
	private $fp;
	private $filename;
	private $notDeletableUsers;
	private $userWithManagementPermission;
	
	// Caching of htpasswd-file
	private $userListCache = null;
	private $htpasswdCache = null;
	
	// Static Variables
	static $REGULAR_USER_PASSWORD = '~^([^:]+):(.+)$~im';

	public function __construct($htpasswd_filename, $notDeletableUsers = array(), $userWithManagementPermission = array()) {
		$this->filename = $htpasswd_filename;
		$this->notDeletableUsers = $notDeletableUsers;
		$this->userWithManagementPermission = $userWithManagementPermission;
		$this->createFileIfNotExistant ();
	}

	private function createFileIfNotExistant() {
		if (true === file_exists ( $this->filename )) {
		} else {
			touch ( $this->filename );
		}
	}

	private function getHtpasswdContent() {
		if ($this->htpasswdCache === null) {
			
			$this->htpasswdCache = file_get_contents ( $this->filename );
		}
		
		return $this->htpasswdCache;
	}

	private function updateHtpasswdContent() {
		$this->htpasswdCache = null;
		$this->userListCache = null;
		$this->getUserList ();
	}

	public function getUserList() {
		if ($this->userListCache === null) {
			$result = array ();
			
			$content = $this->getHtpasswdContent ();
			
			if (preg_match_all ( $this::$REGULAR_USER_PASSWORD, $content, $matches, PREG_PATTERN_ORDER ) !== false) {
				foreach ( $matches [1] as $i => $user ) {
					$result [$user] = $matches [2] [$i];
				}
			}
			
			$this->userListCache = $result;
		}
		
		return $this->userListCache;
	}

	private function encodePassword($password) {
		return crypt ( $password, substr ( str_replace ( '+', '.', base64_encode ( pack ( 'N4', mt_rand (), mt_rand (), mt_rand (), mt_rand () ) ) ), 0, 22 ) );
	}

	private function getNewUserEncodedString($username, $password) {
		return $username . ':' . $this->encodePassword ( $password ) . "\n";
	}

	public function addUser($username, $password) {
		$newContent = $this->getHtpasswdContent ();
		$newContent .= $this->getNewUserEncodedString ( $username, $password );
		
		$this->replaceHtPasswdContent ( $newContent );
	}

	public function updateUser($username, $password) {
		if ($this->userExists ( $username )) {
			$this->deleteUser ( $username );
		}
		
		$this->addUser ( $username, $password );
	}

	public function deleteUser($username) {
		$content = $this->getHtpasswdContent ();
		
		$newContent = '';
		$usernameDeleted = false;
		
		if (preg_match_all ( $this::$REGULAR_USER_PASSWORD, $this->getHtpasswdContent (), $match ) > 0) {
			foreach ( $match [1] as $i => $user ) {
				if ($user == $username) {
					$usernameDeleted = true;
				} else {
					$newContent .= $match [0] [$i] . "\n";
				}
			}
			
			if (true === $usernameDeleted) {
				$this->replaceHtPasswdContent ( $newContent );
			}
		}
		
		return $usernameDeleted;
	}

	private function replaceHtPasswdContent($newContent) {
		// file_put_contents ( $this->filename, $newContent );
		$fp = fopen ( $this->filename, 'w' );
		fwrite ( $fp, $newContent );
		fclose ( $fp );
		
		$this->updateHtpasswdContent ();
	}

	public function userExists($username) {
		$userList = $this->getUserList ();
		
		if (isset ( $userList [$username] ))
			return true;
		
		return false;
	}

	public function isUserDeleteable($username) {
		return ! in_array ( $username, $this->notDeletableUsers );
	}

	public function isUserAllowedToManageUsers($username) {
		/* If parameter is an array => allow only users in list. IF parameter is boolean, return boolean value */
		if (is_array ( $this->userWithManagementPermission )) {
			$accessAllowed = in_array ( $username, $this->userWithManagementPermission );
		} else {
			$accessAllowed = ($this->userWithManagementPermission === true);
		}
		
		return $accessAllowed;
	}
}

?>