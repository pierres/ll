<?php

define('PATH', './');

require (PATH.'modules/Modul.php');
require (PATH.'modules/Settings.php');
require (PATH.'modules/Exceptions.php');
require (PATH.'modules/Functions.php');
require (PATH.'modules/Io.php');


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
	elseif (file_exists(PATH.'pages/abstract/'.$class . '.php'))
		{
		require(PATH.'pages/abstract/'.$class . '.php');
		}
	else
		{
		die('Konnte Modul "'.$class.'" nicht finden!');
		}
	}

Modul::__set('Settings', new Settings());
$Io = Modul::__set('Io', new Io());


try
	{
	$page = preg_replace('/\W/', '', $Io->getString('page'));

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
	$Io->redirect('Portal', 'forum=1', 1);
	}

?>