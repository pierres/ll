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
class UnMarkup extends Modul{

private $sep;
private $sepc;
private $Stack;

function __construct()
	{
	$this->sep 	= chr(28);

	$this->Stack = new Stack();
	}


public function fromHtml($text)
	{
	if (empty($text))
		{
		return '';
		}

	/** LL3.1-Kompatibilität */
	$text = str_replace('<br />', "\n", $text);

	$text = preg_replace('/<span class="\w+?">/', '', $text);
	$text = str_replace('</span>', '', $text);

	$text = preg_replace('#<cite>(.+?)</cite><blockquote><div>#', '<quote $1>', $text);
	$text = str_replace('<blockquote><div>', '<quote>', $text);
	$text = str_replace('</div></blockquote>', '</quote>', $text);

	$text = str_replace('<em>', "''", $text);
	$text = str_replace('</em>', "''", $text);

	$text = str_replace('<strong>', "'''", $text);
	$text = str_replace('</strong>', "'''", $text);

	$text = str_replace('<q>', '"', $text);
	$text = str_replace('</q>', '"', $text);

	$text = preg_replace('#<a href="mailto:(.+?)">.+?</a>#', '$1', $text);

	$text = preg_replace_callback('#<!-- cutted --><a href="(.+?)"(?: onclick="return !window\.open\(this\.href\);" rel="nofollow" class="extlink"| class="link")>.+?</a><!-- /cutted -->#', array($this, 'unmakeCuttedLink'), $text);

	$text = preg_replace_callback('#<a href="(.+?)"(?: onclick="return !window\.open\(this\.href\);" rel="nofollow" class="extlink"| class="link")>(.+?)</a>#', array($this, 'unmakeLink'), $text);

	$text = preg_replace_callback('#<img src="images/smilies/[\w-]+.png" alt="([\w-]+)" class="smiley" />#',array($this, 'unmakeSmiley'), $text);

	$text = preg_replace_callback('#<a href="(?:.+?)GetImage(?:.+?)url=(.+?)" onclick="return !window\.open\(this\.href\);" rel="nofollow"><img src="(?:.+?)" alt="" class="image" /></a>#', array($this, 'urldecode'), $text);

	$text = preg_replace_callback('#<ul>.+</ul>#m', array($this, 'unmakeList'), $text);

	/*
	Jetzt schreiben wir wieder alle gefundenen Tags zurück
	*/
	while ($this->Stack->hasNext())
		{
		$text = str_replace
			(
			$this->sep.$this->Stack->lastID().$this->sep,
			$this->Stack->pop(),
			$text
			);
		}

	$text = unhtmlspecialchars($text);

	return $text;
	}

private function urldecode($matches)
	{
	return urldecode($matches[1]);
	}

private function unmakeLocalUrl($url)
	{
	if (empty($url) || strpos($url, '?') === 0 || strpos($url, '/') === 0)
		{
		return 'http'.(!getenv('HTTPS') ? '' : 's').'://'
			.getenv('HTTP_HOST').(strpos($url, '?') === 0 ? '/': '').$url;
		}
	else
		{
		return $url;
		}
	}

private function unmakeLink($matches)
	{
	$url = $matches[1];
	$name = $matches[2];

	$this->Stack->push( '<'.$this->unmakeLocalUrl($url).' '.$name.'>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeCuttedLink($matches)
	{
	$url = $matches[1];
	$this->Stack->push($this->unmakeLocalUrl($url));

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeSmiley($matches)
	{
	foreach (Markup::$smilies as $replace => $search)
		{
		if ($matches[1] == $search)
			{
			return $replace;
			}
		}
	
	return $matches[1];
	}

private function unmakeList($matches)
	{
	$in = str_replace('</li>', '', $matches[0]);

	$out = '';
	$depth = 0;

	while (preg_match('/<(ul|li|\/ul)>([^<]*)(.*)/sS', $in, $matches))
		{
		switch ($matches[1])
			{
			case 'ul' :
				$depth++;
			break;

			case 'li' :
				$out .= str_repeat('*', $depth).' '.$matches[2]."\n";
			break;

			case '/ul' :
				$depth--;
				if ($depth == 0)
					{
					$out .= $matches[2];
					}
			break;
			}

		$in = $matches[3];
		}

	return $out;
	}

}

?>