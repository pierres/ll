<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/
abstract class Modul {

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
	'Input' => 'modules/Input.php',
	'L10n' => 'modules/L10n.php',
	'Log' => 'modules/Log.php',
	'Mail' => 'modules/Mail.php',
	'Markup' => 'modules/Markup.php',
	'Modul' => 'modules/Modul.php',
	'ObjectCache' => 'modules/ObjectCache.php',
	'Ouput' => 'modules/Output.php',
	'PersistentCache' => 'modules/PersistentCache.php',
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

public static function get($name)
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

public function __get($name)
	{
	return self::get($name);
	}

public static function set($name, $object)
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

public function __set($name, $object)
	{
	return self::set($name, $object);
	}

protected function getName()
	{
	return get_class($this);
	}

}

?>