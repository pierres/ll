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

class UnMarkupTest extends LLTestCase {


public function testEmpty()
	{
	$this->assertEquals('', $this->ll->UnMarkup->fromHtml(''));
	}

public function testCode()
	{
	$out = "<code>.\n\t\n\n\n\n..</code>";
	$in = "<pre><code>.\n\t\n\n\n\n..</code></pre>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "<code>\ntest<code>...</code>\n</code>";
	$in = "<pre><code>\ntest&lt;code&gt;...</code></pre><p>\n&lt;/code&gt;</p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "<code>\ntest</code>...<code>\t\n\n\n\n</code>";
	$in = "<pre><code>\ntest</code></pre><p>...</p><pre><code>\t\n\n\n\n</code></pre>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "<code>\ntest";
	$in = "<p>&lt;code&gt;\ntest</p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "</code>\ntest";
	$in = "<p>&lt;/code&gt;\ntest</p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "<code>.\t\t..</code>";
	$in = "<p><code>.\t\t..</code></p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "<code>.\n\t..</code>";
	$in = "<p>&lt;code&gt;.\n\t..&lt;/code&gt;</p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "<code>\ttest<code>...</code>\t</code>";
	$in = "<p><code>\ttest&lt;code&gt;...</code>\t&lt;/code&gt;</p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "<code>\ttest</code>...<code>\t\t\t\t\t</code>";
	$in = "<p><code>\ttest</code>...<code>\t\t\t\t\t</code></p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "<code>\ttest";
	$in = "<p>&lt;code&gt;\ttest</p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "</code>\ttest";
	$in = "<p>&lt;/code&gt;\ttest</p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
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
		$this->assertEquals('<a href="'.$url.'">...</a>', $this->ll->UnMarkup->fromHtml('<p><a href="'.htmlentities($url).'" rel="nofollow">...</a></p>'));
		$this->assertEquals($url, $this->ll->UnMarkup->fromHtml('<p><a href="'.htmlentities($url).'" rel="nofollow" class="link-auto">'.htmlentities($url).'</a></p>'));
		}

	foreach ($urls as $url)
		{
		$this->assertEquals('<img src="'.$url.'" />', $this->ll->UnMarkup->fromHtml('<p><a href="?page=GetImage&amp;url='.urlencode($url).'" rel="nofollow"><img src="?page=GetImage&amp;thumb=1&amp;url='.urlencode($url).'" alt="" class="image" /></a></p>'));
		}

	foreach ($urls as $url)
		{
		$this->assertEquals('<audio src="'.$url.'" />', $this->ll->UnMarkup->fromHtml('<p><audio src="'.htmlentities($url).'" controls="controls"><a href="'.htmlentities($url).'" rel="nofollow">'.htmlentities($url).'</a></audio></p>'));
		}

	foreach ($urls as $url)
		{
		$this->assertEquals('<video src="'.$url.'" />', $this->ll->UnMarkup->fromHtml('<p><video src="'.htmlentities($url).'" controls="controls"><a href="'.htmlentities($url).'" rel="nofollow">'.htmlentities($url).'</a></video></p>'));
		}

	foreach ($mediaUrls as $url)
		{
		$this->assertEquals($url.'test.png', $this->ll->UnMarkup->fromHtml('<p><a href="?page=GetImage&amp;url='.urlencode($url.'test.png').'" rel="nofollow" class="link-auto"><img src="?page=GetImage&amp;thumb=1&amp;url='.urlencode($url.'test.png').'" alt="" class="image" /></a></p>'));
		}

	foreach ($mediaUrls as $url)
		{
		$this->assertEquals($url.'test.ogg', $this->ll->UnMarkup->fromHtml('<p><video src="'.htmlentities($url.'test.ogg').'" controls="controls"><a href="'.htmlentities($url.'test.ogg').'" rel="nofollow" class="link-auto">'.htmlentities($url.'test.ogg').'</a></video></p>'));
		}

