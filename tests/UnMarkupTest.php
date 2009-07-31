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
	$in =
'<pre>
test&quot;&lt;code&gt;
</pre>';
	$out =
'<code>
test"<code>
</code>';

	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$in =
'-<br /><pre>
test&quot;&lt;code&gt;
</pre><br />-';
	$out =
'-
<code>
test"<code>
</code>
-';

	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testQuote()
	{
	$out = '<quote></quote>';
	$in = htmlspecialchars('<quote></quote>');
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote>test</quote>';
	$in = '<blockquote><div>test</div></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote>test<quote>test2</quote></quote>';
	$in = '<blockquote><div>test<blockquote><div>test2</div></blockquote></div></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote ></quote>';
	$in = htmlspecialchars('<quote ></quote>');
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote author>test</quote>';
	$in = '<cite>author</cite><blockquote><div>test</div></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote author>test<quote author2>test2</quote></quote>';
	$in = '<cite>author</cite><blockquote><div>test<cite>author2</cite><blockquote><div>test2</div></blockquote></div></blockquote>';
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
** 4
';
	$in = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>';
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
	$in = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc';
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
	$in = 'o<br /><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abcd<br /><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc';
  	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testLinkInList()
	{
	$out =
'* <http://www.heise.de Heise>
* 2gg
';
	$in = '<ul><li><a href="http://www.heise.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">Heise</a></li><li>2gg</li></ul>';
  	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testDel()
	{
	$this->assertEquals('--test--', $this->ll->UnMarkup->fromHtml('<span><del>test</del></span>'));
	}

public function testIns()
	{
	$this->assertEquals('++test++', $this->ll->UnMarkup->fromHtml('<span><ins>test</ins></span>'));
	}

public function testStrong()
	{
	$this->assertEquals('**test**', $this->ll->UnMarkup->fromHtml('<strong>test</strong>'));
	}

public function testBug86()
	{
	$this->assertEquals('test
<code>
123
</code>
blah', $this->ll->UnMarkup->fromHtml('test<br /><pre>
123
</pre><br />blah'));
	}

public function testBug85()
	{
	$out = '<quote>
* 1
* 2
</quote>';
	$in = '<blockquote><div><br /><ul><li>1</li><li>2</li></ul></div></blockquote>';

	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '* 2
* 3
* 4
<code>
reg
</code>
* 2
* 3
';
	$in = '<ul><li>2</li><li>3</li><li>4</li></ul><pre>
reg
</pre><br /><ul><li>2</li><li>3</li></ul>';

	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testBug93()
	{
	$this->assertEquals('<http://www.laber-land.de aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa>', $this->ll->UnMarkup->fromHtml('<a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa</a>'));

	$this->assertEquals('http://www.laber-land.de/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $this->ll->UnMarkup->fromHtml('<!-- cutted --><a href="http://www.laber-land.de/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">http://www.laber-land.de/aaaaaaaaaaaa...</a><!-- /cutted -->'));
	}

}

?>