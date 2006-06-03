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
/*
	Such-Arrays:
*/
private $search 		= array();
private $replace 		= array();
private $codeSearch 		= array();
private $codeReplace 		= array();
private $smilies_search		= array();
private $smilies_replace	= array();

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


	$protocoll 	= '(?:https?|ftp):\/\/';
	$name 		= '[a-z0-9](?:[a-z0-9_\-\.]*[a-z0-9])?';
	$tld 		= '[a-z]{2,5}';
	$domain		=  $name.'\.'.$tld;
	$address	= '(?:'.$domain.'|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})';
	$path 		= '(?:\/(?:[a-z0-9_%&:;,\+\-\/=~\.#]*[a-z0-9\/])?)?';
	$request 	= '(?:\?[a-z0-9_%&:;,\+\-\/=~\.#]*[a-z0-9])?';
	$img	 	= '[a-z0-9_\-]+\.(?:gif|jpe?g|png)';


	/** Code muß am Zeilenanfang beginnen */
	$this->codeSearch[]  = '#^<code>$(.+?)^</code>$\n?#esm';
	$this->codeReplace[] = '$this->makeCode(\'$1\')';
	/** Inline Code */
	$this->codeSearch[]  = '/==(.+?)==/e';
	$this->codeReplace[] = '$this->makeCode(\'$1\', \'code\')';

	/** Zitate */
	$this->search[]  = '#&lt;quote(?: .+?)?&gt;.+&lt;/quote&gt;#es';
	$this->replace[] = '$this->makeQuote(\'$0\')';

	/** komplette URL mit Namen */
	$this->search[]  = '/&lt;('.$protocoll.$address.$path.$request.') (.+?)&gt;/ies';
	$this->replace[] = '$this->makeLink(\'$1\', \'$2\')';
	/** www.domain.tld  mit Namen */
	$this->search[]  = '/&lt;(www\.'.$domain.$path.$request.') (.+?)&gt;/ies';
	$this->replace[] = '$this->makeLink(\'http://$1\', \'$2\')';
	/** ftp.domain.tld  mit Namen */
	$this->search[]  = '/&lt;(ftp\.'.$domain.$path.$request.') (.+?)&gt;/ies';
	$this->replace[] = '$this->makeLink(\'ftp://$1\', \'$2\')';
	/** komplette URL */
	$this->search[]  = '/&lt;('.$protocoll.$address.$path.$request.')&gt;/ies';
	$this->replace[] = '$this->makeLink(\'$1\')';
	/** www.domain.tld */
	$this->search[]  = '/&lt;(www\.'.$domain.$path.$request.')&gt;/ies';
	$this->replace[] = '$this->makeLink(\'http://$1\')';
	/** ftp.domain.tld */
	$this->search[]  = '/&lt;(ftp\.'.$domain.$path.$request.')&gt;/ies';
	$this->replace[] = '$this->makeLink(\'ftp://$1\')';