	$out = '<a href="http://www.archlinux.de/">...</a> http://www.archlinux.de/test.png';
	$in = '<p><a href="http://www.archlinux.de/" rel="nofollow">...</a> <a href="?page=GetImage&amp;url=http%3A%2F%2Fwww.archlinux.de%2Ftest.png" rel="nofollow" class="link-auto"><img src="?page=GetImage&amp;thumb=1&amp;url=http%3A%2F%2Fwww.archlinux.de%2Ftest.png" alt="" class="image" /></a></p>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testSmilies()
	{
	$this->ll->loadModul('Markup');
	foreach (Markup::$smilies as $smiley => $name)
		{
		$this->assertEquals($smiley, $this->ll->UnMarkup->fromHtml('<p><img src="images/smilies/'.$name.'.png" alt="'.$name.'" class="smiley" /></p>'));
		}
	}

public function testEm()
	{
	$this->assertEquals("''test''", $this->ll->UnMarkup->fromHtml('<p><em>test</em></p>'));
	$this->assertEquals("'''test'''", $this->ll->UnMarkup->fromHtml('<p><strong>test</strong></p>'));
	$this->assertEquals("'''test\n'''", $this->ll->UnMarkup->fromHtml("<p>'''test\n'''</p>"));
	$this->assertEquals("''test'''", $this->ll->UnMarkup->fromHtml("<p><em>test</em>'</p>"));
	$this->assertEquals("'''''test'''''", $this->ll->UnMarkup->fromHtml("<p><strong>''test</strong>''</p>"));
	}

public function testInlineQuote()
	{
	$this->assertEquals('"test"', $this->ll->UnMarkup->fromHtml('<p><q>test</q></p>'));
	$this->assertEquals("\"test\n\"", $this->ll->UnMarkup->fromHtml("<p>&quot;test\n&quot;</p>"));
	}

public function testQuote()
	{
	$out = '<quote></quote>';
	$in = '<blockquote></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote>test</quote>';
	$in = '<blockquote><p>test</p></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote>test<quote>test2</quote></quote>';
	$in = '<blockquote><p>test</p><blockquote><p>test2</p></blockquote></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote author>test</quote>';
	$in = '<cite>author</cite><blockquote><p>test</p></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = '<quote author>test<quote author2>test2</quote></quote>';
	$in = '<cite>author</cite><blockquote><p>test</p><cite>author2</cite><blockquote><p>test2</p></blockquote></blockquote>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testParagraph()
	{
	$out = "a\n\nb";
	$in = '<p>a</p><p>b</p>';
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "a\nb";
	$in = "<p>a\nb</p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testQuoteAndParagraphs()
	{
	$out = "<quote>a\n\nb</quote>";
	$in = "<blockquote><p>a</p><p>b</p></blockquote>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "<quote>a<quote>b\n\nc</quote></quote>";
	$in = "<blockquote><p>a</p><blockquote><p>b</p><p>c</p></blockquote></blockquote>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$out = "<quote>a<quote>b\n\nc</quote>d</quote>";
	$in = "<blockquote><p>a</p><blockquote><p>b</p><p>c</p></blockquote><p>d</p></blockquote>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testQuoteAndLink()
	{
	$out = '<quote>http://www.laber-land.de/</quote>';
	$in = '<blockquote><p><a href="http://www.laber-land.de/" rel="nofollow" class="link-auto">http://www.laber-land.de/</a></p></blockquote>';
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
	$in = '<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul><p>abc</p>';
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
	$in = "<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul><p>abcd</p><ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi<ul><li>2bii</li></ul></li></ul></li><li>2c</li></ul></li><li>3<ul><li>4</li></ul></li></ul><p>abc</p>";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));

	$in = '<ul><li>a</li></ul><p>b</p><ul><li>c</li></ul>';
	$out = "* a\nb\n* c";
	$this->assertEquals($out, $this->ll->UnMarkup->fromHtml($in));
	}

public function testQuoteAndList()
	{
	$out = "<quote>\n* 1\n* 2\n</quote>";
	$in = "<blockquote><ul><li>1</li><li>2</li></ul></blockquote>";

	$this->assertEquals($out,  $this->ll->UnMarkup->fromHtml($in));

	$out = "* 2\n* 3\n* 4\n<code>\nreg\n</code>\n* 2\n* 3";
	$in = "<ul><li>2</li><li>3</li><li>4</li></ul><pre><code>\nreg\n</code></pre><ul><li>2</li><li>3</li></ul>";

	$this->assertEquals($out,  $this->ll->UnMarkup->fromHtml($in));
	}

public function testCodeAndList()
	{
	$in = "<ul><li><code>a</code></li></ul>";
	$out = "* <code>a</code>";

	$this->assertEquals($out,  $this->ll->UnMarkup->fromHtml($in));
	}

}

?>