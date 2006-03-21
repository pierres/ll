<?php


class UnMarkup extends Modul{


public function fromHtml($text)
	{
	if (empty($text))
		{
		return '';
		}

	/** LL3.1-Kompatibilität */
	$search[] = '<br />';
	$replace[] = "\n";

	$search[] = '<pre>';
	$replace[] = '<code>';
	$search[] = '</pre>';
	$replace[] = '</code>';

	$preg_search[] = '/<pre class="(\w{3,8})">/';
	$preg_replace[] = '<code $1>';

	$preg_search[] = '/<span class="\w+?">/';
	$preg_replace[] = '';
	$search[] = '</span>';
	$replace[] = '';

	$preg_search[] = '#<h[1-6]>(.+?)</h([1-6])>#e';
	$preg_replace[] = '$this->unmakeHeading(\'$1\',\'$2\')';

	/** FIXME: Keine saubere Lösung, aber Test ist "grün" */
	$preg_search[] = '#<cite>(.+?)</cite><quote>#';
	$preg_replace[] = '<quote=$1>';
	$search[] = '<blockquote><div>';
	$replace[] = '<quote>';
	$search[] = '</div></blockquote>';
	$replace[] = '</quote>';

	$search[] = '<em>';
	$replace[] = '\'\'';
	$search[] = '</em>';
	$replace[] = '\'\'';

	$search[] = '<strong>';
	$replace[] = '!!';
	$search[] = '</strong>';
	$replace[] = '!!';

	$search[] = '<del>';
	$replace[] = '--';
	$search[] = '</del>';
	$replace[] = '--';

	$search[] = '<ins>';
	$replace[] = '++';
	$search[] = '</ins>';
	$replace[] = '++';

	$preg_search[] = '#<a href="mailto:(.+?)">.+?</a>#';
	$preg_replace[] = '$1';

	$preg_search[] = '#<a href="(.+?)"(?: onclick="openLink\(this\)" rel="nofollow" class="extlink"| class="link")?>(.+?)</a>#e';
	$preg_replace[] = '$this->unmakeLink(\'$1\', \'$2\')';

	$preg_search[] = '#<img src="images/smilies/\w+.gif" alt="(\w+)" class="smiley" />#e';
	$preg_replace[] ='$this->unmakeSmiley(\'$1\')';

	$preg_search[] = '#<img src="images/smilies/extra/\w+.gif" alt="(\w+)" class="smiley" />#e';
	$preg_replace[] ='$this->unmakeExtraSmiley(\'$1\')';

	$preg_search[] = '#<img src="(.+?)" alt="" class="image" (?:onclick="openImage\(this\)" )?/>#';
	$preg_replace[] = '$1';

	$preg_search[] = '#<ul>.+</ul>#es';
	$preg_replace[] = '$this->unmakeList(\'$0\')';

	/** FIXME: Prüfe, ob Reihenfolge relevant sein kann -> Ja, bei Zitaten mit Autor*/
	$text = str_replace($search, $replace, $text);
	$text = preg_replace($preg_search, $preg_replace, $text);

	$text = unhtmlspecialchars($text);

	return $text;
	}

private function unmakeHeading($text, $level)
	{
	$text = str_replace('\"', '"', $text);

	$eq = str_repeat('=', $level);

	return $eq.$text.$eq;
	}

private function unmakeLink($url, $name)
	{
	$url = str_replace('\"', '"', $url);
	$name = str_replace('\"', '"', $name);

	if (preg_match('/^\[\d+\]$/', $name))
		{
		return '<'.$url.'>';
		}
	/** FIXME: keine schöne Lösung...könnte schiefgehen */
	if 	(
		$name !== $url
		&& !empty($name)
		&& strpos($url, $name) === false
		&& strpos($name, '...') === false
		&& strlen($name) != 50
		)
		{
		return '<'.$url.' '.$name.'>';
		}
	else
		{
		return $url;
		}
	}

private function unmakeSmiley($smiley)
	{
	$smiley = str_replace('\"', '"', $smiley);

	switch($smiley)
		{
		case 'wink' 		: return ';-)';
		case 'grin' 		: return ';D';
		case 'rolleyes' 	: return '::)';
		case 'smiley' 		: return ':-)';
		case 'undecided' 	: return ':-\\';
		case 'lipsrsealed' 	: return ':-X';
		case 'embarassed' 	: return ':-[';
		case 'kiss' 		: return ':-*';
		case 'angry' 		: return '>:(';
		case 'tongue' 		: return ':P';
		case 'cheesy' 		: return ':D';
		case 'sad' 		: return ':-(';
		case 'shocked' 		: return ':o';
		case 'cool' 		: return '8)';
		case 'huh' 		: return '???';
		case 'cry' 		: return ':\'(';
		default 		: return $smiley;
		}
	}

private function unmakeExtraSmiley($smiley)
	{
	$smiley = str_replace('\"', '"', $smiley);

	return ':'.$smiley.':';
	}

private function unmakeList($in)
	{
	$in = str_replace('\"', '"', $in);
	$in = str_replace('</li>', '', $in);

	$out = '';
	$depth = 0;

	while (preg_match('/<([^>]*)>([^<]*)(.*)/sS', $in, $matches))
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