/*
	Folgendes sollte eigentlich alle URLs finden.
	Auch (www.laber-land.de) und www.laber-land.de!
*/
	/** E-Mails */
	$this->search[] = '/'.$name.'@'.$domain.'/ie';
	$this->replace[] = '$this->makeEmail(\'$0\')';

	/** Bilder */
	$this->search[] = '/'.$protocoll.$address.$path.$img.'/ie';
	$this->replace[] = '$this->makeImage(\'$0\')';
	/** Bilder www.domain.tld */
	$this->search[] = '/www\.'.$domain.$path.$img.'/ie';
	$this->replace[] = '$this->makeImage(\'http://$0\')';
	/** Bilder ftp.domain.tld */
	$this->search[] = '/ftp\.'.$domain.$path.$img.'/ie';
	$this->replace[] = '$this->makeImage(\'ftp://$0\')';

	/** komplette URL */
	$this->search[] = '/'.$protocoll.$address.$path.$request.'/ie';
	$this->replace[] = '$this->makeLink(\'$0\', \'$0\')';
	/** www.domain.tld */
	$this->search[] = '/www\.'.$domain.$path.$request.'/ie';
	$this->replace[] = '$this->makeLink(\'http://$0\', \'$0\')';
	/** ftp.domain.tld */
	$this->search[] = '/ftp\.'.$domain.$path.$request.'/ie';
	$this->replace[] = '$this->makeLink(\'ftp://$0\', \'$0\')';


	/** Überschriften */
	$this->search[]  = '/^(!{1,6})(.+?)$(\n?)/me';
	$this->replace[] = '$this->makeHeading(\'$2\', strlen(\'$1\')).\'$3\'';
	/** Hervorhebungen */
	$this->search[]  = '#//([^/\n]+?)//#';
	$this->replace[] = '<em>$1</em>';

	$this->search[]  = '/\*\*([^\*\s](?:[^\*\n]*?[^\*\s])?)\*\*/';
	$this->replace[] = '<strong>$1</strong>';

	$this->search[]  = '/&quot;(.+?)&quot;/';
	$this->replace[] = '<q>$1</q>';

	$this->search[]  = '/^----+$(\n?)/m';
	$this->replace[] = '<hr />$1';

	$this->search[]  = '/--(.+?)--/';
	$this->replace[] = '<span><del>$1</del></span>';

	$this->search[]  = '/\+\+(.+?)\+\+/';
	$this->replace[] = '<span><ins>$1</ins></span>';

	/** Listen */
	$this->search[]  = '/(?:^\*+ [^\n]+$\n?)+/em';
	$this->replace[] = '$this->makeList(\'$0\')';

	$this->smilies_search[] = '/(^|\s)(;-?\))($|\W)/e';		//;-) ;)
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'wink\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(;D)($|\W)/e';		//;D
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'grin\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(::\))($|\W)/e';		//::)
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'rolleyes\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:-?\))($|\W)/e';		//:-) :)
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'smiley\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:-\\\|:-\/)($|\W)/e';	//:-\ :-/
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'undecided\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:-X)($|\W)/ie';		//:-X :-x
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'lipsrsealed\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:-\[)($|\W)/e';		//:-[
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'embarassed\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:-\*)($|\W)/e';		//:-*
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'kiss\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(&gt;:\()($|\W)/e';		//>:(
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'angry\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:P)($|\W)/ei';		//:P :p
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'tongue\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:D)($|\W)/e';		//:D
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'cheesy\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:-?\()($|\W)/e';		//:-( :(
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'sad\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:o)($|\W)/ei';		//:o :O
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'shocked\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(8\))($|\W)/e';		//8)
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'cool\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(\?\?\?)($|\W)/e';		//???
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'huh\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:?\'\()($|\W)/e';		//'( :'(
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'cry\').\'$3\'';

	$this->smilies_search[] = '/&lt;(\w{2,15})&gt;/e';
	$this->smilies_replace[] = '$this->makeExtraSmiley(\'$1\')';
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

	$text = preg_replace($this->codeSearch, $this->codeReplace, $text);
	$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
	$text = preg_replace($this->search, $this->replace, $text);

	if ($this->smiliesenabled)
		{
		$text = preg_replace($this->smilies_search, $this->smilies_replace, $text);
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

private function makeCode($in, $tag = 'pre')
	{
	/** FIXME Vielleicht kann man dieses blöde Veerhalten von PHP ausschalten
		-> preg_replace_callback
	*/
	$in = str_replace('\"', '"', $in);

	$this->Codes->push('<'.$tag.'>'.htmlspecialchars($in, ENT_COMPAT, 'UTF-8').'</'.$tag.'>');

	return $this->sepc.$this->Codes->lastID().$this->sepc;
	}
// -------------------------------------------------------
private function openQuote($cite = '')
	{
	$this->quotes++;
	return (empty($cite) ? '' : '<cite>'.$cite.'</cite>').'<blockquote><div>';
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
private function makeQuote($in)
	{
	$in = preg_replace
		(
		array('#&lt;quote(?: (.+?))?&gt;\s*#e'  , '#\s*&lt;/quote&gt;#e'),
		array('$this->openQuote(\'$1\')', '$this->closeQuote()'),
		$in
		);

	while ($this->quotes > 0)
		{
		$in .= '</div></blockquote>';
		$this->quotes--;
		}

	return $in;
	}
// -------------------------------------------------------
/**
	erzeugt Listenelemente (auch geschachtelt)
*/
private function makeList($in)
	{
	$out = '';
	$last = 0;

	foreach (explode("\n", trim($in)) as $line)
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

private function makeHeading($text, $level)
	{
	return '<h'.$level.'>'.$text.'</h'.$level.'>';
	}

private function makeLink($url, $name = '')
	{
	if (empty($name))
		{
		$name = '['.$this->linkNumber.']';
		$this->linkNumber++;
		}
	elseif (strlen($name) > 50)
		{
		// Verhindere das Abschneiden im Entity
		$name = unhtmlspecialchars($name);
		$name = substr($name, 0, 37).'...'.substr($name, -10);
		$name = htmlspecialchars($name, ENT_COMPAT, 'UTF-8');
		}

	if (strpos($url, $this->Settings->getValue('domain')) !== false)
		{
		$target = ' class="link"';
		}
	else
		{
		$target = ' onclick="return !window.open(this.href);" rel="nofollow" class="extlink"';
		}

	$this->Stack->push('<a href="'.$url.'"'.$target.'>'.$name.'</a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function makeImage($url)
	{
	$this->Stack->push('<a href="?page=GetImage;url='.urlencode($url).'" onclick="return !window.open(this.href);" rel="nofollow"><img src="?page=GetImage;thumb;url='.urlencode($url).'" alt="" class="image" /></a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function makeEmail($email)
	{
// 	$email = rehtmlspecialchars($email);

	$this->Stack->push('<a href="mailto:'.$email.'">'.$email.'</a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function makeSmiley($smiley)
	{
	$this->Stack->push('<img src="images/smilies/'.$smiley.'.gif" alt="'.$smiley.'" class="smiley" />');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function makeExtraSmiley($smiley)
	{
	$smilies = array('Mr-T','afro','alien','angel','angry','annoyed','antlers','anxious','argue','army','artist','baby','balloon','balloon2','balloon3','bandana','batman','beadyeyes','beadyeyes2','beam','beatnik','beatnik2','behead','behead2','bigcry','biker','blank','blush','bobby','bobby2','bomb','bomb2','book','book2','bow','brood','bucktooth','builder','builder2','bulb','bulb2','charming','cheesy','chef','chinese','clown','computer','confused','cool','cool2','cool3','cool4','cowboy','crown','crowngrin','cry','cry2','curtain','cyclist','daisy','dead','deal','deal2','devil','devilish','disappointed','disguise','dizzy','dizzy2','dozey','drummer','drunk','dunce','dunce2','earmuffs','ears','egypt','elf','elvis','embarassed','end','evil','evil2','evil3','evilgrin','fireman','freak','furious','furious2','furious3','glasses','glasses2','goofy','gorgeous','gossip','greedy','grin','grin2','grin3','guitarist','hair','hair2','hanged','happy','happy2','hat','hat2','heart','helmet','help','hippy','huh2','idea','idea2','idea3','iloveyou','indian_brave','indian_chief','inquisitive','jester','joker','juggle','juggle2','karate','kid','kiss','kiss2','klingon','knife','laugh','laugh2','laugh3','laugh4','leer','lips','lips2','lipsrsealed','lipsrsealed2','lost','love','mad','mask','mean','mellow','mickey','moustache','nice','no','oops','operator','party','party2','party3','pimp','pimp2','pirate','pleased','policeman','pumpkin','punk','rifle','rockstar','rolleyes','rolleyes2','rolleyes3','rolleyes4','rolleyes5','sad','sad2','sad3','santa','santa2','santa3','scholar','shame','shifty','shocked','shocked2','shocked3','shout','shy','sick','sick2','singer','skull','sleep','sleeping','sleepy','smart','smartass','smartass2','smash','smile','smiley','smiley2','smitten','smoking','smug','smug2','sneaky','snobby','snore','sombrero','speechless','square','stare','stars','stooge_curly','stooge_larry','stooge_moe','stop','stunned','stupid','sultan','sunny','surprised','sweatdrop','sweetheart','thinking','thinking2','thumbsdown','thumbsup','tiny','tired','toff','toilet','tongue','tongue2','tongue3','uhoh','uhoh2','undecided','uneasy','vampire','vanish','veryangry','veryangry2','vulcan','wacko','wacky','wall','whip','wideeyed','wings','wink','wink2','wink3','wiseguy','withclever','worried','worried2','wreck','wry','xmas','yes','zzz');

	if (in_array($smiley, $smilies))
		{
		$this->Stack->push('<img src="images/smilies/extra/'.$smiley.'.gif" alt="'.$smiley.'" class="smiley" />');
		return $this->sep.$this->Stack->lastID().$this->sep;
		}
	else
		{
		return '&lt;'.$smiley.'&gt;';
		}
	}

}

?>