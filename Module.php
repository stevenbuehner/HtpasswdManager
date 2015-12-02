<?php

/**
 * Steven Bühner
 * 
 * @copyright Steven Bühner
 * @license MIT
 */
namespace HtpasswdManager;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use HtpasswdManager\Service\HtpasswdService;

class Module implements AutoloaderProviderInterface {

	public function getAutoloaderConfig() {
		return array (
				'Zend\Loader\ClassMapAutoloader' => array (
						__DIR__ . '/autoload_classmap.php' 
				),
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								// if we're in a namespace deeper than one level we need to fix the \ in the path
								__NAMESPACE__ => __DIR__ . '/src/' . str_replace ( '\\', '/', __NAMESPACE__ ) 
						) 
				) 
		);
	}

	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}

	public function onBootstrap(MvcEvent $e) {
		// You may not need to do this if you're doing it elsewhere in your
		// application
		$eventManager = $e->getApplication ()->getEventManager ();
		$moduleRouteListener = new ModuleRouteListener ();
		$moduleRouteListener->attach ( $eventManager );
	}

	public function getServiceConfig() {
		return array (
				'factories' => array (
						'HtpasswdManager\Service\HtpasswdService' => function ($sm) {
							$config = $sm->get ( 'Config' );
							
							if (! isset ( $config ['HtpasswdManager'] ) || ! is_array ( $config ['HtpasswdManager'] ) || ! isset ( $config ['HtpasswdManager'] ['htpasswd'] ) || empty ( $config ['HtpasswdManager'] ['htpasswd'] )) {
								throw new \Exception ( 'HtpasswdManager Config not found' );
							}
							
							// Default
							$not_deletable_users = array ();
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
							
							$htpasswd_filename = $config ['HtpasswdManager'] ['htpasswd'];
							$service = new HtpasswdService ( $htpasswd_filename, $not_deletable_users, $user_with_management_permission );
							
							return $service;
						} 
				) 
		);
	}

}
