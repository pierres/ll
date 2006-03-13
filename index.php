<?php

define('PATH', './');

require (PATH.'modules/Settings.php');
require (PATH.'modules/Exceptions.php');
require (PATH.'modules/Functions.php');
require (PATH.'modules/Io.php');
require (PATH.'modules/DB.php');
require (PATH.'modules/User.php');
require (PATH.'modules/Board.php');

function __autoload($class)
	{
	if (file_exists(PATH.'modules/'.$class . '.php'))
		{
		require(PATH.'modules/'.$class . '.php');
		}
	elseif (file_exists(PATH.'pages/'.$class . '.php'))
		{
		require(PATH.'pages/'.$class . '.php');
		}
	else
		{
		die('Konnte Modul "'.$class.'" nicht finden!');
		}
	}

Modul::__set('Settings', new Settings());
Modul::__set('Io', new Io());
Modul::__set('DB', new DB(
	Modul::__get('Settings')->getValue('sql_user'),
	Modul::__get('Settings')->getValue('sql_password'),
	Modul::__get('Settings')->getValue('sql_database')
	));
Modul::__set('Board', new Board());
Modul::__set('User', new User());

try
	{
	$page = preg_replace('/\W/', '', Modul::__get('Io')->getString('page'));

	try
		{
		@include(PATH.'pages/'.$page.'.php');
		}
	catch (Exception $e)
		{
		include(PATH.'pages/NotFound.php');
		$page = 'NotFound';
		}

	$class = new $page();
	$class->prepare();
	$class->show();
	}
catch(IoRequestException $e)
	{
	Modul::__get('Io')->redirect('Portal', 'forum=1', 1);
	}

?>