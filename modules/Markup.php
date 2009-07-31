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
/** FIXME: maximale Tiefe in Schleifen festlegen ->DOS */
class Markup extends Modul{


private $sep = '';
private $sepc = '';
/*
	Zwischenspeicher für gefundene Tags
*/
private $Stack = null;
private $Codes = null;

private $linkNumber = 1;
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
	$this->sep 	= chr(28);
	$this->sepc 	= chr(26);

	$this->Stack = new Stack();
	$this->Codes = new Stack();
	}

private function createStackLink($string)
	{
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
	$request 	= '(?:\?[a-z0-9_%&:;,\+\-\/=~\.#]*[a-z0-9])?';
	$img	 	= '[a-z0-9_\-]+\.(?:gif|jpe?g|png)';


	/** Code muß am Zeilenanfang beginnen */
	$text = preg_replace_callback('#^<code>$(.+?)^</code>$#sm', array($this, 'makeCode'), $text);
	/** Inline Code */
	$text = preg_replace_callback('/==(.+?)==/', array($this, 'makeInlineCode'), $text);

	/** komplette URL mit Namen */
	$text = preg_replace_callback('/<('.$protocoll.$address.$path.$request.') (.+?)>/is', array($this, 'makeNamedLink'), $text);
	/** www.domain.tld  mit Namen */
	$text = preg_replace_callback('/<(www\.'.$domain.$path.$request.') (.+?)>/is',  array($this, 'makeNamedWWWLink'), $text);
	/** ftp.domain.tld  mit Namen */
	$text = preg_replace_callback('/<(ftp\.'.$domain.$path.$request.') (.+?)>/is',  array($this, 'makeNamedFTPLink'), $text);
	/** komplette URL */
	$text = preg_replace_callback('/<('.$protocoll.$address.$path.$request.')>/is', array($this, 'makeNumberedLink'), $text);
	/** www.domain.tld */
	$text = preg_replace_callback('/<(www\.'.$domain.$path.$request.')>/is', array($this, 'makeNumberedWWWLink'), $text);
	/** ftp.domain.tld */
	$text = preg_replace_callback('/<(ftp\.'.$domain.$path.$request.')>/is', array($this, 'makeNumberedFTPLink'), $text);

	/** E-Mails */
	$text = preg_replace_callback('/'.$name.'@'.$domain.'/i', array($this, 'makeEmail'), $text);

	/** Bilder */
	$text = preg_replace_callback('/'.$protocoll.$address.$path.$img.'/i', array($this, 'makeImage'), $text);
	/** Bilder www.domain.tld */
	$text = preg_replace_callback('/www\.'.$domain.$path.$img.'/i', array($this, 'makeWWWImage'), $text);
	/** Bilder ftp.domain.tld */
	$text = preg_replace_callback('/ftp\.'.$domain.$path.$img.'/i', array($this, 'makeFTPImage'), $text);

	/** komplette URL */
	$text = preg_replace_callback('/'.$protocoll.$address.$path.$request.'/i', array($this, 'makeLink'), $text);
	/** www.domain.tld */
	$text = preg_replace_callback('/www\.'.$domain.$path.$request.'/i', array($this, 'makeWWWLink'), $text);
	/** ftp.domain.tld */
	$text = preg_replace_callback('/ftp\.'.$domain.$path.$request.'/i', array($this, 'makeFTPLink'), $text);

	return $text;
	}

private function complieSecondPass($text)
	{
	/** Hervorhebungen */
	$text = preg_replace_callback('#//([^/\n]+?)//#', array($this, 'makeEm'), $text);

	$text = preg_replace_callback('/\*\*([^\*\s](?:[^\*\n]*?[^\*\s])?)\*\*/', array($this, 'makeStrong'), $text);

	$text = preg_replace_callback('/&quot;(.+?)&quot;/', array($this, 'makeInlineQuote'), $text);

	$text = preg_replace('/^----+$(\n?)/m', '<hr />$1', $text);

	$text = preg_replace_callback('/--(.+?)--/', array($this, 'makeDel'), $text);

	$text = preg_replace_callback('/\+\+(.+?)\+\+/', array($this, 'makeIns'), $text);
	
	foreach (self::$smilies as $search => $replace)
		{
		$text = str_replace(
			$search,
			$this->createStackLink('<img src="images/smilies/'.$replace.'.png" alt="'.$replace.'" class="smiley" />'),
			$text);
		}

	/** Listen */
	$text = preg_replace_callback('/(?:^\*+ [^\n]+$\n?)+/m',array($this, 'makeList'), $text);

	/** Zitate */
	$text = preg_replace_callback('#&lt;quote(?: .+?)?&gt;.+&lt;/quote&gt;#s', array($this, 'makeQuote'), $text);

	return $text;
	}

