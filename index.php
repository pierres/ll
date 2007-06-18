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
		Page::loadPage($page);
		}
	catch (RuntimeException $e)
		{
		$page = 'NotFound';
		Page::loadPage($page);
		}

	$class = new $page();
	$class->prepare();
	$class->show();
	}
catch(IoRequestException $e)
	{
	/** Temporärer Workaround */
	if ($Io->getHost() == 'www.laber-land.de')
		{
		$Io->redirect('Portal', 'forum=1', 1);
		}
	else
		{
		$Io->redirectToUrl($Io->getURL().'?page=Forums');
		}
	}

?>