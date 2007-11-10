<?php

abstract class Modul{

private static $loadedModules = array();

private static $availableModules = array
	(
	'AdminForm' => 'pages/abstract/AdminForm.php',
	'AdminPage' => 'pages/abstract/AdminPage.php',
	'Form' => 'pages/abstract/Form.php',
	'GetFile' => 'pages/abstract/GetFile.php',
	'Page' => 'pages/abstract/Page.php',
	'Poll' => 'modules/Poll.php',
	'AdminFunctions' => 'modules/AdminFunctions.php',
	'Board' => 'modules/Board.php',
	'DB' => 'modules/DB.php',
	'Exceptions' => 'modules/Exceptions.php',
	'Functions' => 'modules/Functions.php',
// 	'IOutput' => 'modules/IOutput.php',
	'Io' => 'modules/Io.php',
	'Log' => 'modules/Log.php',
	'Mail' => 'modules/Mail.php',
	'Markup' => 'modules/Markup.php',
	'Modul' => 'modules/Modul.php',
	'Settings' => 'modules/Settings.php',
	'Stack' => 'modules/Stack.php',
	'ThreadList' => 'modules/ThreadList.php',
	'UnMarkup' => 'modules/UnMarkup.php',
	'User' => 'modules/User.php'
	);

public static function loadModul($name)
	{
	if (isset(self::$availableModules[$name]))
		{
		include_once(self::$availableModules[$name]);
		}
	else
		{
		throw new RuntimeException('Modul '.$name.' wurde nicht gefunden!', 0);
		}
	}

public static function __get($name)
	{
	if (!isset(self::$loadedModules[$name]))
		{
		self::loadModul($name);
		$new = new $name();
		self::$loadedModules[$name] = &$new;
		return $new;
		}
	else
		{
		return self::$loadedModules[$name];
		}
	}

public static function __set($name, $object)
	{
	if (!isset(self::$loadedModules[$name]))
		{
		self::$loadedModules[$name] = $object;
		return $object;
		}
	else
		{
		return self::$loadedModules[$name];
		}
	}

protected function getName()
	{
	return get_class($this);
	}

}

?>