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
		$this->showFailure('Zutritt verboten!');
		}

	if ($this->User->isLevel(User::ROOT))
		{
		$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('AdminGlobalSettings').'">Globale Einstellungen</a>');
		}
	$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('AdminSettings').'">Einstellungen</a>');
	$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('AdminCats').'">Kategorien &amp; Foren</a>');
	$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('AdminDesign').'">Layout &amp; Design</a>');
	}

}

?>