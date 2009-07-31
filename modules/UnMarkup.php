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
	$this->sep = chr(28);

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

	$text = preg_replace_callback('#<a href="(?:.+?)GetImage(?:.+?)url=(.+?)" rel="nofollow" rev="auto"><img src="(?:.+?)" alt="" class="image" /></a>#', array($this, 'unmakeAutoImage'), $text);
	$text = preg_replace_callback('#<a href="(?:.+?)GetImage(?:.+?)url=(.+?)" rel="nofollow"><img src="(?:.+?)" alt="" class="image" /></a>#', array($this, 'unmakeImage'), $text);

	$text = preg_replace_callback('#<video src="(.+?)" controls="controls" rev="auto"><a href="(?:.+?)" rel="nofollow">(?:.+?)</a></video>#', array($this, 'unmakeAutoVideo'), $text);
	$text = preg_replace_callback('#<video src="(.+?)" controls="controls"><a href="(?:.+?)" rel="nofollow">(?:.+?)</a></video>#', array($this, 'unmakeVideo'), $text);
	$text = preg_replace_callback('#<audio src="(.+?)" controls="controls"><a href="(?:.+?)" rel="nofollow">(?:.+?)</a></audio>#', array($this, 'unmakeAudio'), $text);

	$text = preg_replace_callback('#<a href="(.+?)" rel="nofollow" rev="auto">.+?</a>#', array($this, 'unmakeAutoLink'), $text);
	$text = preg_replace_callback('#<a href="(.+?)" rel="nofollow">(.+?)</a>#', array($this, 'unmakeNamedLink'), $text);

	$text = preg_replace_callback('#<img src="images/smilies/[\w-]+.png" alt="([\w-]+)" class="smiley" />#',array($this, 'unmakeSmiley'), $text);

	$text = preg_replace_callback('#<ul>.+</ul>#m', array($this, 'unmakeList'), $text);

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

private function unmakeImage($matches)
	{
	$this->Stack->push('<img src="'.urldecode($matches[1]).'" />');
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeAutoImage($matches)
	{
	$this->Stack->push(urldecode($matches[1]));
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeVideo($matches)
	{
	$this->Stack->push('<video src="'.$matches[1].'" />');
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeAutoVideo($matches)
	{
	$this->Stack->push($matches[1]);
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeAudio($matches)
	{
	$this->Stack->push('<audio src="'.$matches[1].'" />');
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeNamedLink($matches)
	{
	$this->Stack->push( '<a href="'.$matches[1].'">'.$matches[2].'</a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeAutoLink($matches)
	{
	$this->Stack->push($matches[1]);

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