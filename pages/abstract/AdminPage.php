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
abstract class AdminPage extends Page{


public function __construct()
	{
	parent::__construct();

	if (!$this->User->isAdmin())
		{
		//$this->showWarning('Zutritt verboten!');
		$this->Output->redirect('Forums');
		}
	}

protected function makeMenu()
	{
	$menu =	'<a href="?page=Forums;id='.$this->Board->getId().'"><span class="button" id="start">Übersicht</span></a>
	<a href="?page=AdminSettings;id='.$this->Board->getId().'"><span class="button" id="settings">Einstellungen</span></a>'.($this->User->isLevel(User::ROOT) ? ' <a href="?page=AdminGlobalSettings;id='.$this->Board->getId().'"><span class="button">Globale Einstellungen</span></a>' : '').'
	<a href="?page=AdminCats;id='.$this->Board->getId().'"><span class="button">Kategorien &amp; Foren</span></a>
	<a href="?page=AdminDesign;id='.$this->Board->getId().'"><span class="button">Layout &amp; Design</span></a>
	<a href="?page=AdminTags;id='.$this->Board->getId().'"><span class="button">Tags</span></a>
	<a href="?page=Logout;id='.$this->Board->getId().'"><span class="button" id="logout">Abmelden</span></a>';

	return $menu;
	}
}

?>