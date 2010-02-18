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

	# those chars are only used for HTML tags
	# & is transformed to &amp; and cannot be used here
	$noHtml = '[^"<>]';

	$text = preg_replace('#(?:</p>)?<pre><code>#', '<code>', $text);
	$text = preg_replace('#</code></pre>(?:<p>)?#', '</code>', $text);

	$text = preg_replace('#(?:</p>)?<cite>('.$noHtml.'+?)</cite><blockquote>(?:<p>)?#', '<quote $1>', $text);
	$text = preg_replace('#(?:</p>)?<blockquote>(?:<p>)?#', '<quote>', $text);
	$text = preg_replace('#(?:</p>)?</blockquote>#', '</quote>', $text);

	$text = str_replace('<p>', '', $text);
	$text = str_replace('</p>', "\n\n", $text);

	$text = str_replace('<em>', "''", $text);
	$text = str_replace('</em>', "''", $text);

	$text = str_replace('<strong>', "'''", $text);
	$text = str_replace('</strong>', "'''", $text);

	$text = str_replace('<q>', '"', $text);
	$text = str_replace('</q>', '"', $text);

	$text = preg_replace_callback('#<a href="(?:'.$noHtml.'+?)GetImage(?:'.$noHtml.'+?)url=('.$noHtml.'+?)" rel="nofollow" class="link-auto"><img src="(?:'.$noHtml.'+?)" alt="" class="image" /></a>#', array($this, 'unmakeAutoImage'), $text);
	$text = preg_replace_callback('#<a href="(?:'.$noHtml.'+?)GetImage(?:'.$noHtml.'+?)url=('.$noHtml.'+?)" rel="nofollow"><img src="(?:'.$noHtml.'+?)" alt="" class="image" /></a>#', array($this, 'unmakeImage'), $text);

	$text = preg_replace_callback('#<video src="('.$noHtml.'+?)" controls="controls"><a href="(?:'.$noHtml.'+?)" rel="nofollow" class="link-auto">(?:'.$noHtml.'+?)</a></video>#', array($this, 'unmakeAutoVideo'), $text);
	$text = preg_replace_callback('#<video src="('.$noHtml.'+?)" controls="controls"><a href="(?:'.$noHtml.'+?)" rel="nofollow">(?:'.$noHtml.'+?)</a></video>#', array($this, 'unmakeVideo'), $text);
	$text = preg_replace_callback('#<audio src="('.$noHtml.'+?)" controls="controls"><a href="(?:'.$noHtml.'+?)" rel="nofollow">(?:'.$noHtml.'+?)</a></audio>#', array($this, 'unmakeAudio'), $text);

	$text = preg_replace_callback('#<a href="('.$noHtml.'+?)" rel="nofollow" class="link-auto">'.$noHtml.'+?</a>#', array($this, 'unmakeAutoLink'), $text);
	$text = preg_replace_callback('#<a href="('.$noHtml.'+?)" rel="nofollow">('.$noHtml.'+?)</a>#', array($this, 'unmakeNamedLink'), $text);

	$text = preg_replace_callback('#<img src="images/smilies/[\w-]+\.png" alt="([\w-]+)" class="smiley" />#',array($this, 'unmakeSmiley'), $text);

	$text = preg_replace_callback("#(?:\n\n)?<ul>.+</ul>#m", array($this, 'unmakeList'), $text);

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

	return trim($text);
	}

public function fromHtmlToText($text)
	{
	$replace = array(
		'#</li>#',
		'#</p>#',
		'#<pre><code>.+?</code></pre>#s',
		'#<code>.+?</code>#',
		'#<audio.+?</audio>#',
		'#<video.+?</video>#',
		'/\s+/');

	$text = preg_replace($replace, ' ', $text);
	$text = strip_tags($text);
	$text = cutString($text, 400);

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
	$in = $matches[0];
	$out = '';
	$depth = 0;
	$pos = 0;
	$maxPos = strlen($in)-1;

	while ($pos <= $maxPos)
		{
		$listart = strpos($in, '<li>', $pos);
		$liend = strpos($in, '</li>', $pos);
		$ulstart = strpos($in, '<ul>', $pos);
		$ulend = strpos($in, '</ul>', $pos);

		if ($ulstart === $pos)
			{
			$pos += 4;
			$depth++;
			}
		elseif ($listart === $pos)
			{
			if ($ulstart !== false && $ulstart < $liend)
				{
				$out .= str_repeat('*', $depth).' '.substr($in, $listart + 4, $ulstart - $listart - 4)."\n";
				$pos = $ulstart;
				}
			else
				{
				$out .= str_repeat('*', $depth).' '.substr($in, $listart + 4, $liend - $listart - 4)."\n";
				$pos = $liend + 5;
				}
			}
		elseif ($liend === $pos)
			{
			$pos += 5;
			}
		elseif ($ulend === $pos)
			{
			$pos += 5;
			$depth--;
			}
		else
			{
			$pos = $ulstart;
			}
		}

	return $out;
	}

}

?>