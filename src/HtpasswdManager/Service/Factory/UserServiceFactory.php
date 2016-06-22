<?php

namespace HtpasswdManager\Service\Factory;

use HtpasswdManager\Service\UserService;

class UserServiceFactory {

	public function __invoke($sm) {
		$config = $sm->get ( 'Config' );
		
		// Default
		$not_deletable_users = array();
		if (isset ( $config ['HtpasswdManager'] ['not_deletable_users'] ) && is_array ( $config ['HtpasswdManager'] ['not_deletable_users'] )) {
			$not_deletable_users = $config ['HtpasswdManager'] ['not_deletable_users'];
		}
		
		// Default
		$user_with_management_permission = true;
		if (isset ( $config ['HtpasswdManager'] ['usermanagement_users'] ) && is_array ( $config ['HtpasswdManager'] ['usermanagement_users'] )) {
			$user_with_management_permission = $config ['HtpasswdManager'] ['usermanagement_users'];
		} else if ($config ['HtpasswdManager'] ['usermanagement_users'] === false) {
			$user_with_management_permission = false;
		}
		
		$request = $sm->get ( 'Request' );
		
		return new UserService ( $request, $not_deletable_users, $user_with_management_permission );
	}

}
