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
	$this->search[]  = '#^&lt;code(?: (\w{3,8}))?&gt;$(.+?)^&lt;/code&gt;$#esm';//<code>.+</code>
	$this->replace[] = '$this->makeCode(\'$2\', \'$1\')';

	$this->search[]  = '#&lt;quote(?:=.+?)?&gt;.+&lt;/quote&gt;#esm';//<quote=...>...</quote>
	$this->replace[] = '$this->makeQuote(\'$0\')';

	/** Listen */
	$this->search[]  = '/(?:^\*+\s.+$\n)*^\*+\s.+$/em';
	$this->replace[] = '$this->makeList(\'$0\')';

	/** Überschriften */
	$this->search[]  = '/^=(={1,6})=*(.+?)=*$/me';
	$this->replace[] = '$this->makeHeading(\'$2\', \'$1\')';

	/** neu: Hervorhebungen */
	$this->search[]  = '/\'\'(.+?)\'\'/';
	$this->replace[] = '<em>$1</em>';

	$this->search[]  = '/!!(.+?)!!/';
	$this->replace[] = '<strong>$1</strong>';

	$this->search[]  = '/--(.+?)--/';
	$this->replace[] = '<del>$1</del>';

	$this->search[]  = '/\+\+(.+?)\+\+/';
	$this->replace[] = '<ins>$1</ins>';


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
/*
	Sind das alle Shortcuts? Folgende List ist zumindest aus LL2.
	Erstze \W mit \s (Tip von Felix)
*/
	$this->smilies_search[] = '/(^|\s)(;-?\))($|\W)/e';		//;-) ;)
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'wink\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(;D)($|\W)/e';		//;D
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'grin\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(::\))($|\W)/e';		//::)
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'rolleyes\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:-?\))($|\W)/e';		//:-) :)
	$this->smilies_replace[]   = '\'$1\'.$this->makeSmiley(\'smiley\').\'$3\'';

	$this->smilies_search[] = '/(^|\s)(:-\\\|:-\/)($|\W)/e';	//:-\ ;-/
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

	$this->smilies_search[] = '/(^|\s):(\w{2,15}):($|\W)/e';
	$this->smilies_replace[] = '\'$1\'.$this->makeExtraSmiley(\'$2\').\'$3\'';
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
	/** Altest Verhalten bei Zeilenumbrüchen */
	$text = preg_replace('/\n/', '<br />', $text);

	/** Keine Zeilenumbrüche entfernen; Das macht sonst Probleme bei UnMarkup z.B. bei Listen */
	$text = preg_replace('/[^\n\S]{1,}/', ' ', $text);
	//$text = preg_replace('/\s{1,}/', ' ', $text);

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

private function makeCode($in, $type = '')
	{
	/** FIXME Vielleicht kann man dieses blöde Veerhalten von PHP ausschalten */
	$in = str_replace('\"', '"', $in);

	$type = ucfirst(strtolower($type));

	if (!empty($type) && file_exists(PATH.'/modules/highlight/'.$type.'.php'))
		{
		require_once(PATH.'/modules/highlight/'.$type.'.php');

		$highlight = new $type();
		//den Code bunt einfärben
		$highlight->toHtml($in);

		$this->Codes->push('<pre class="'.strtolower($type).'">'.$in.'</pre>');
		}
	else
		{
		$this->Codes->push('<pre>'.$in.'</pre>');
		}

	return $this->sepc.$this->Codes->lastID().$this->sepc;
	}
