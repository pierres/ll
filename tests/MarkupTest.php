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

class MarkupTest extends LLTestCase {


public function testEmpty()
	{
	$this->assertEquals($this->ll->Markup->toHtml(''), '');
	}

public function testCode()
	{
	$in =
'-
<pre>
test"<pre>
</pre>
-';
	$out =
'<p>-
<pre>
test&quot;&lt;pre&gt;
</pre>
-</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in =
'<pre>
test"<pre>
</pre>';
	$out =
'<p><pre>
test&quot;&lt;pre&gt;
</pre></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testQuote()
	{
	$in = '<quote></quote>';
	$out = '<p>'.htmlspecialchars('<quote></quote>').'</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote>test</quote>';
	$out = '<p><blockquote><div>test</div></blockquote></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote>test<quote>test2</quote></quote>';
	$out = '<p><blockquote><div>test<blockquote><div>test2</div></blockquote></div></blockquote></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote ></quote>';
	$out = '<p>'.htmlspecialchars('<quote ></quote>').'</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote author>test</quote>';
	$out = '<p><cite>author</cite><blockquote><div>test</div></blockquote></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote author>test<quote author2>test2</quote></quote>';
	$out = '<p><cite>author</cite><blockquote><div>test<cite>author2</cite><blockquote><div>test2</div></blockquote></div></blockquote></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testQuoteAndLink()
	{
	$in = '<quote>http://www.laber-land.de/</quote>';
	$out = '<p><blockquote><div><a href="http://www.laber-land.de/" rel="nofollow" rev="auto">http://www.laber-land.de/</a></div></blockquote></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = 'http://www.laber-land.de/test.html</quote>';
	$out = '<p><a href="http://www.laber-land.de/test.html" rel="nofollow" rev="auto">http://www.laber-land.de/test.html</a>&lt;/quote&gt;</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
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
	$out = '<p><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

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
	$out = '<p><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

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
	$out = '<p><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

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
	$out = '<p><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abcd
<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testEm()
	{
	$this->assertEquals('<p><em>test</em></p>', $this->ll->Markup->toHtml("''test''"));
	}

public function testStrong()
	{
	$this->assertEquals('<p><strong>test</strong></p>', $this->ll->Markup->toHtml("'''test'''"));
	}

public function testInlineCode()
	{
	$this->assertEquals("<p><code>''test''</code></p>", $this->ll->Markup->toHtml("<code>''test''</code>"));
	}

public function testInlineQuote()
	{
	$this->assertEquals('<p><q>test</q></p>', $this->ll->Markup->toHtml('"test"'));
	}

public function testURL()
	{
	$this->assertEquals('<p><a href="http://www.laber-land.de" rel="nofollow">Laber-Land</a></p>', $this->ll->Markup->toHtml('<a href="http://www.laber-land.de">Laber-Land</a>'));
	$this->assertEquals('<p><a href="http://www.laber-land.de" rel="nofollow">Laber-Land</a></p>', $this->ll->Markup->toHtml('<a href="www.laber-land.de">Laber-Land</a>'));
	$this->assertEquals('<p><a href="ftp://ftp.laber-land.de" rel="nofollow">Laber-Land</a></p>', $this->ll->Markup->toHtml('<a href="ftp.laber-land.de">Laber-Land</a>'));

	$this->assertEquals('<p><a href="http://www.laber-land.de" rel="nofollow" rev="auto">http://www.laber-land.de</a></p>', $this->ll->Markup->toHtml('http://www.laber-land.de'));
	$this->assertEquals('<p><a href="http://www.laber-land.de" rel="nofollow" rev="auto">http://www.laber-land.de</a></p>', $this->ll->Markup->toHtml('www.laber-land.de'));
	$this->assertEquals('<p><a href="ftp://ftp.laber-land.de" rel="nofollow" rev="auto">ftp://ftp.laber-land.de</a></p>', $this->ll->Markup->toHtml('ftp.laber-land.de'));
	}

public function testSmilies()
	{
	$this->assertEquals('<p><img src="images/smilies/wink.png" alt="wink" class="smiley" /></p>',$this->ll->Markup->toHtml(';-)'));
	}

public function testBug86()
	{
	$this->assertEquals('<p>test
<pre>
123
</pre>
blah</p>', $this->ll->Markup->toHtml('test
<pre>
123
</pre>
blah'));
	}

public function testBug85()
	{
	$in = '<quote>
* 1
* 2
</quote>';
	$out = '<p><blockquote><div>
<ul><li>1</li><li>2</li></ul></div></blockquote></p>';

	$this->assertEquals($out,  $this->ll->Markup->toHtml($in));

	$in = '* 2
* 3
* 4
<pre>
reg
</pre>
* 2
* 3';
	$out = '<p><ul><li>2</li><li>3</li><li>4</li></ul><pre>
reg
</pre>
<ul><li>2</li><li>3</li></ul></p>';

	$this->assertEquals($out,  $this->ll->Markup->toHtml($in));
	}

public function testBug121()
	{
	$in = '<quote>
* a</quote>';

	$out = '<p>&lt;quote&gt;
<ul><li>a&lt;/quote&gt;</li></ul></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote></quote></quote><quote></quote>';

	$out = '<p><blockquote><div></div></blockquote>&lt;/quote&gt;<blockquote><div></div></blockquote></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = 'a<quote>b</quote>c</quote>d<quote>e</quote>f';

	$out = '<p>a<blockquote><div>b</div></blockquote>c&lt;/quote&gt;d<blockquote><div>e</div></blockquote>f</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testBug131()
	{
	$in = '<quote>a<quote>b</quote>';
	$out = '<p><blockquote><div>a<blockquote><div>b</div></blockquote></div></blockquote></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

}

?>