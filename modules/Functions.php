<?php


function hexVal($in)
	{
	/** FIXME: nicht unbedingt geschickt */
	$result = preg_replace('/[^0-9a-fA-F]/', '', $in);
	return (empty($result) ? 0 : $result);
	}

function mTime()
	{
	$ms = explode(' ', microtime());
	return ((float) $ms[0] + (float) $ms[1]);
	}

/**
* Wandle $number in natrliche Zahl (inkl. 0) um
*/
function nat($number)
	{
	return ($number < 0 ? 0 : floor($number));
	}

function unhtmlspecialchars($string)
	{
	$string = str_replace ( '&amp;', '&', $string );
	$string = str_replace ( '&quot;', '"', $string );
	$string = str_replace ( '&lt;', '<', $string );
	$string = str_replace ( '&gt;', '>', $string );

	return $string;
	}

/**
* gibt die Tageszeit aus
* FIXME: Ist das hier so geschickt?
* @param &$time Unix-Zeitstempel
*/
function formatDate($time)
	{
	if (empty($time))
		{
		return '';
		}

	$return = date('j.n.Y \u\m G:i', $time);

	if (date('zY',$time) == date('zY'))
		{
		$return = '<strong>'.$return.'</strong>';
		}

	return $return;
	}

function cutString($string, $length)
	{
	return (strlen($string) > $length ? mb_substr($string, 0, ($length-3), 'UTF-8').'...' : $string);
	}

function gzdecode($string)
	{
	return gzinflate(substr($string, 10));
	}

?>
