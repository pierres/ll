<?php


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

	$text = str_replace('<code>', '==', $text);
	$text = str_replace('</code>', '==', $text);

	$text = str_replace('<pre>', '<code>', $text);
	$text = str_replace('</pre>', "</code>", $text);

	$text = preg_replace('/<span class="\w+?">/', '', $text);
	$text = str_replace('</span>', '', $text);

	$text = preg_replace_callback('#<h[1-6]>(.+?)</h([1-6])>#', array($this, 'unmakeHeading'), $text);

	$text = preg_replace('#<cite>(.+?)</cite><blockquote><div>#', '<quote $1>', $text);
	$text = str_replace('<blockquote><div>', '<quote>', $text);
	$text = str_replace('</div></blockquote>', '</quote>', $text);

	$text = str_replace('<em>', '//', $text);
	$text = str_replace('</em>', '//', $text);

	$text = str_replace('<strong>', '**', $text);
	$text = str_replace('</strong>', '**', $text);

	$text = str_replace('<hr />', "----\n", $text);

	$text = str_replace('<q>', '"', $text);
	$text = str_replace('</q>', '"', $text);

	$text = str_replace('<span><del>', '--', $text);
	$text = str_replace('</del>', '--', $text);

	$text = str_replace('<span><ins>', '++', $text);
	$text = str_replace('</ins>', '++', $text);

	$text = preg_replace('#<a href="mailto:(.+?)">.+?</a>#', '$1', $text);


	$text = preg_replace_callback('#<!-- numbered --><a href="(.+?)"(?: onclick="return !window\.open\(this\.href\);" rel="nofollow" class="extlink"| class="link")>\[\d+?\]</a><!-- /numbered -->#', array($this, 'unmakeNumberedLink'), $text);

	$text = preg_replace_callback('#<!-- cutted --><a href="(.+?)"(?: onclick="return !window\.open\(this\.href\);" rel="nofollow" class="extlink"| class="link")>.+?</a><!-- /cutted -->#', array($this, 'unmakeCuttedLink'), $text);

	$text = preg_replace_callback('#<a href="(.+?)"(?: onclick="return !window\.open\(this\.href\);" rel="nofollow" class="extlink"| class="link")>(.+?)</a>#', array($this, 'unmakeLink'), $text);


	$text = preg_replace_callback('#<img src="images/smilies/\w+.gif" alt="(\w+)" class="smiley" />#',array($this, 'unmakeSmiley'), $text);

	$text = preg_replace_callback('#<img src="images/smilies/extra/\w+.gif" alt="(\w+)" class="smiley" />#',array($this, 'unmakeExtraSmiley'), $text);

	$text = preg_replace_callback('#<a href="\?page=GetImage;url=(.+?)" onclick="return !window\.open\(this\.href\);" rel="nofollow"><img src="\?page=GetImage;thumb;url=(.+?)" alt="" class="image" /></a>#', array($this, 'urldecode'), $text);

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

private function unmakeHeading($matches)
	{
	return str_repeat('!', $matches[2]).$matches[1];
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

private function unmakeNumberedLink($matches)
	{
	$url = $matches[1];
	$this->Stack->push( '<'.$this->unmakeLocalUrl($url).'>');

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
	/** TODO: Stack verwenden */

	switch($matches[1])
		{
		case 'wink' 			: return ';-)';
		case 'grin' 			: return ';D';
		case 'rolleyes' 		: return '::)';
		case 'smiley' 			: return ':-)';
		case 'undecided' 		: return ':-\\';
		case 'lipsrsealed' 		: return ':-X';
		case 'embarassed' 		: return ':-[';
		case 'kiss' 			: return ':-*';
		case 'angry' 			: return '>:(';
		case 'tongue' 			: return ':P';
		case 'cheesy' 			: return ':D';
		case 'sad' 			: return ':-(';
		case 'shocked' 			: return ':o';
		case 'cool' 			: return '8)';
		case 'huh' 			: return '???';
		case 'cry' 			: return ':\'(';
		default 			: return $matches[1];
		}
	}

private function unmakeExtraSmiley($matches)
	{
	$this->Stack->push( '<'.$matches[1].'>');

	return $this->sep.$this->Stack->lastID().$this->sep;
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