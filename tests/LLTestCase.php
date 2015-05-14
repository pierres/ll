<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

ini_set('include_path', ini_get('include_path').':../');

require ('modules/Modul.php');
require ('modules/Settings.php');
require ('modules/Exceptions.php');
require ('modules/Functions.php');
require ('modules/Input.php');
require ('modules/Output.php');
require ('modules/L10n.php');


function __autoload($class)
	{
	Modul::loadModul($class);
	}


class TestModul extends Modul {}


abstract class LLTestCase extends PHPUnit_Framework_TestCase {

protected $ll = null;


public function setUp()
	{
	Modul::set('Settings', new Settings());
	Modul::set('Input', new Input());
	Modul::set('L10n', new L10n());
	Modul::set('Output', new Output());
	$this->ll = new TestModul();
	}

public function tearDown()
	{
	}

}

?>
