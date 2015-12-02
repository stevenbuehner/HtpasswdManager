<?php
return array (
		'controllers' => array (
				'invokables' => array (
						'HtpasswdManager\Controller\User' => 'HtpasswdManager\Controller\UserController' 
				) 
		),
		'router' => array (
				'routes' => array (
						'htpasswdmanager' => array (
								'type' => 'Segment',
								'options' => array (
										// Change this to something specific to your module
										'route' => '/usermanagement[/:action][/:user]',
										'constraints' => array (
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'user' => '[a-zA-Z0-9.!?_-]+' 
										),
										'defaults' => array (
												// Change this value to reflect the namespace in which
												// the controllers for your module are found
												'__NAMESPACE__' => 'HtpasswdManager\Controller',
												'controller' => 'User',
												'action' => 'index' 
										) 
								),
								'may_terminate' => true 
						) 
				) 
		),
		'view_manager' => array (
				'template_path_stack' => array (
						'HtpasswdManager' => __DIR__ . '/../view' 
				) 
		),
		'HtpasswdManager' => array (
				// Carefull! File needs to be writeable by apache-user (www-data)
				// The .htaccess file needs to be set to use this .htpasswd file for authentication
				'htpasswd' => 'path/to/.htpasswd',
				
				// Users, that can't be deleted with the GUI
				'not_deletable_users' => array (
						'admin'
				),
				
				// May be an array (for specific users) or boolean for general true / false
				'usermanagement_users' => true 
		) 
);
