<?php

require('LLTestCase.php');

class UnMarkupTest extends LLTestCase{


public function testEmpty()
	{
	$this->assertEquals($this->UnMarkup->fromHtml(''), '');
	}

public function testCode()
	{
	$in =
'<pre>
test&quot;&lt;code&gt;
</pre>
';
	$out =
'<code>
test"<code>
</code>
';

	$this->assertEquals($out, $this->UnMarkup->fromHtml($in));
	}

public function testQuote()
	{
	$out = '<quote></quote>';
	$in = htmlspecialchars('<quote></quote>');
	$this->assertEquals($out, $this->UnMarkup->fromHtml($in));

	$out = '<quote>test</quote>';
	$in = '<blockquote><div>test</div></blockquote>';
	$this->assertEquals($out, $this->UnMarkup->fromHtml($in));

	$out = '<quote>test<quote>test2</quote></quote>';
	$in = '<blockquote><div>test<blockquote><div>test2</div></blockquote></div></blockquote>';
	$this->assertEquals($out, $this->UnMarkup->fromHtml($in));

	$out = '<quote=></quote>';
	$in = htmlspecialchars('<quote=></quote>');
	$this->assertEquals($out, $this->UnMarkup->fromHtml($in));

	$out = '<quote=author>test</quote>';
	$in = '<cite>author</cite><blockquote><div>test</div></blockquote>';
	$this->assertEquals($out, $this->UnMarkup->fromHtml($in));

	$out = '<quote=author>test<quote=author2>test2</quote></quote>';
	$in = '<cite>author</cite><blockquote><div>test<cite>author2</cite><blockquote><div>test2</div></blockquote></div></blockquote>';
	$this->assertEquals($out, $this->UnMarkup->fromHtml($in));
	}

public function testList()
	{
	$out =
'* 1
* 2
** 2a
** 2b
*** 2bi
**** 2bii
** 2c
* 3
** 4
';
	$in = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>';
 	$this->assertEquals($out, $this->UnMarkup->fromHtml($in));

		$out =
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
	$in = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc';
	$this->assertEquals($out, $this->UnMarkup->fromHtml($in));

	$out =
'o
* 1
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
	$in = 'o<br /><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abcd<br /><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc';
  	$this->assertEquals($out, $this->UnMarkup->fromHtml($in));
	}

public function testDel()
	{
	$this->assertEquals('--test--', $this->UnMarkup->fromHtml('<del>test</del>'));
	}

public function testIns()
	{
	$this->assertEquals('++test++', $this->UnMarkup->fromHtml('<ins>test</ins>'));
	}

public function testStrong()
	{
	$this->assertEquals('!!test!!', $this->UnMarkup->fromHtml('<strong>test</strong>'));
	}


}

?>