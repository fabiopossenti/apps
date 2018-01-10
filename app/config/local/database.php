<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/

	'connections' => array(

		'mysql' => array(
			'driver'    => 'mysql',
			'host'      => 'localhost',
			'database'  => 'herbiee',
			'username'  => 'root',
			'password'  => '1234',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		),
			
			//esse é o oficial
// 		'mysql' => array(
// 				'driver'    => 'mysql',
// 				'host'      => 'mysql.hostinger.com.br',
// 				'database'  => 'u875549567_flane',
// 				'username'  => 'u875549567_root',
// 				'password'  => 'pos2008',
// 				'charset'   => 'utf8',
// 				'collation' => 'utf8_unicode_ci',
// 				'prefix'    => '',
// 		),			

			//16mb.com
// 		'mysql' => array(
// 				'driver'    => 'mysql',
// 				'host'      => 'mysql.hostinger.com.br',
// 				'database'  => 'u349673643_flane',
// 				'username'  => 'u349673643_root',
// 				'password'  => 'pos2008',
// 				'charset'   => 'utf8',
// 				'collation' => 'utf8_unicode_ci',
// 				'prefix'    => '',
// 		),			
			
// 		'mysql' => array(
// 			'driver'    => 'mysql',
// 			'host'      => 'mysql.hostinger.com.br',
// 			'database'  => 'u410379731_herbi',
// 			'username'  => 'u410379731_root',
// 			'password'  => 'pos2008',
// 			'charset'   => 'utf8',
// 			'collation' => 'utf8_unicode_ci',
// 			'prefix'    => '',
// 		),			

		'pgsql' => array(
			'driver'   => 'pgsql',
			'host'     => 'localhost',
			'database' => 'homestead',
			'username' => 'homestead',
			'password' => 'secret',
			'charset'  => 'utf8',
			'prefix'   => '',
			'schema'   => 'public',
		),

	),

);
