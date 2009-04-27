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

require('LLTestCase.php');

class InputTest extends LLTestCase {

public function testBug165()
	{
	// this might be ugly...
	$_POST['test_bad'] = 'ab'.chr(27);
	$_POST['test_good'] = 'abc';

	try
		{
		$this->ll->Input->Post->getString('test_bad');
		$this->fail('test_bad should not be accepted!');
		}
	catch (RequestException $e)
		{
		}

	try
		{
		$test_good = $this->ll->Input->Post->getString('test_good');
		$this->assertEquals($test_good, $_POST['test_good']);
		}
	catch (RequestException $e)
		{
		$this->fail($e);
		}
	}

public function testPcreSegfault()
	{
	$_POST['text'] = str_repeat('#', 6000);
	$text = $this->ll->Input->Post->getString('text');
	$this->assertEquals(strlen($text), 6000);
	}
}

?>