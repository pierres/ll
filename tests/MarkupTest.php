<?php

require('LLTestCase.php');

class MarkupTest extends LLTestCase{


public function testEmpty()
	{
	$this->assertEquals($this->Markup->toHtml(''), '');
	}

public function testCode()
	{
	$in =
'-
<code>
test"<code>
</code>
-';
	$out =
'-<br /><pre>
test&quot;&lt;code&gt;
</pre><br />-';
	$this->assertEquals($out, $this->Markup->toHtml($in));
	}

public function testQuote()
	{
	$in = '<quote></quote>';
	$out = htmlspecialchars('<quote></quote>');
	$this->assertEquals($out, $this->Markup->toHtml($in));

	$in = '<quote>test</quote>';
	$out = '<blockquote><div>test</div></blockquote>';
	$this->assertEquals($out, $this->Markup->toHtml($in));

	$in = '<quote>test<quote>test2</quote></quote>';
	$out = '<blockquote><div>test<blockquote><div>test2</div></blockquote></div></blockquote>';
	$this->assertEquals($out, $this->Markup->toHtml($in));

	$in = '<quote=></quote>';
	$out = htmlspecialchars('<quote=></quote>');
	$this->assertEquals($out, $this->Markup->toHtml($in));

	$in = '<quote=author>test</quote>';
	$out = '<cite>author</cite><blockquote><div>test</div></blockquote>';
	$this->assertEquals($out, $this->Markup->toHtml($in));

	$in = '<quote=author>test<quote=author2>test2</quote></quote>';
	$out = '<cite>author</cite><blockquote><div>test<cite>author2</cite><blockquote><div>test2</div></blockquote></div></blockquote>';
	$this->assertEquals($out, $this->Markup->toHtml($in));
	}

public function testList()
	{
	$in =
'* 1
* 2
** 2a
** 2b
*** 2bi
**** 2bii
** 2c
* 3
** 4';
	$out = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>';
	$this->assertEquals($out, $this->Markup->toHtml($in));

$in =
'* 1
* 2
** 2a
** 2b
*** 2bi
**** 2bii
** 2c
* 3
** 4
abc';
	$out = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc';
	$this->assertEquals($out, $this->Markup->toHtml($in));

	/** Teste Robustheit */
	$in =
'*** 1
* 2
** 2a
** 2b
*** 2bi
******* 2bii
** 2c
* 3
****** 4';
	$out = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>';
	$this->assertEquals($out, $this->Markup->toHtml($in));

	$in =
'* 1
* 2
** 2a
** 2b
*** 2bi
**** 2bii
** 2c
* 3
** 4
abcd
* 1
* 2
** 2a
** 2b
*** 2bi
**** 2bii
** 2c
* 3
** 4
abc';
	$out = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abcd<br /><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc';
	$this->assertEquals($out, $this->Markup->toHtml($in));
	}

public function testDel()
	{
	$this->assertEquals('<del>test</del>', $this->Markup->toHtml('--test--'));
	}

public function testIns()
	{
	$this->assertEquals('<ins>test</ins>', $this->Markup->toHtml('++test++'));
	}

public function testStrong()
	{
	$this->assertEquals('<strong>test</strong>', $this->Markup->toHtml('!!test!!'));
	}


}

?>