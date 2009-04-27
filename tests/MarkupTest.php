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
<code>
test"<code>
</code>
-';
	$out =
'-<br /><pre>
test&quot;&lt;code&gt;
</pre><br />-';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in =
'<code>
test"<code>
</code>';
	$out =
'<pre>
test&quot;&lt;code&gt;
</pre>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testQuote()
	{
	$in = '<quote></quote>';
	$out = htmlspecialchars('<quote></quote>');
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote>test</quote>';
	$out = '<blockquote><div>test</div></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote>test<quote>test2</quote></quote>';
	$out = '<blockquote><div>test<blockquote><div>test2</div></blockquote></div></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote ></quote>';
	$out = htmlspecialchars('<quote ></quote>');
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote author>test</quote>';
	$out = '<cite>author</cite><blockquote><div>test</div></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote author>test<quote author2>test2</quote></quote>';
	$out = '<cite>author</cite><blockquote><div>test<cite>author2</cite><blockquote><div>test2</div></blockquote></div></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testQuoteAndLink()
	{
	$in = '<quote>http://www.laber-land.de/</quote>';
	$out = '<blockquote><div><a href="http://www.laber-land.de/" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">http://www.laber-land.de/</a></div></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = 'http://www.laber-land.de/test.html</quote>';
	$out = '<a href="http://www.laber-land.de/test.html" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">http://www.laber-land.de/test.html</a>&lt;/quote&gt;';
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
	$out = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>';
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
	$out = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc';
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
	$out = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>';
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
	$out = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abcd<br /><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul>abc';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testListAndStrong()
	{
	$in =
'* 1
** test **';

	$out = '<ul><li>1<ul><li>test **</li></ul></li></ul>';

	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in =
'* 1
** 2
**test**';

	$out = '<ul><li>1<ul><li>2</li></ul></li></ul><strong>test</strong>';

	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testHeading()
	{
	$this->assertEquals('<h1>test</h1>', $this->ll->Markup->toHtml('!test'));
	$this->assertEquals('<h2>test</h2>', $this->ll->Markup->toHtml('!!test'));
	$this->assertEquals('<h3>test</h3>', $this->ll->Markup->toHtml('!!!test'));
	$this->assertEquals('<h4>test</h4>', $this->ll->Markup->toHtml('!!!!test'));
	$this->assertEquals('<h5>test</h5>', $this->ll->Markup->toHtml('!!!!!test'));
	$this->assertEquals('<h6>test</h6>', $this->ll->Markup->toHtml('!!!!!!test'));
	}

public function testEm()
	{
	$this->assertEquals('<em>test</em>', $this->ll->Markup->toHtml('//test//'));
	}

public function testStrong()
	{
	$this->assertEquals('<strong>test</strong>', $this->ll->Markup->toHtml('**test**'));
	}

public function testInlineCode()
	{
	$this->assertEquals('<code>//test//</code>', $this->ll->Markup->toHtml('==//test//=='));
	}

public function testInlineQuote()
	{
	$this->assertEquals('<q>test</q>', $this->ll->Markup->toHtml('"test"'));
	}

public function testHr()
	{
	$this->assertEquals('<hr />', $this->ll->Markup->toHtml('----'));
	}

public function testDel()
	{
	$this->assertEquals('<span><del>test</del></span>', $this->ll->Markup->toHtml('--test--'));
	}

public function testIns()
	{
	$this->assertEquals('<span><ins>test</ins></span>', $this->ll->Markup->toHtml('++test++'));
	}

public function testURL()
	{
	$this->assertEquals('<a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">Laber-Land</a>', $this->ll->Markup->toHtml('<http://www.laber-land.de Laber-Land>'));
	$this->assertEquals('<a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">Laber-Land</a>', $this->ll->Markup->toHtml('<www.laber-land.de Laber-Land>'));
	$this->assertEquals('<a href="ftp://ftp.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">Laber-Land</a>', $this->ll->Markup->toHtml('<ftp.laber-land.de Laber-Land>'));
	$this->assertEquals('<!-- numbered --><a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">[1]</a><!-- /numbered -->', $this->ll->Markup->toHtml('<http://www.laber-land.de>'));
	$this->assertEquals('<!-- numbered --><a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">[2]</a><!-- /numbered -->', $this->ll->Markup->toHtml('<www.laber-land.de>'));
	$this->assertEquals('<!-- numbered --><a href="ftp://ftp.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">[3]</a><!-- /numbered -->', $this->ll->Markup->toHtml('<ftp.laber-land.de>'));
	$this->assertEquals('<a href="mailto:support@laber-land.de">support@laber-land.de</a>', $this->ll->Markup->toHtml('support@laber-land.de'));
// 	$this->assertEquals('<a href="?page=GetImage;url=http%3A%2F%2Fwww.laber-land.de%2Fimages%2Flogo.png" onclick="return !window.open(this.href);" rel="nofollow"><img src="?page=GetImage;thumb;url=http%3A%2F%2Fwww.laber-land.de%2Fimages%2Flogo.png" alt="" class="image" /></a>', $this->ll->Markup->toHtml('http://www.laber-land.de/images/logo.png'));
// 	$this->assertEquals('<a href="?page=GetImage;url=http%3A%2F%2Fwww.laber-land.de%2Fimages%2Flogo.png" onclick="return !window.open(this.href);" rel="nofollow"><img src="?page=GetImage;thumb;url=http%3A%2F%2Fwww.laber-land.de%2Fimages%2Flogo.png" alt="" class="image" /></a>', $this->ll->Markup->toHtml('www.laber-land.de/images/logo.png'));
// 	$this->assertEquals('<a href="?page=GetImage;url=ftp%3A%2F%2Fftp.laber-land.de%2Fimages%2Flogo.png" onclick="return !window.open(this.href);" rel="nofollow"><img src="?page=GetImage;thumb;url=ftp%3A%2F%2Fftp.laber-land.de%2Fimages%2Flogo.png" alt="" class="image" /></a>', $this->ll->Markup->toHtml('ftp.laber-land.de/images/logo.png'));
	$this->assertEquals('<a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">http://www.laber-land.de</a>', $this->ll->Markup->toHtml('http://www.laber-land.de'));
	$this->assertEquals('<a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">www.laber-land.de</a>', $this->ll->Markup->toHtml('www.laber-land.de'));
	$this->assertEquals('<a href="ftp://ftp.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">ftp.laber-land.de</a>', $this->ll->Markup->toHtml('ftp.laber-land.de'));
	}

public function testSmilies()
	{
	$this->assertEquals('<img src="images/smilies/wink.gif" alt="wink" class="smiley" />',$this->ll->Markup->toHtml(';-)'));
	}

public function testBug74()
	{
	$this->assertEquals('<q>--test</q>--', $this->ll->Markup->toHtml('"--test"--'));
	$this->assertEquals('<span><del><q>test</q></del></span>', $this->ll->Markup->toHtml('--"test"--'));
	$this->assertEquals('<q>--test--</q>', $this->ll->Markup->toHtml('"--test--"'));
	}

public function testBug86()
	{
	$this->assertEquals('test<br /><pre>
123
</pre><br />blah', $this->ll->Markup->toHtml('test
<code>
123
</code>
blah'));
	}

public function testBug85()
	{
	$in = '<quote>
* 1
* 2
</quote>';
	$out = '<blockquote><div><br /><ul><li>1</li><li>2</li></ul></div></blockquote>';

	$this->assertEquals($out,  $this->ll->Markup->toHtml($in));

	$in = '* 2
* 3
* 4
<code>
reg
</code>
* 2
* 3';
	$out = '<ul><li>2</li><li>3</li><li>4</li></ul><pre>
reg
</pre><br /><ul><li>2</li><li>3</li></ul>';

	$this->assertEquals($out,  $this->ll->Markup->toHtml($in));
	}

public function testBug93()
	{
	$this->assertEquals('<a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa</a>', $this->ll->Markup->toHtml('<http://www.laber-land.de aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa>'));

	$this->assertEquals('<!-- numbered --><a href="http://www.laber-land.de/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">[4]</a><!-- /numbered -->', $this->ll->Markup->toHtml('<http://www.laber-land.de/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa>'));

	$this->assertEquals('<!-- cutted --><a href="http://www.laber-land.de/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">http://www.laber-land.de/aaaaaaaaaaaa...</a><!-- /cutted -->', $this->ll->Markup->toHtml('http://www.laber-land.de/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'));
	}

public function testBug121()
	{
	$in = '<quote>
* a</quote>';

	$out = '&lt;quote&gt;<br /><ul><li>a&lt;/quote&gt;</li></ul>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote></quote></quote><quote></quote>';

	$out = '<blockquote><div></div></blockquote>&lt;/quote&gt;<blockquote><div></div></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = 'a<quote>b</quote>c</quote>d<quote>e</quote>f';

	$out = 'a<blockquote><div>b</div></blockquote>c&lt;/quote&gt;d<blockquote><div>e</div></blockquote>f';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testBug131()
	{
	$in = '<quote>a<quote>b</quote>';
	$out = '<blockquote><div>a<blockquote><div>b</div></blockquote></div></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

}

?>