/**
* @param &$text Text
*/
public function toHtml($text)
	{
	if (empty($text))
		{
		return '';
		}

	// Man weiß ja nie ....
	$text = str_replace($this->sep, '', $text);
	$text = str_replace($this->sepc, '', $text);
	$text = str_replace("\r", '', $text);	//Wer braucht schon Windows-Zeilenumbrche?

	$text = $this->complieFirstPass($text);
	$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
	$text = $this->complieSecondPass($text);

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

	$text = preg_replace('/\n{2,}/', '<br /><br />', $text);
	/** Altes Verhalten bei Zeilenumbrüchen */
	$text = str_replace("\n", '<br />', $text);
	$text = preg_replace('/\s{1,}/', ' ', $text);

	while ($this->Codes->hasNext())
		{
		$text = str_replace
			(
			$this->sepc.$this->Codes->lastID().$this->sepc,
			$this->Codes->pop(),
			$text
			);
		}

	return $text;
	}

private function makeCode($matches)
	{
	$this->Codes->push('<pre>'.htmlspecialchars($matches[1], ENT_COMPAT, 'UTF-8').'</pre>');

	return $this->sepc.$this->Codes->lastID().$this->sepc;
	}

private function makeInlineCode($matches)
	{
	$this->Codes->push('<code>'.htmlspecialchars($matches[1], ENT_COMPAT, 'UTF-8').'</code>');

	return $this->sepc.$this->Codes->lastID().$this->sepc;
	}

private function makeQuote($matches)
	{
	$text = $matches[0];
	$pos = 0;
	$last = 0;
	$maxPos = strlen($text);
	$out = '';
	$open = 0;

	while ($pos <= $maxPos)
		{
		$start = strpos($text, '&lt;quote', $pos);
		$end = strpos($text, '&lt;/quote&gt;', $pos);

		if ($start !== false && $end !== false && $start < $end)
			{
			$out .= substr($text, $last, $start-$last);
			$quote = substr($text, $start);

			if (preg_match('#^&lt;quote (.+?)&gt;#s', $quote, $matches))
				{
				$pos = $start+13+strlen($matches[1])+1;
				$open++;
				$out .= '<cite>'.$matches[1].'</cite><blockquote><div>';
				}
			elseif (preg_match('#^&lt;quote&gt;#', $quote))
				{
				$pos = $start+13;
				$open++;
				$out .= '<blockquote><div>';
				}
			else
				{
				$out .= '&lt;quote';
				$pos = $start+9;
				}
			}
		elseif ($end !== false)
			{
			$out .= substr($text, $last, $end-$last);
			if ($open > 0)
				{
				$pos = $end+14;
				$open--;
				$out .= '</div></blockquote>';
				}
			else
				{
				$pos = $end+14;
				$out .= '&lt;/quote&gt;';
				}
			}
		else
			{
			break;
			}

		$last = $pos;
		}

	/* Alle geöffneten Tags auf jeden Fall schließen */
	$out .= str_repeat('</div></blockquote>', $open);

	return $out;
	}

