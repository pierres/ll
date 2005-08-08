<?php

abstract class HighLight{

protected $sep 			= '';

protected $codes 		= null;
protected $syntax_search 	= array();
protected $syntax_replace 	= array();


function __construct()
	{
	// Wir nehmen zwei ASCII-Zeichen die sicherlich niemand braucht,
	// um den Code zu markieren
	// Schlechter Stil, ich weiß ;-)
	$this->sep = chr(26);

	$this->codes = new Stack();

	$this->setSyntax();
	}

protected function setSyntax()
	{
	}

/**
	Tolle Funktion; schreibt $in auf den Stack
	und gibt einen Tag zurück, der die Position im Stack enthält
*/
protected function replace($in, $type, $ignore = '')
	{
	if (strlen($in) == 0)
		{
		return  str_replace('\"', '"', $ignore);
		}

	$in = str_replace('\"', '"', $in);


	/** FIXME: Hier ist noch viel zu optimieren */
	// Hier ein paar üble Fixes...
	switch($type)
		{
		case 'doc':
		case 'comment':
			//FIX für Strings etc. in Kommentaren
			$matches = array(array());
			//Finde bereits gefundenen Code in Kommentaren...und ind dessen Codes!...
			while(preg_match_all('/'.$this->sep.'(\d+)'.$this->sep.'/S', $in, $matches) > 0)
				{
				//...und schreibe diesen wieder zurück
				for ($i = 0; $i < count($matches[0]); $i++)
					{
					$replace = $this->codes->fetch($matches[1][$i]);

					$in = str_replace
						(
						$matches[0][$i],
						$replace[0],
						$in
						);
					}
				}

			if($type == 'doc')
				{
				// @param
				$in = preg_replace('/(@param\s+)(.+?)(\s)/eiS', '$this->replace(\'$1\', \'doctag\').$this->replace(\'$2\', \'docparam\').\'$3\'', $in);
				// @blah
				$in = preg_replace('/@\w+/eS', '$this->replace(\'$0\', \'doctag\')', $in);
				// FIXME
				$in = preg_replace('/\bfixme\b/ieS', '$this->replace(\'$0\', \'docfixme\')', $in);
				}

			break;
		}

	$this->codes->push(array($in, $type));

	return $this->sep.$this->codes->lastID().$this->sep;
	}
/**
	Einfache Funktion zur Syntaxerkennung.
	Gefundene Ausdrücke werden nicht nocheinmal bearbeitet;
	dadurch sehr gute Syntaxerkennung
*/
public function toHtml(&$code)
	{
	$code = preg_replace($this->syntax_search, $this->syntax_replace, $code);

	while ($this->codes->hasNext())
		{
		$id = $this->codes->lastID();
		$value = $this->codes->pop();

		$code = str_replace
			(
			$this->sep.$id.$this->sep,
			'<span class="'.$value[1].'">'.$value[0].'</span>',
			$code
			);
		}
	}

}
?>