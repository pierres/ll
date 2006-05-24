<?php

require('LLTestCase.php');

class AntiSpamTest extends LLTestCase{


public function setup()
	{
	Modul::__set('DB', new DB(
		$this->Settings->getValue('sql_user'),
		$this->Settings->getValue('sql_password'),
		$this->Settings->getValue('sql_database')
		));
	}

public function testIsSpam()
	{
	$this->AntiSpam->addSpam('1blah www.bdomain.de blubb http://laber-land.de');

	$this->assertTrue($this->AntiSpam->isSpam('www.laber-land.de'));
	$this->assertTrue($this->AntiSpam->isSpam('http://bdomain.de'));
	}


}


?>