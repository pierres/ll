<?php

/**
*
* @author Pierre Schmitz
*/
class Markup extends Modul{


private $sep 			= '';
private $sepc 			= '';
/*
	Zwischenspeicher für gefundene Tags
*/
private $Stack			= null;
private $Codes 			= null;
private $quotes 		= 0;

private $linkNumber 		= 1;

private $smilies 		= array();

private $HighLight 		= null;

private $smiliesenabled		= true;


public function enableSmilies($value = true)
	{
	$this->smiliesenabled = ($value ? true : false);
	}


function __construct()
	{
	$this->sep 	= chr(28);
	$this->sepc 	= chr(26);

	$this->Stack = new Stack();
	$this->Codes = new Stack();

	$this->smilies = array(
		';-)' => 'wink',
		';)' => 'wink',
		';D' => 'grin',
		'::)' => 'rolleyes',
		':-)' => 'smiley',
		':)' => 'smiley',
		':-\\' => 'undecided',
		':-/' => 'undecided',
		':-X' => 'lipsrsealed',
		':-x' => 'lipsrsealed',
		':-[' => 'embarassed',
		':-*' => 'kiss',
		'&gt;:\(' => 'angry',
		':P' => 'tongue',
		':p' => 'tongue',
		':D' => 'cheesy',
		':-(' => 'sad',
		':(' => 'sad',
		':O' => 'shocked',
		':o' => 'shocked',
		'8)' => 'cool',
		'???' => 'huh',
		':\'(' => 'cry',
		'\'(' => 'cry');
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
	$text = preg_replace_callback('#^<code>$(.+?)^</code>$\n?#sm', array($this, 'makeCode'), $text);
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
	/** Zitate */
	$text = preg_replace_callback('#&lt;quote(?: .+?)?&gt;.+&lt;/quote&gt;#s', array($this, 'makeQuote'), $text);
	/** Überschriften */
	$text = preg_replace_callback('/^(!{1,6})(.+?)$(\n?)/m', array($this, 'makeHeading'), $text);
	/** Hervorhebungen */
	$text = preg_replace('#//([^/\n]+?)//#', '<em>$1</em>', $text);

	$text = preg_replace('/\*\*([^\*\s](?:[^\*\n]*?[^\*\s])?)\*\*/', '<strong>$1</strong>', $text);

	$text = preg_replace('/&quot;(.+?)&quot;/', '<q>$1</q>', $text);

	$text = preg_replace('/^----+$(\n?)/m', '<hr />$1', $text);

	$text = preg_replace('/--(.+?)--/', '<span><del>$1</del></span>', $text);

	$text = preg_replace('/\+\+(.+?)\+\+/', '<span><ins>$1</ins></span>', $text);

	/** Listen */
	$text = preg_replace_callback('/(?:^\*+ [^\n]+$\n?)+/m',array($this, 'makeList'), $text);

	return $text;
	}

private function compileSmilies($text)
	{
	//;-) ;)
	$text = preg_replace_callback('/(^|\s)(;-?\))($|\W)/', array($this, 'makeSmiley'), $text);
	//;D
	$text = preg_replace_callback('/(^|\s)(;D)($|\W)/', array($this, 'makeSmiley'), $text);
	//::)
	$text = preg_replace_callback('/(^|\s)(::\))($|\W)/', array($this, 'makeSmiley'), $text);
	//:-) :)
	$text = preg_replace_callback('/(^|\s)(:-?\))($|\W)/', array($this, 'makeSmiley'), $text);
	//:-\ :-/
	$text = preg_replace_callback('/(^|\s)(:-\\\|:-\/)($|\W)/', array($this, 'makeSmiley'), $text);
	//:-X :-x
	$text = preg_replace_callback('/(^|\s)(:-X)($|\W)/i', array($this, 'makeSmiley'), $text);
	//:-[
	$text = preg_replace_callback('/(^|\s)(:-\[)($|\W)/', array($this, 'makeSmiley'), $text);
	//:-*
	$text = preg_replace_callback('/(^|\s)(:-\*)($|\W)/', array($this, 'makeSmiley'), $text);
	//>:(
	$text = preg_replace_callback('/(^|\s)(&gt;:\()($|\W)/', array($this, 'makeSmiley'), $text);
	//:P :p
	$text = preg_replace_callback('/(^|\s)(:P)($|\W)/i', array($this, 'makeSmiley'), $text);
	//:D
	$text = preg_replace_callback('/(^|\s)(:D)($|\W)/', array($this, 'makeSmiley'), $text);
	//:-( :(
	$text = preg_replace_callback('/(^|\s)(:-?\()($|\W)/', array($this, 'makeSmiley'), $text);
	//:o :O
	$text = preg_replace_callback('/(^|\s)(:o)($|\W)/i', array($this, 'makeSmiley'), $text);
	//8)
	$text = preg_replace_callback('/(^|\s)(8\))($|\W)/', array($this, 'makeSmiley'), $text);
	//???
	$text = preg_replace_callback('/(^|\s)(\?\?\?)($|\W)/', array($this, 'makeSmiley'), $text);
	//'( :'(
	$text = preg_replace_callback('/(^|\s)(:?\'\()($|\W)/', array($this, 'makeSmiley'), $text);

	$text = preg_replace_callback('/&lt;(\w{2,15})&gt;/', array($this, 'makeExtraSmiley'), $text);

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

	if ($this->smiliesenabled)
		{
		$text = $this->compileSmilies($text);
		}

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
// -------------------------------------------------------
private function openQuote($matches)
	{
	$this->quotes++;
	return (empty($matches[1]) ? '' : '<cite>'.$matches[1].'</cite>').'<blockquote><div>';
	}

private function closeQuote()
	{
	if ($this->quotes == 0)
		{
		return '';
		}
	else
		{
		$this->quotes--;
		return '</div></blockquote>';
		}
	}
/*
	Nach langem Hin und Her bin ich auf folgende sehr einfache Lösung gekommen
	[quote] erhöht quotes um 1, [/quote] zieht 1 ab.
	Bei 0 wird nicht mehr geschlossen und sollte am Ende quotes > 0 sein,
	sind noch Tags zu schließen.
	Das sollte eigentlich immer zuverlässig funktionieren.
*/
/** FIXME: Vereinfachnung wie unmakeList() */
private function makeQuote($matches)
	{
	$matches[0] = preg_replace_callback('#&lt;quote(?: (.+?))?&gt;\s*#', array($this, 'openQuote'), $matches[0]);
	$matches[0] = preg_replace_callback('#\s*&lt;/quote&gt;#', array($this, 'closeQuote'), $matches[0]);

	while ($this->quotes > 0)
		{
		$matches[0] .= '</div></blockquote>';
		$this->quotes--;
		}

	return $matches[0];
	}
// -------------------------------------------------------
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

	return $out;
	}

private function makeHeading($matches)
	{
	$level = strlen($matches[1]);
	return '<h'.$level.'>'.$matches[2].'</h'.$level.'>'.$matches[3];
	}

private function makeLink($matches)
	{
	$matches[1] = $matches[0];
	$matches[2] = $matches[0];

	return $this->makeNamedLink($matches);
	}

private function makeWWWLink($matches)
	{
	$matches[1] = $matches[0];
	$matches[2] = $matches[0];

	return $this->makeNamedWWWLink($matches);
	}

private function makeFTPLink($matches)
	{
	$matches[1] = $matches[0];
	$matches[2] = $matches[0];

	return $this->makeNamedFTPLink($matches);
	}

private function makeNumberedLink($matches)
	{
	$url = $matches[1];

	$name = '['.$this->linkNumber.']';
	$this->linkNumber++;

	if (strpos($url, $this->Settings->getValue('domain')) !== false)
		{
		$target = ' class="link"';
		}
	else
		{
		$target = ' onclick="return !window.open(this.href);" rel="nofollow" class="extlink"';
		}

	$this->Stack->push('<a href="'.htmlspecialchars($url, ENT_COMPAT, 'UTF-8').'"'.$target.'>'.htmlspecialchars($name, ENT_COMPAT, 'UTF-8').'</a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
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

private function makeNamedLink($matches)
	{
	$url = $matches[1];
	$name = $matches[2];

	if (strlen($name) > 50)
		{
		$name = mb_substr($name, 0, 37, 'UTF-8').'...'.mb_substr($name, -10, 'UTF-8');
		}

	if (strpos($url, $this->Settings->getValue('domain')) !== false)
		{
		$target = ' class="link"';
		}
	else
		{
		$target = ' onclick="return !window.open(this.href);" rel="nofollow" class="extlink"';
		}

	$this->Stack->push('<a href="'.htmlspecialchars($url, ENT_COMPAT, 'UTF-8').'"'.$target.'>'.htmlspecialchars($name, ENT_COMPAT, 'UTF-8').'</a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function makeNamedWWWLink($matches)
	{
	$matches[1] = 'http://'.$matches[1];
	return $this->makeNamedLink($matches);
	}

private function makeNamedFTPLink($matches)
	{
	$matches[1] = 'ftp://'.$matches[1];
	return $this->makeNamedLink($matches);
	}

private function makeImage($matches)
	{
	$url = urlencode($matches[0]);

	$this->Stack->push('<a href="?page=GetImage;url='.$url.'" onclick="return !window.open(this.href);" rel="nofollow"><img src="?page=GetImage;thumb;url='.$url.'" alt="" class="image" /></a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
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

	$this->Stack->push('<a href="mailto:'.$email.'">'.$email.'</a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function makeSmiley($matches)
	{
	$this->Stack->push('<img src="images/smilies/'.$this->smilies[$matches[2]].'.gif" alt="'.$this->smilies[$matches[2]].'" class="smiley" />');

	return $matches[1].$this->sep.$this->Stack->lastID().$this->sep.$matches[3];
	}

private function makeExtraSmiley($matches)
	{
	$smilies = array('Mr-T','afro','alien','angel','angry','annoyed','antlers','anxious','argue','army','artist','baby','balloon','balloon2','balloon3','bandana','batman','beadyeyes','beadyeyes2','beam','beatnik','beatnik2','behead','behead2','bigcry','biker','blank','blush','bobby','bobby2','bomb','bomb2','book','book2','bow','brood','bucktooth','builder','builder2','bulb','bulb2','charming','cheesy','chef','chinese','clown','computer','confused','cool','cool2','cool3','cool4','cowboy','crown','crowngrin','cry','cry2','curtain','cyclist','daisy','dead','deal','deal2','devil','devilish','disappointed','disguise','dizzy','dizzy2','dozey','drummer','drunk','dunce','dunce2','earmuffs','ears','egypt','elf','elvis','embarassed','end','evil','evil2','evil3','evilgrin','fireman','freak','furious','furious2','furious3','glasses','glasses2','goofy','gorgeous','gossip','greedy','grin','grin2','grin3','guitarist','hair','hair2','hanged','happy','happy2','hat','hat2','heart','helmet','help','hippy','huh2','idea','idea2','idea3','iloveyou','indian_brave','indian_chief','inquisitive','jester','joker','juggle','juggle2','karate','kid','kiss','kiss2','klingon','knife','laugh','laugh2','laugh3','laugh4','leer','lips','lips2','lipsrsealed','lipsrsealed2','lost','love','mad','mask','mean','mellow','mickey','moustache','nice','no','oops','operator','party','party2','party3','pimp','pimp2','pirate','pleased','policeman','pumpkin','punk','rifle','rockstar','rolleyes','rolleyes2','rolleyes3','rolleyes4','rolleyes5','sad','sad2','sad3','santa','santa2','santa3','scholar','shame','shifty','shocked','shocked2','shocked3','shout','shy','sick','sick2','singer','skull','sleep','sleeping','sleepy','smart','smartass','smartass2','smash','smile','smiley','smiley2','smitten','smoking','smug','smug2','sneaky','snobby','snore','sombrero','speechless','square','stare','stars','stooge_curly','stooge_larry','stooge_moe','stop','stunned','stupid','sultan','sunny','surprised','sweatdrop','sweetheart','thinking','thinking2','thumbsdown','thumbsup','tiny','tired','toff','toilet','tongue','tongue2','tongue3','uhoh','uhoh2','undecided','uneasy','vampire','vanish','veryangry','veryangry2','vulcan','wacko','wacky','wall','whip','wideeyed','wings','wink','wink2','wink3','wiseguy','withclever','worried','worried2','wreck','wry','xmas','yes','zzz');

	if (in_array($matches[1], $smilies))
		{
		$this->Stack->push('<img src="images/smilies/extra/'.$matches[1].'.gif" alt="'.$matches[1].'" class="smiley" />');
		return $this->sep.$this->Stack->lastID().$this->sep;
		}
	else
		{
		return '&lt;'.$matches[1].'&gt;';
		}
	}

}

?>