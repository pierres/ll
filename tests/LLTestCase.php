<?php

require_once 'PHPUnit2/Framework/TestCase.php';
require ('modules/Settings.php');
require ('modules/Functions.php');

function __autoload($class)
	{
	if (file_exists('modules/'.$class . '.php'))
		{
		require('modules/'.$class . '.php');
		}
	elseif (file_exists('pages/'.$class . '.php'))
		{
		require('pages/'.$class . '.php');
		}
	elseif (file_exists('pages/abstract/'.$class . '.php'))
		{
		require('pages/abstract/'.$class . '.php');
		}
	else
		{
		die('Konnte Modul "'.$class.'" nicht finden!');
		}
	}

abstract class LLTestCase extends PHPUnit2_Framework_TestCase{

public static $modules = array();

public static function __get($name)
	{
	if (!isset(self::$modules[$name]))
		{
		$new = new $name();
		self::$modules[$name] = &$new;
		return $new;
		}
	else
		{
		return self::$modules[$name];
		}
	}

public static function __set($name, &$object)
	{
	if (!isset(self::$modules[$name]))
		{
		self::$modules[$name] = $object;
		return $object;
		}
	else
		{
		return self::$modules[$name];
		}
	}

public function setUp()
	{
	}

public function tearDown()
	{
	self::$modules = array();
	}

}

?>