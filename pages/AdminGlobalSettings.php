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

class AdminGlobalSettings extends AdminPage {


public function prepare()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	$this->setTitle('Globale Einstellungen');

	$body= '
		<div class="box">
			<ul>
				<li><a href="'.$this->Output->createUrl('AdminDeletedThreads').'">Gelöschte Themen</a></li>
				<li><a href="'.$this->Output->createUrl('AdminDeletedPosts').'">Gelöschte Beiträge</a></li>
				<li><a href="'.$this->Output->createUrl('AdminRenameUser').'">Benutzer umbenennen</a></li>
				<li><a href="'.$this->Output->createUrl('AdminCreateBoard').'">Board erstellen</a></li>
				<li><a href="'.$this->Output->createUrl('AdminDelBoard').'">Board löschen</a></li>
				<li><a href="'.$this->Output->createUrl('AdminForumsMerge').'">Foren zusammenlegen</a></li>
			</ul>
		</div>
		';

	$this->setBody($body);
	}


}


?>
