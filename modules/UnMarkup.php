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


	$text = unhtmlspecialchars($text);

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

// private function unmakeList($list)
// 	{
// 	$list = str_replace('\"', '"', $list);
// 	$pos = 0;
// 	$last = 0;
// 	$ul = 0;
// 	$out = '';
//
// 	while ($pos < strlen($list))
// 		{
// 		if (strpos($list, '<ul>', $pos)
// 			{
// 			$out .= substr($list, $last, $pos-$last);
// 			$ul++;
// 			$pos += 4;
// 			continue;
// 			}
//
// 		if (strpos($list, '<li>', $pos)
// 			{
// 			$out .= substr($list, $last, $pos-$last);
//
// 			for($i = 0; $i < $ul; $i++)
// 				{
// 				$out .= '*';
// 				}
//
// 			$out .= ' ';
//
// 			$li++;
// 			$pos += 4;
// 			continue;
// 			}
//
// 		$pos++;
// 		$last = $pos;
// 		}
// 	}

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
			$line = preg_replace('#<ul>#', "a\n* ", $line);
			$out .= $line."b\n";
			continue;
			}

		$cur = $last + preg_match_all('/<ul>/', $line, $blubb) - preg_match_all('#</ul>#', $line, $blubb);

		$line = preg_replace('#</?ul>#', '', $line);

		for ($i=0; $i < $cur; $i++)
			{
			$out .= 'c*';
			}

		$out .= 'd '.$line."e\n";

		$last = $cur;
		}

	return trim($out);
	}

}

?>