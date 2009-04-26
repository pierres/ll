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

abstract class AdminForm extends Form {


public function __construct()
	{
	AdminPage::__construct();
	}

protected function getMenu()
	{
	$menu =	'<div id="brd-navlinks"><ul>
	<li id="navindex"><a href="'.$this->Output->createUrl('Forums').'"><span>Übersicht</span></a></li>
	<li><a href="'.$this->Output->createUrl('AdminSettings').'"><span>Einstellungen</span></a>'.($this->User->isLevel(User::ROOT) ? ' <a href="'.$this->Output->createUrl('AdminGlobalSettings').'"><span>Globale Einstellungen</span></a>' : '').'</li>
	<li><a href="'.$this->Output->createUrl('AdminCats').'"><span>Kategorien &amp; Foren</span></a></li>
	<li><a href="'.$this->Output->createUrl('AdminDesign').'"><span>Layout &amp; Design</span></a></li>
	<li><a href="'.$this->Output->createUrl('Logout').'"><span id="logout">Abmelden</span></a></li>
	</ul></div>';

	return $menu;
	}

}


?>