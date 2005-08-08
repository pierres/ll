<?php

class Html extends HighLight{


protected function setSyntax()
	{
	$this->syntax_search[] = '/&lt;!--.+?--&gt;/seS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'comment\')';
// -------------------------------------------------------
	$this->syntax_search[] = '/\'.*?\'/seS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'char\')';
// -------------------------------------------------------
	$this->syntax_search[] = '/&quot;.*?&quot;/seS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'string\')';
// -------------------------------------------------------
	$this->syntax_search[] = '/=/eS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'operator\')';
// -------------------------------------------------------
	$this->syntax_search[] = '/&lt;\/?.+?&gt;/eS';
	$this->syntax_replace[] = '$this->replace(\'$0\', \'tag\')';
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