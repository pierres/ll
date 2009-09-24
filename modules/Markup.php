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

class MarkupException extends RuntimeException {

function __construct($message)
	{
	parent::__construct($message, 0);
	}

}

class Markup extends Modul {

private $loop = 0;
private $maxLoop = 10000;
private $maxLength = 1000000;
private $sep = '';
private $sepc = '';
private $Stack = null;

public static $smilies = array(
	'0:-)' => 'angel',
	':-*)' => 'embarrassed',
	':-*'  => 'kiss',
	'xD'   => 'laugh',
	':-|'  => 'plain',
	':-P'  => 'raspberry',
	':-('  => 'sad',
	':-D'  => 'smile-big',
	':-)'  => 'smile',
	':-0'  => 'surprise',
	':-/'  => 'uncertain',
	';-)'  => 'wink');


function __construct()
	{
	$this->sep = chr(28);
	}

private function loopCheck()
	{
	if ($this->loop++ > $this->maxLoop)
		{
		throw new MarkupException('Maximum loop count exceeded');
		}
	}

private function createStackLink($string)
	{
	$this->loopCheck();
	$this->Stack->push($string);
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function complieFirstPass($text)
	{
	$protocoll 	= '(?:https?|ftp):\/\/';
	$name 		= '[a-z0-9](?:[a-z0-9_\-\.]*[a-z0-9])?';
	$tld 		= '[a-z]{2,5}';
	$domain		=  $name.'\.'.$tld;
	$address	= '(?:'.$domain.'|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})';
	$path 		= '(?:\/(?:[a-z0-9_%&:;,\+\-\/=~\.#]*[a-z0-9\/])?)?';
	$request 	= '(?:\?[a-z0-9_%&:;,\+\-\/=~\.#\[\]]*[a-z0-9=\[\]])?';
	$img	 	= '[a-z0-9_\-]+\.(?:gif|jpe?g|png)';
	$video	 	= '[a-z0-9_\-]+\.(?:ogg|ogm|ogv)';

	# restricted HTML support
	$text = preg_replace_callback('#<pre>(.+?)</pre>#s', array($this, 'makePre'), $text);
	$text = preg_replace_callback('#<code>(.+?)</code>#', array($this, 'makeCode'), $text);

	$text = preg_replace_callback('#<a href="('.$protocoll.'.+?)">(.+?)</a>#', array($this, 'makeNamedLink'), $text);
	$text = preg_replace_callback('#<img src="('.$protocoll.'.+?)" />#', array($this, 'makeImage'), $text);	
	$text = preg_replace_callback('#<audio src="('.$protocoll.'.+?)" />#', array($this, 'makeAudio'), $text);
	$text = preg_replace_callback('#<video src="('.$protocoll.'.+?)" />#', array($this, 'makeVideo'), $text);

	# auto detection for some URLs
	$text = preg_replace_callback('/'.$protocoll.$address.$path.$request.$video.'/i', array($this, 'makeAutoVideo'), $text);
	$text = preg_replace_callback('/'.$protocoll.$address.$path.$request.$img.'/i', array($this, 'makeAutoImage'), $text);
	$text = preg_replace_callback('/'.$protocoll.$address.$path.$request.'/i', array($this, 'makeAutoLink'), $text);

	foreach (self::$smilies as $search => $replace)
		{
		$text = str_replace(
			$search,
			$this->createStackLink('<img src="images/smilies/'.$replace.'.png" alt="'.$replace.'" class="smiley" />'),
			$text);
		}

	return $text;
	}

private function complieSecondPass($text)
	{
	$text = preg_replace_callback("#'''(.+?)'''#", array($this, 'makeStrong'), $text);
	$text = preg_replace_callback("#''(.+?)''#", array($this, 'makeEm'), $text);
	$text = preg_replace_callback('/&quot;(.+?)&quot;/', array($this, 'makeInlineQuote'), $text);
	$text = preg_replace_callback('/(?:^\*+ [^\n]+$\n?)+/m',array($this, 'makeList'), $text);

	return $text;
	}

public function toHtml($text)
	{
	if (empty($text))
		{
		return '';
		}

	if (strlen($text) > $this->maxLength)
		{
		throw new MarkupException('Maximum text length exceeded');
		}

	$this->loop = 0;
	$this->Stack = new Stack();

	$text = str_replace($this->sep, '', $text);
	$text = str_replace("\r", '', $text);

	$text = $this->complieFirstPass($text);
	$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
	$text = $this->complieSecondPass($text);
	$text = preg_replace('/\n{2,}/', "\n\n", $text);
	$text = $this->makeQuoteAndParagraph($text);

	while ($this->Stack->hasNext())
		{
		$text = str_replace
			(
			$this->sep.$this->Stack->lastID().$this->sep,
			$this->Stack->pop(),
			$text
			);
		}

	return $text;
	}

private function makePre($matches)
	{
	return $this->createStackLink('<pre>'.htmlspecialchars($matches[1], ENT_COMPAT, 'UTF-8').'</pre>');
	}

private function makeCode($matches)
	{
	return $this->createStackLink('<code>'.htmlspecialchars($matches[1], ENT_COMPAT, 'UTF-8').'</code>');
	}

private function makeQuoteAndParagraph($text)
	{
	$pos = 0;
	$ppos = 0;
	$last = 0;
	$maxPos = strlen($text)-1;
	$out = '';
	$open = 0;
	$popen = 0;

	while ($pos <= $maxPos)
		{
		$this->loopCheck();

		$start = strpos($text, '&lt;quote', $pos);
		$end = strpos($text, '&lt;/quote&gt;', $pos);
		$ppos = strpos($text, "\n\n", $pos);

		if ($popen == 0 && $start !== $pos && $end !== $pos && $pos <= $maxPos)
			{
			$out .= '<p>';
			$popen++;
			}
		elseif ($open == 0 && $end !== false && $start === false && $pos === $end)
			{
			throw new MarkupException('quote closed but not started');
			}
		elseif ($open == 0 && $ppos !== false && ($start === false || $ppos < $start) && ($end == false || $ppos < $end))
			{
			$out .= substr($text, $last, $ppos-$last);
			$out .= '</p>';
			$popen--;
			$pos = $ppos + 2;
			}
		elseif ($ppos === false && $popen > 0 && $pos == $maxPos)
			{
			$out .= substr($text, $last);
			$out .= '</p>';
			$popen--;
			break;
			}
		elseif ($start !== false && $end !== false && $start < $end)
			{
			$out .= substr($text, $last, $start-$last);
			$quote = substr($text, $start);

			if (preg_match('#^&lt;quote (.+?)&gt;#s', $quote, $matches))
				{
				$pos = $start+13+strlen($matches[1])+1;
				$open++;
				if ($popen > 0)
					{
					$out .= '</p>';
					$popen--;
					}
				$out .= '<cite>'.$matches[1].'</cite><blockquote>';
				}
			elseif (preg_match('#^&lt;quote&gt;#', $quote))
				{
				$pos = $start+13;
				$open++;
				if ($popen > 0)
					{
					$out .= '</p>';
					$popen--;
					}
				$out .= '<blockquote>';
				}
			else
				{
				throw new MarkupException('quote tag incomplete');
				}
			}
		elseif ($end !== false)
			{
			$out .= substr($text, $last, $end-$last);
			if ($open > 0)
				{
				$pos = $end+14;
				$open--;
				if ($popen > 0)
					{
					$out .= '</p>';
					$popen--;
					}
				$out .= '</blockquote>';
				}
			else
				{
				throw new MarkupException('quote closed but not started');
				}
			}
		elseif ($start !== false && $end === false)
			{
			throw new MarkupException('quote not closed');
			}
		else
			{
			$out .= substr($text, $last, $maxPos-$last);
			$pos = $maxPos;
			}

		$last = $pos;
		}

	if ($open > 0)
		{
		throw new MarkupException('quote not closed');
		}

	if ($popen > 0)
		{
		throw new MarkupException('paragraph not closed');
		}

	return $out;
	}

private function makeList($matches)
	{
	$out = '';
	$last = 0;

	foreach (explode("\n", trim($matches[0])) as $line)
		{
		$this->loopCheck();

		$cur = 0;

		# get the current depth
		while (strlen($line) > $cur && $line[$cur] == '*')
			{
			$cur++;
			}

		# go down
		if ($cur == $last+1)
			{
			$out .= '<ul>';
			}
		# go high
		elseif ($cur < $last)
			{
			$out .= '</li>'.str_repeat('</ul></li>', $last-$cur);
			}
		elseif ($cur > $last+1)
			{
			throw new MarkupException('incorrect list depth');
			}
		else
			{
			$out .= '</li>';
			}

		# add line without '* '
		$out .= '<li>'.substr($line, $cur+1);

		$last = $cur;
		}

	# close all open tags
	$out .= str_repeat('</li></ul>', $cur);

	return $this->createStackLink($out);
	}

private function makeEm($matches)
	{
	return $this->createStackLink('<em>'.$matches[1].'</em>');
	}

private function makeStrong($matches)
	{
	return $this->createStackLink('<strong>'.$matches[1].'</strong>');
	}

private function makeInlineQuote($matches)
	{
	return $this->createStackLink('<q>'.$matches[1].'</q>');
	}

private function makeNamedLink($matches, $auto = false)
	{
	if ($auto)
		{
		$url = htmlspecialchars($matches[0], ENT_COMPAT, 'UTF-8');
		$name = $url;
		$rev = ' class="link-auto"';
		}
	else
		{
		$url = htmlspecialchars($matches[1], ENT_COMPAT, 'UTF-8');
		$name = htmlspecialchars($matches[2], ENT_COMPAT, 'UTF-8');
		$rev = '';
		}

	return $this->createStackLink('<a href="'.$url.'" rel="nofollow"'.$rev.'>'.$name.'</a>');
	}

private function makeAutoLink($matches)
	{
	return $this->makeNamedLink($matches, true);
	}

private function makeImage($matches, $auto = false)
	{
	if ($auto)
		{
		$url = $matches[0];
		$rev = ' class="link-auto"';
		}
	else
		{
		$url = $matches[1];
		$rev = '';
		}

	return $this->createStackLink('<a href="?page=GetImage&amp;url='.urlencode($url).'" rel="nofollow"'.$rev.'><img src="?page=GetImage&amp;thumb=1&amp;url='.urlencode($url).'" alt="" class="image" /></a>');
	}

private function makeAutoImage($matches)
	{
	return $this->makeImage($matches, true);
	}

private function makeVideo($matches, $auto = false)
	{
	if ($auto)
		{
		$url = htmlspecialchars($matches[0], ENT_COMPAT, 'UTF-8');
		$rev = ' class="link-auto"';
		}
	else
		{
		$url = htmlspecialchars($matches[1], ENT_COMPAT, 'UTF-8');
		$rev = '';
		}

	return $this->createStackLink('<video src="'.$url.'" controls="controls"><a href="'.$url.'" rel="nofollow"'.$rev.'>'.$url.'</a></video>');
	}

private function makeAutoVideo($matches)
	{
	return $this->makeVideo($matches, true);
	}

private function makeAudio($matches)
	{
	$url = htmlspecialchars($matches[1], ENT_COMPAT, 'UTF-8');

	return $this->createStackLink('<audio src="'.$url.'" controls="controls"><a href="'.$url.'" rel="nofollow">'.$url.'</a></audio>');
	}

}

?>