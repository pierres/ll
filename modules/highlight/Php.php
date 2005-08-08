<?php

class Php extends HighLight{


protected function setSyntax()
	{
	/*
	Folgendes zur universellen Erkennung von Code.
	Macht alles schön bunt ;-)
*/

// -------------------------------------------------------
	$this->syntax_search[] = '/\\\(&quot;|&gt;|&lt;|&amp;|\S)/eS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'escape\')';
// -------------------------------------------------------
// Kann man nach unten verschieben,
// wenn Variablen in Strings nicht erkannt werden sollen
	$this->syntax_search[] = '/(?:&amp;)?\$\w+/eS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'variable\')';
// -------------------------------------------------------
// String und char-Erekknung nicht immer zuverlässig -> vielleicht doch besser lexikalische Analyse
	$this->syntax_search[] = '/\'.*?\'/seS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'char\')';
// -------------------------------------------------------
	$this->syntax_search[] = '/&quot;.*?&quot;/seS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'string\')';

// -------------------------------------------------------
	$this->syntax_search[] = '/\/\*\*[^\*].+?\*+\//semS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'doc\')';

	$this->syntax_search[] = '/\/\*+.+?\*+\//semS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'comment\')';

	$this->syntax_search[] = '/&lt;!--.+?--&gt;/seS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'comment\')';

	$this->syntax_search[] = '/(\/\/|#).+/meS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'comment\')';
// -------------------------------------------------------
// Ein sehr unschöner Code...
	$this->syntax_search[] = '/('.$this->sep.'\d+'.$this->sep.')|(\d+)/eS';
	$this->syntax_replace[] = '$this->replace(\'$2\', \'integer\', \'$1\')';
// -------------------------------------------------------
	$this->syntax_search[] = '/\b(true|false)\b/ieS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'boolean\')';
// -------------------------------------------------------
	$this->syntax_search[] = '/&lt;\/?.+?&gt;|&lt;\?(?:php)?|\?&gt;/eS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'tag\')';
// -------------------------------------------------------
	/** Warum ist folgendes sooo langsam? */
	$this->syntax_search[] = '/\[|\]|\+\+|--|-&gt;|::|:|=&gt;|&lt;=|==|&lt;|&gt;|-|\+|\*|\/|\.|=|\^|;|&amp;&amp;|&amp;|\|\||\|/eS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'operator\')';

// -------------------------------------------------------
	$this->syntax_search[] = '/\b(require|include|null|as|if|fi|then|else|elseif|while|do|repeat|until|continue|break|switch|case|default|exit|halt|function|programm|class|new|return|and|or|const|var|for|foreach|with|begin|end|echo|final|global|public|private|static|abstract)\b/ieS';
	$this->syntax_replace[] = '$this->replace(\'$1\', \'keyword\')';
// -------------------------------------------------------
	$this->syntax_search[] = '/(\w+)(\s*\()/eS';
	$this->syntax_replace[] = '$this->replace(\'$1\', \'function\').\'$2\'';
// -------------------------------------------------------
	}

/*
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


}
?>