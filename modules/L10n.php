<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class L10n extends Modul {

public function __construct()
	{
	setlocale(LC_ALL, $this->Settings->getValue('locale'));
	date_default_timezone_set($this->Settings->getValue('timezone'));

	if (function_exists('bindtextdomain'))
		{
		bindtextdomain('LL', 'l10n');
		textdomain('LL');
		}
	}

public function getText($text)
	{
	if (function_exists('gettext'))
		{
		return gettext($text);
		}
	else
		{
		return $text;
		}
	}

public function getDate($timestamp = null)
	{
	return strftime('%x', $timestamp);
	}

public function getTime($timestamp = null)
	{
	return strftime('%X', $timestamp);
	}

public function getDateTime($timestamp = null)
	{
	return strftime('%c', $timestamp);
	}

}

?>