// -------------------------------------------------------
private function openQuote($cite = '')
	{
	$cite = str_replace('\"', '"', $cite);

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
private function makeQuote($in)
	{
	$in = str_replace('\"', '"', $in);

	$in = preg_replace
		(
		array('#&lt;quote(?:=(.+?))?&gt;#em'  , '#&lt;/quote&gt;#em'),
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
	$in = trim(str_replace('\"', '"', $in));

	$lines = explode("\n", $in);

	$out = '';

	$cur = 0;
	$last = 0;
	$li = 0;

	for($i=0; $i<count($lines);$i++)
		{
		$line = $lines[$i];

		/**
		Auf welcher Listenebene sind wir?
		Die erste Ebene sollte 1 sein
		Das hier sollte schneller als ein regulärer Ausdruck sein
		*/
		if ($last > 0)
			{
			$cur = $this->getListDepth($line);
			}
		else
			{
			$cur = 1;
			}

		$next = ($i+1 < count($lines) ? $this->getListDepth($lines[$i+1]) : 0);

		/* eine Ebene tiefer */
		if ($cur > $last)
			{
			for ($j = $cur; $j > $last; $j--)
				{
				$out .= '<ul>';
				}
			}
		/* eine Ebene höher */
		elseif ($cur < $last)
			{
			for ($j = $last; $j > $cur; $j--)
				{
				$out .= '</ul>';

				if ($li > 0)
					{
					$out .= '</li>';
					$li--;
					}
				}
			}
		/* Füge Zeile ohne Ebenenzeichen und Leerzeichen (+1) hinzu */
		if ($cur < $next)
			{
			$out .= '<li>'.substr($line, $cur+1);
			$li++;
			}
		else
			{
			$out .= '<li>'.substr($line, $cur+1).'</li>';
			}

		$last = $cur;
		}

	/* Alle geöffneten Tags auf jeden Fall schließen */
	while ($cur > 0)
		{
		$out .= '</ul>';
		$cur--;

		if ($li > 0)
			{
			$out .= '</li>';
			$li--;
			}
		}

	return $out;
	}

private function getListDepth($line)
	{
	$cur = 0;
	/* Ermittle die aktuelle Tiefe */
	while (strlen($line) > $cur && $line[$cur] == '*')
		{
		$cur++;
		}
	return $cur;
	}

private function makeHeading($text, $level)
	{
	$text = str_replace('\"', '"', $text);

	$level = strlen($level);

	return '<h'.$level.'>'.$text.'</h'.$level.'>';
	}

private function makeLink($url, $name = '')
	{
	$url = str_replace('\"', '"', $url);
	$name = str_replace('\"', '"', $name);

	if (empty($name))
		{
		$name = '['.$this->linkNumber.']';
		$this->linkNumber++;
		}
	elseif (strlen($name) > 50)
		{
		$name = substr($name, 0, 37).'...'.substr($name, -10);
		}

	/** FIXME: externer oder interner Link? */
	if (strpos($url, Settings::DOMAIN) !== false)
		{
		$target = ' class="link"';
		}
	else
		{
		$target = ' onclick="openLink(this)" rel="nofollow" class="extlink"';
		}

	$this->Stack->push('<a href="'.$url.'"'.$target.'>'.$name.'</a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function makeImage($url)
	{
	$url = str_replace('\"', '"', $url);

	$this->Stack->push('<img src="'.$url.'" alt="" class="image" onclick="openImage(this)" />');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function makeEmail($email)
	{
	$email = str_replace('\"', '"', $email);

	$this->Stack->push('<a href="mailto:'.$email.'">'.$email.'</a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function makeSmiley($smiley)
	{
	$smiley = str_replace('\"', '"', $smiley);

	$this->Stack->push('<img src="images/smilies/'.$smiley.'.gif" alt="'.$smiley.'" class="smiley" />');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function makeExtraSmiley($smiley)
	{
	$smiley = str_replace('\"', '"', $smiley);

	if (file_exists(PATH.'images/smilies/extra/'.$smiley.'.gif'))
		{
		$this->Stack->push('<img src="images/smilies/extra/'.$smiley.'.gif" alt="'.$smiley.'" class="smiley" />');
		return $this->sep.$this->Stack->lastID().$this->sep;
		}
	else
		{
		return ':'.$smiley.':';
		}
	}

}

?>