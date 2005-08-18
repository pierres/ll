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

	$preg_search[] = '#<cite>(.+?)</cite><blockquote><div>#';
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

	$text = preg_replace($preg_search, $preg_replace, $text);
	$text = str_replace($search, $replace, $text);


	$text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');

	return $text;
	}

private function unmakeHeading($text, $level)
	{
	$text = str_replace('\"', '"', $text);

	$eq = '=';

	for ($i=0; $i < $level; $i++)
		{
		$eq .= '=';
		}

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
		case 'wink' 		: return ';-)'; break;
		case 'grin' 		: return ';D'; break;
		case 'rolleyes' 	: return '::)'; break;
		case 'smiley' 		: return ':-)'; break;
		case 'undecided' 	: return ':-\\'; break;
		case 'lipsrsealed' 	: return ':-X'; break;
		case 'embarassed' 	: return ':-['; break;
		case 'kiss' 		: return ':-*'; break;
		case 'angry' 		: return '>:('; break;
		case 'tongue' 		: return ':P'; break;
		case 'cheesy' 		: return ':D'; break;
		case 'sad' 		: return ':-('; break;
		case 'shocked' 		: return ':o'; break;
		case 'cool' 		: return '8)'; break;
		case 'huh' 		: return '???'; break;
		case 'cry' 		: return ':\'('; break;
		default 		: return $smiley;
		}
	}

private function unmakeExtraSmiley($smiley)
	{
	$smiley = str_replace('\"', '"', $smiley);

	return ':'.$smiley.':';
	}

private function unmakeList($list)
	{
	$list = str_replace('\"', '"', $list);


	$list = str_replace('<li>', '', $list);
	$list = str_replace('<ul>', '</li><ul>', $list);
	$list = str_replace('</ul></li>', '</ul>', $list);
	$list = substr($list, 5);

	$lines = explode("</li>", $list);

	$out = '';

	$cur = 0;
	$last = 0;
	$blubb = array(); //die Variable brauchen wir nicht ;-)

	foreach ($lines as $line)
		{
		if (strpos($line, '</ul>') < strpos($line, '<ul>'))
			{
			$line = preg_replace('#</ul>#', '', $line);
			$last = 1;
			$line = preg_replace('#<ul>#', "\n* ", $line);
			$out .= $line."\n";
			continue;
			}

		$cur = $last + preg_match_all('/<ul>/', $line, $blubb) - preg_match_all('#</ul>#', $line, $blubb);

		$line = preg_replace('#</?ul>#', '', $line);

		for ($i=0; $i < $cur; $i++)
			{
			$out .= '*';
			}

		$out .= ' '.$line."\n";

		$last = $cur;
		}

	return trim($out);
	}

}

?>