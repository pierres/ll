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
class AdminGlobalSettings extends AdminPage{


public function prepare()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	$body=
	'
	<table class="frame" style="width:80%">
	<tr>
		<td class="title" colspan="2">
			Globale Einstellungen
		</td>
	</tr>
	<tr>
		<td class="main">
			<img src="images/dev.png" />
		</td>
		<td class="main">
			<ul>
				<li style="margin:20px;">
				<a href="?page=AdminDeletedThreads;id='.$this->Board->getId().'"><span class="button">Gelöschte Themen</span></a>
				</li>
				<li style="margin:20px;">
				<a href="?page=AdminDeletedPosts;id='.$this->Board->getId().'"><span class="button">Gelöschte Beiträge</span></a>
				</li>
				<li style="margin:20px;">
				<a href="?page=AdminRenameUser;id='.$this->Board->getId().'"><span class="button">Benutzer umbenennen</span></a>
				</li>
				<li style="margin:20px;">
				<a href="?page=AdminCreateBoard;id='.$this->Board->getId().'"><span class="button">Board erstellen</span></a>
				</li>
				<li style="margin:20px;">
				<a href="?page=AdminDelBoard;id='.$this->Board->getId().'"><span class="button">Board löschen</span></a>
				</li>
				<li style="margin:20px;">
				<a href="?page=AdminForumsMerge;id='.$this->Board->getId().'"><span class="button">Foren zusammenlegen</span></a>
				</li>
			</ul>
		</td>
	</tr>
	</table>
	';

	$this->setValue('title', 'Globale Einstellungen');
	$this->setValue('body', $body);
	}


}


?>