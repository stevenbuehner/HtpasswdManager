[![Build Status](https://scrutinizer-ci.com/g/stevenbuehner/HtpasswdManager/badges/build.png?b=master)](https://scrutinizer-ci.com/g/stevenbuehner/HtpasswdManager/build-status/master)

# zf2-htpasswd-module
A ZendFramework 2 Module to manage users in a htpasswd file with basic authentication

# Default Configuration
```
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
```
