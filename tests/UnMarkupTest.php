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

class UnMarkupTest extends LLTestCase {


public function testEmpty()
	{
	$this->assertEquals($this->ll->UnMarkup->fromHtml(''), '');
	}

public function testCode()
	{
	$out =
'- <pre>
test"<pre>
</pre> -';
	$in =
'<p>- <pre>
test&quot;&lt;pre&gt;
</pre> -</p>';

	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out =
'<pre>
test"<pre>
</pre>';
	$in =
'<p><pre>
test&quot;&lt;pre&gt;
</pre></p>';

	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testQuote()
	{
	$out = '<quote></quote>';
	$in = '<p>'.htmlspecialchars('<quote></quote>').'</p>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote>test</quote>';
	$in = '<blockquote><p>test</p></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote>test<quote>test2</quote></quote>';
	$in = '<blockquote><p>test</p><blockquote><p>test2</p></blockquote></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote ></quote>';
	$in = htmlspecialchars('<quote ></quote>');
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote author>test</quote>';
	$in = '<cite>author</cite><blockquote><p>test</p></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote author>test<quote author2>test2</quote></quote>';
	$in = '<cite>author</cite><blockquote><p>test<cite>author2</cite><blockquote><p>test2</p></blockquote></p></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
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
** 4';
	$in = '<p><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul></p>';
 	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

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
	$in = '<p><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc</p>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

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
	$in = '<p>o
<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abcd
<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc</p>';
  	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testLinkInList()
	{
	$out =
'* <a href="http://www.heise.de">Heise</a>
* 2gg';
	$in = '<p>
<ul><li><a href="http://www.heise.de" rel="nofollow">Heise</a></li><li>2gg</li></ul></p>';
  	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testStrong()
	{
	$this->assertEquals("'''test'''", $this->ll->UnMarkup->fromHtml('<strong>test</strong>'));
	}

public function testBug86()
	{
	$this->assertEquals('test
<pre>
123
</pre>
blah', $this->ll->UnMarkup->fromHtml('test
<pre>
123
</pre>
blah'));
	}

public function testBug85()
	{
	$out = '<quote>
* 1
* 2
</quote>';
	$in = '<blockquote><p>
<ul><li>1</li><li>2</li></ul></p></blockquote>';

	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '* 2
* 3
* 4
<pre>
reg
</pre>
* 2
* 3';
	$in = '<ul><li>2</li><li>3</li><li>4</li></ul><pre>
reg
</pre>
<ul><li>2</li><li>3</li></ul>';

	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

}

?>