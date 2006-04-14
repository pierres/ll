<?php

require('LLTestCase.php');

class MailTest extends LLTestCase{

public function testValidateMail()
	{
	$this->assertTrue($this->Mail->validateMail('support@laber-land.de'));
	$this->assertFalse($this->Mail->validateMail('a support@laber-land.de'));
	}

}

?>