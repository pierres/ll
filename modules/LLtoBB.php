<?php

class LLtoBB extends Modul{

private $sep;
private $sepc;
private $Stack;

function __construct()
	{
	$this->sep = chr(28);

	$this->Stack = new Stack();
	}


public function convert($text)
	{
	if (empty($text))
		{
		return '';
		}

	# those chars are only used for HTML tags
	# & is transformed to &amp; and cannot be used here
	$noHtml = '[^"<>]';

	$text = preg_replace('#(?:</p>)?<pre><code>#', '[code]', $text);
	$text = preg_replace('#</code></pre>(?:<p>)?#', '[/code]', $text);

	$text = preg_replace('#(?:</p>)?<cite>(.+?)</cite><blockquote>(?:<p>)?#', '[quote=$1]', $text);
	$text = preg_replace('#(?:</p>)?<blockquote>(?:<p>)?#', '[quote]', $text);
	$text = preg_replace("#(?:</p>)?</blockquote>\n*#", "[/quote]\n", $text);

	$text = str_replace('<p>', '', $text);
	$text = str_replace('</p>', "\n\n", $text);

	$text = str_replace('<em>', "[i]", $text);
	$text = str_replace('</em>', "[/i]", $text);

	$text = str_replace('<strong>', "[b]", $text);
	$text = str_replace('</strong>', "[/b]", $text);

	# there is no equivalent bbcode; use i instead
	$text = str_replace('<code>', "[i]", $text);
	$text = str_replace('</code>', "[/i]", $text);

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

	$text = preg_replace("#\n*<ul>\n*#m", "\n[list]\n", $text);
	$text = preg_replace("#\n*</ul>\n*#m", "[/list]\n", $text);
	$text = str_replace('<li>', '[*]', $text);
	$text = str_replace('</li>', "[/*]\n", $text);

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

private function unmakeImage($matches)
	{
	$this->Stack->push('[img]'.urldecode($matches[1]).'[/img]');
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeAutoImage($matches)
	{
	$this->Stack->push('[url]'.urldecode($matches[1]).'[/url]');
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeVideo($matches)
	{
	$this->Stack->push('[url]'.$matches[1].'[/url]');
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeAutoVideo($matches)
	{
	$this->Stack->push('[url]'.$matches[1].'[/url]');
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeAudio($matches)
	{
	$this->Stack->push('[url]'.$matches[1].'[/url]');
	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeNamedLink($matches)
	{
	$this->Stack->push( '[url='.$matches[1].']'.$matches[2].'[/url]');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeAutoLink($matches)
	{
	$this->Stack->push('[url]'.$matches[1].'[/url]');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeSmiley($matches)
	{
	$smilies = array(
		'0:-)' => 'angel',
		':-*)' => 'embarrassed',
		':-*'  => 'kiss',
		':lol:'   => 'laugh',
		':|'  => 'plain',
		':P'  => 'raspberry',
		':('  => 'sad',
		':D'  => 'smile-big',
		':)'  => 'smile',
		':-0'  => 'surprise',
		':/'  => 'uncertain',
		';)'  => 'wink');

	foreach ($smilies as $replace => $search)
		{
		if ($matches[1] == $search)
			{
			return $replace;
			}
		}

	return $matches[1];
	}

}

?>