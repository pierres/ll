<?php

require('LLTestCase.php');

class MarkupTest extends LLTestCase{

function testDel()
	{
	$this->assertEquals($this->Markup->toHtml('--test--'), '<del>test</del>');
	}

function testIns()
	{
	$this->assertEquals($this->Markup->toHtml('++test++'), '<ins>test</ins>');
	}

function testStrong()
	{
	$this->assertEquals($this->Markup->toHtml('!!test!!'), '<strong>test</strong>');
	}


}

?>