/**
	erzeugt Listenelemente (auch geschachtelt)
*/
private function makeList($matches)
	{
	$out = '';
	$last = 0;

	foreach (explode("\n", trim($matches[0])) as $line)
		{
		$cur = 0;

		/* Ermittle die aktuelle Tiefe */
		while (strlen($line) > $cur && $line[$cur] == '*')
			{
			$cur++;
			}

		/* eine Ebene tiefer */
		if ($cur == $last+1)
			{
			$out .= '<ul>';
			}
		elseif ($cur > $last)
			{
			$line = substr($line, $cur-$last-1);
			$cur = $last + 1;

			$out .= '<ul>';
			}
		/* eine oder mehrere Ebene höher */
		elseif ($cur < $last)
			{
			$out .= '</li>'.str_repeat('</ul></li>', $last-$cur);
			}
		else
			{
			$out .= '</li>';
			}

		/* Füge Zeile ohne Ebenenzeichen und Leerzeichen (+1) hinzu */
		$out .= '<li>'.substr($line, $cur+1);

		$last = $cur;
		}

	/* Alle geöffneten Tags auf jeden Fall schließen */
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

private function makeDel($matches)
	{
	return $this->createStackLink('<span><del>'.$matches[1].'</del></span>');
	}

private function makeIns($matches)
	{
	return $this->createStackLink('<span><ins>'.$matches[1].'</ins></span>');
	}

private function makeLink($matches)
	{
	$matches[1] = $matches[0];
	$matches[2] = $matches[0];

	return $this->makeNamedLink($matches, true);
	}

private function makeWWWLink($matches)
	{
	$matches[1] = $matches[0];
	$matches[2] = $matches[0];

	return $this->makeNamedWWWLink($matches, true);
	}

private function makeFTPLink($matches)
	{
	$matches[1] = $matches[0];
	$matches[2] = $matches[0];

	return $this->makeNamedFTPLink($matches, true);
	}

private function isLocalHost($url)
	{
	$request = parse_url($url);

	try
		{
		return ($this->Input->getHost() == $request['host']);
		}
	catch (RequestException $e)
		{
		return false;
		}
	}

private function makeLocalUrl($url)
	{
	$request = parse_url($url);

	$path = empty($request['path']) ? '' : substr($request['path'], 1);
	$query = empty($request['query']) ? '' : '?'.$request['query'];
	$fragment = empty($request['fragment']) ? '' : '#'.$request['fragment'];

	$newUrl = $path.$query.$fragment;

	return empty($newUrl) ? $url : $newUrl;
	}

private function makeNumberedLink($matches)
	{
	$url = $matches[1];

	$name = $this->linkNumber;
	$this->linkNumber++;

	if ($this->isLocalHost($url))
		{
		$target = ' class="link"';
		$url = $this->makeLocalUrl($url);
		}
	else
		{
		$target = ' onclick="return !window.open(this.href);" rel="nofollow" class="extlink"';
		}

	return $this->createStackLink($this->tagElement('numbered', '<a href="'.htmlspecialchars($url, ENT_COMPAT, 'UTF-8').'"'.$target.'>['.$name.']</a>'));
	}

private function makeNumberedWWWLink($matches)
	{
	$matches[1] = 'http://'.$matches[1];
	return $this->makeNumberedLink($matches);
	}

private function makeNumberedFTPLink($matches)
	{
	$matches[1] = 'ftp://'.$matches[1];
	return $this->makeNumberedLink($matches);
	}

private function makeNamedLink($matches, $cutName = false)
	{
	$url = $matches[1];
	$name = $matches[2];

	if ($this->isLocalHost($url))
		{
		$target = ' class="link"';
		$url = $this->makeLocalUrl($url);
		// $url == $name?
		if ($matches[1] == $matches[2])
			{
			$name = $url;
			}
		}
	else
		{
		$target = ' onclick="return !window.open(this.href);" rel="nofollow" class="extlink"';
		}

	if ($cutName && strlen($name) > 50)
		{
		$name = mb_substr($name, 0, 37, 'UTF-8').'...'.mb_substr($name, -10, null, 'UTF-8');
		$cutted = true;
		}
	else
		{
		$cutted = false;
		}


	$link = '<a href="'.htmlspecialchars($url, ENT_COMPAT, 'UTF-8').'"'.$target.'>'.htmlspecialchars($name, ENT_COMPAT, 'UTF-8').'</a>';

	if ($cutted)
		{
		$link = $this->tagElement('cutted', $link);
		}

	return $this->createStackLink($link);
	}

private function tagElement($tag, $element)
	{
	return '<!-- '.$tag.' -->'.$element.'<!-- /'.$tag.' -->';
	}


private function makeNamedWWWLink($matches, $cutName = false)
	{
	$matches[1] = 'http://'.$matches[1];
	return $this->makeNamedLink($matches, $cutName);
	}

private function makeNamedFTPLink($matches, $cutName = false)
	{
	$matches[1] = 'ftp://'.$matches[1];
	return $this->makeNamedLink($matches, $cutName);
	}

private function makeImage($matches)
	{
	return $this->createStackLink('<a href="'.$this->Output->createUrl('GetImage', array('url' => $matches[0])).'" onclick="return !window.open(this.href);" rel="nofollow"><img src="'.$this->Output->createUrl('GetImage', array('thumb' => 1, 'url' => $matches[0])).'" alt="" class="image" /></a>');
	}

private function makeWWWImage($matches)
	{
	$matches[0] = 'http://'.$matches[0];
	return $this->makeImage($matches);
	}

private function makeFTPImage($matches)
	{
	$matches[0] = 'ftp://'.$matches[0];
	return $this->makeImage($matches);
	}

private function makeEmail($matches)
	{
	$email = htmlspecialchars($matches[0], ENT_COMPAT, 'UTF-8');

	return $this->createStackLink('<a href="mailto:'.$email.'">'.$email.'</a>');
	}

}

?>