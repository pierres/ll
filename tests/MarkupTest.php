<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

require('LLTestCase.php');

class MarkupTest extends LLTestCase {


public function testEmptyText()
	{
	$this->assertEquals('', $this->ll->Markup->toHtml(''));
	}

public function testLongText()
	{
	$count = 1000000;
	$this->assertEquals('<p>'.str_repeat('a', $count).'</p>', $this->ll->Markup->toHtml(str_repeat('a', $count)));
	try
		{
		$this->assertEquals('', $this->ll->Markup->toHtml(str_repeat('a', $count+1)));
		$this->fail();
		}
	catch (MarkupException $e)
		{
		}
	}

public function testLoopCounter()
	{
	$depth = 10000;

	$in = '<quote>'.str_repeat('...<quote>', $depth).str_repeat('...</quote>', $depth).'...</quote>';
	$out = '';
	try
		{
		$this->assertEquals($out, $this->ll->Markup->toHtml($in));
		$this->fail();
		}
	catch (MarkupException $e)
		{
		}
	}

public function testCode()
	{
	$in = "<code>.\n\t\n\n\n\n..</code>";
	$out = "<pre><code>.\n\t\n\n\n\n..</code></pre>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "<code>\ntest<code>...</code>\n</code>";
	$out = "<pre><code>\ntest&lt;code&gt;...</code></pre><p>\n&lt;/code&gt;</p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "<code>\ntest</code>...<code>\t\n\n\n\n</code>";
	$out = "<pre><code>\ntest</code></pre><p>...</p><pre><code>\t\n\n\n\n</code></pre>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "<code>\ntest<code>...<code>\t\n\n\n\n</code></code>";
	$out = "<pre><code>\ntest&lt;code&gt;...&lt;code&gt;\t\n\n\n\n</code></pre><p>&lt;/code&gt;</p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "<code>\ntest";
	$out = "<p>&lt;code&gt;\ntest</p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "</code>\ntest";
	$out = "<p>&lt;/code&gt;\ntest</p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "<code>.\t\t..</code>";
	$out = "<p><code>.\t\t..</code></p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "<code>\ttest<code>...</code>\t</code>";
	$out = "<p><code>\ttest&lt;code&gt;...</code>\t&lt;/code&gt;</p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "<code>\ttest</code>...<code>\t\t\t\t\t</code>";
	$out = "<p><code>\ttest</code>...<code>\t\t\t\t\t</code></p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "<code>\ttest";
	$out = "<p>&lt;code&gt;\ttest</p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "</code>\ttest";
	$out = "<p>&lt;/code&gt;\ttest</p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testLinks()
	{
	$mediaUrls = array(
		'http://www.archlinux.de/',
		'ftp://ftp.archlinux.org/path/',
		'http://www.archlinux.de/some-other-long/path/',
		'http://www.archlinux.de/some-other-long/path/?media='
		);
	$urls = array_merge($mediaUrls, array(
		'http://www.archlinux.de',
		'http://www.archlinux.de/?page=Packages;orderby=builddate;sort=1;repository=0;architecture=2;group=0;packager=0;search=;searchfield=0;package=50',
		'http://www.archlinux.de/?page=Packages&orderby=builddate&sort=1&repository=0&architecture=2&group=0&packager=0&search=&searchfield=0&package=50',
		'http://www.google.de/search?hl=de&client=firefox-a&rls=org.mozilla%3Aen-US%3Aofficial&hs=eAU&q=archlinux+%2Bdeutsch&btnG=Suche&meta=',
		'http://bugs.archlinux.org/index.php?string=&project=6&due[]=59&due[]=67&due[]=72&status[]',
		'ftp://ftp.archlinux.org',
		'ftp://www.archlinux.org//',
		'http://wiki.archlinux.org/index.php/Main_Page_%28Deutsch%29'
		));

	foreach ($urls as $url)
		{
		$this->assertEquals('<p><a href="'.htmlentities($url).'" rel="nofollow">...</a></p>', $this->ll->Markup->toHtml('<a href="'.$url.'">...</a>'));
		$this->assertEquals('<p><a href="'.htmlentities($url).'" rel="nofollow" class="link-auto">'.htmlentities($url).'</a></p>', $this->ll->Markup->toHtml($url));
		}

	foreach ($urls as $url)
		{
		$this->assertEquals('<p><a href="?page=GetImage&amp;url='.urlencode($url).'" rel="nofollow"><img src="?page=GetImage&amp;thumb=1&amp;url='.urlencode($url).'" alt="" class="image" /></a></p>', $this->ll->Markup->toHtml('<img src="'.$url.'" />'));
		}

	foreach ($urls as $url)
		{
		$this->assertEquals('<p><audio src="'.htmlentities($url).'" controls="controls"><a href="'.htmlentities($url).'" rel="nofollow">'.htmlentities($url).'</a></audio></p>', $this->ll->Markup->toHtml('<audio src="'.$url.'" />'));
		}

	foreach ($urls as $url)
		{
		$this->assertEquals('<p><video src="'.htmlentities($url).'" controls="controls"><a href="'.htmlentities($url).'" rel="nofollow">'.htmlentities($url).'</a></video></p>', $this->ll->Markup->toHtml('<video src="'.$url.'" />'));
		}

	foreach ($mediaUrls as $url)
		{
		$this->assertEquals('<p><a href="?page=GetImage&amp;url='.urlencode($url.'test.png').'" rel="nofollow" class="link-auto"><img src="?page=GetImage&amp;thumb=1&amp;url='.urlencode($url.'test.png').'" alt="" class="image" /></a></p>', $this->ll->Markup->toHtml($url.'test.png'));
		}

	foreach ($mediaUrls as $url)
		{
		$this->assertEquals('<p><video src="'.htmlentities($url.'test.ogg').'" controls="controls"><a href="'.htmlentities($url.'test.ogg').'" rel="nofollow" class="link-auto">'.htmlentities($url.'test.ogg').'</a></video></p>', $this->ll->Markup->toHtml($url.'test.ogg'));
		}

	$in = '<a href="http://www.archlinux.de/">...</a> http://www.archlinux.de/test.png';
	$out = '<p><a href="http://www.archlinux.de/" rel="nofollow">...</a> <a href="?page=GetImage&amp;url=http%3A%2F%2Fwww.archlinux.de%2Ftest.png" rel="nofollow" class="link-auto"><img src="?page=GetImage&amp;thumb=1&amp;url=http%3A%2F%2Fwww.archlinux.de%2Ftest.png" alt="" class="image" /></a></p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testSmilies()
	{
	foreach (Markup::$smilies as $smiley => $name)
		{
		$this->assertEquals('<p><img src="images/smilies/'.$name.'.png" alt="'.$name.'" class="smiley" /></p>', $this->ll->Markup->toHtml($smiley));
		}
	}

public function testEm()
	{
	$this->assertEquals('<p><em>test</em></p>', $this->ll->Markup->toHtml("''test''"));
	$this->assertEquals('<p><strong>test</strong></p>', $this->ll->Markup->toHtml("'''test'''"));
	$this->assertEquals("<p>'''test\n'''</p>", $this->ll->Markup->toHtml("'''test\n'''"));
	$this->assertEquals("<p><em>test</em>'</p>", $this->ll->Markup->toHtml("''test'''"));
	$this->assertEquals("<p><strong>''test</strong>''</p>", $this->ll->Markup->toHtml("'''''test'''''"));
	}

public function testInlineQuote()
	{
	$this->assertEquals('<p><q>test</q></p>', $this->ll->Markup->toHtml('"test"'));
	$this->assertEquals("<p>&quot;test\n&quot;</p>", $this->ll->Markup->toHtml("\"test\n\""));
	}

public function testQuote()
	{
	$in = '<quote></quote>';
	$out = '<blockquote></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote>test</quote>';
	$out = '<blockquote><p>test</p></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote>test<quote>test2</quote></quote>';
	$out = '<blockquote><p>test</p><blockquote><p>test2</p></blockquote></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote author>test</quote>';
	$out = '<cite>author</cite><blockquote><p>test</p></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = '<quote author>test<quote author2>test2</quote></quote>';
	$out = '<cite>author</cite><blockquote><p>test</p><cite>author2</cite><blockquote><p>test2</p></blockquote></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	try
		{
		$in = "</quote>\n\na";
		$this->ll->Markup->toHtml($in);
		$this->fail();
		}
	catch (MarkupException $e)
		{
		}
	}

public function testParagraph()
	{
	$in = "a\n\nb";
	$out = '<p>a</p><p>b</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "a\n\n\n\n\n\n\nb";
	$out = '<p>a</p><p>b</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "a\r\n\r\nb";
	$out = '<p>a</p><p>b</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "a\nb";
	$out = "<p>a\nb</p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	$in = "a\r\nb";
	$out = "<p>a\nb</p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testQuoteAndLink()
	{
	$in = '<quote>http://www.laber-land.de/</quote>';
	$out = '<blockquote><p><a href="http://www.laber-land.de/" rel="nofollow" class="link-auto">http://www.laber-land.de/</a></p></blockquote>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));

	try
		{
		$in = 'http://www.laber-land.de/test.html</quote>';
		$this->ll->Markup->toHtml($in);
		$this->fail();
		}
	catch (MarkupException $e)
		{
		}
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
	$out = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul><p>abc</p>';
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));


	try
		{
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
		$this->ll->Markup->toHtml($in);
		$this->fail();
		}
	catch (MarkupException $e)
		{
		}

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
	$out = "<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul><p>abcd\n</p><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul><p>abc</p>";
	$this->assertEquals($out, $this->ll->Markup->toHtml($in));
	}

public function testQuoteAndList()
	{
	$in = "<quote>\n* 1\n* 2\n</quote>";
	$out = "<blockquote><p>\n</p><ul><li>1</li><li>2</li></ul></blockquote>";

	$this->assertEquals($out,  $this->ll->Markup->toHtml($in));

	$in = "* 2\n* 3\n* 4\n<code>\nreg\n</code>\n* 2\n* 3";
	$out = "<ul><li>2</li><li>3</li><li>4</li></ul><pre><code>\nreg\n</code></pre><p>\n</p><ul><li>2</li><li>3</li></ul>";

	$this->assertEquals($out,  $this->ll->Markup->toHtml($in));
	}

public function testCodeAndList()
	{
	$in = "* <code>a</code>";
	$out = "<ul><li><code>a</code></li></ul>";

	$this->assertEquals($out,  $this->ll->Markup->toHtml($in));
	}

public function testQuoteErrorCheck()
	{
	try
		{
		$in = "<quote>\n* a</quote>";
		$this->ll->Markup->toHtml($in);
		$this->fail();
		}
	catch (MarkupException $e)
		{
		}

	try
		{
		$in = '<quote></quote></quote><quote></quote>';
		$this->ll->Markup->toHtml($in);
		$this->fail();
		}
	catch (MarkupException $e)
		{
		}

	try
		{
		$in = 'a<quote>b</quote>c</quote>d<quote>e</quote>f';
		$this->ll->Markup->toHtml($in);
		$this->fail();
		}
	catch (MarkupException $e)
		{
		}

	try
		{
		$in = '<quote>a<quote>b</quote>';
		$this->ll->Markup->toHtml($in);
		$this->fail();
		}
	catch (MarkupException $e)
		{
		}
	}

}

?>