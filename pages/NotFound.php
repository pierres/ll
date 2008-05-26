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
/** TODO Dies kann man auch benutzerfreundlicher realisieren */
class NotFound extends Page{


public function prepare()
	{
	$search = urlencode(trim(preg_replace('/\W/', ' ',  str_replace('.php', '', $_SERVER["REQUEST_URI"]))));
	$this->Io->redirect('Search', 'search='.$search);
	}

}

?>