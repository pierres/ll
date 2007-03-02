<?php

define('IN_LL', null);

require ('modules/Modul.php');
require ('modules/Settings.php');
require ('modules/Exceptions.php');
require ('modules/Functions.php');
require ('modules/Io.php');

Modul::__set('Settings', new Settings());
$Io = Modul::__set('Io', new Io());

function __autoload($class)
	{
	Modul::loadModul($class);
	}

try
	{
	$page = $Io->getString('page');

	try
		{
		Modul::loadModul($page);
		}
	catch (RuntimeException $e)
		{
		$page = 'NotFound';
		Modul::loadModul($page);
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