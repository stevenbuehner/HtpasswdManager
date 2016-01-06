<?php

/**
 * @author Steven Bühner
 * @license MIT
 */
namespace HtpasswdManager\Service;

use Zend\Http\Request;

class UserService {
	private $request;

	public function __construct(Request $request) {
		$this->request = $request;
	}

	/**
	 * Return the current used user
	 *
	 * @return string
	 */
	public function getCurrentUser() {
		return $this->request->getServer ()->get ( 'REMOTE_USER', 'NoUser' );
	}

}

?>