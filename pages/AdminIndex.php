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
class AdminIndex extends AdminPage{


public function prepare()
	{
	$body=
	'
	<table class="frame" style="width:80%">
	<tr>
		<td class="title" colspan="2">
			Administration
		</td>
	</tr>
	<tr>
		<td class="main">
			<img src="images/dev.png" />
		</td>
		<td class="main">
			<h1>Willkommen zur Administration, '.$this->User->getName().'!</h1>
			<p>
			In diesem Bereich kannst Du Dein Board verwalten. Hier können neue Kategorien und Foren eingerichtet und verändert werden, aber auch das komplette Aussehen Deines Boards kannst Du hier mit wenigen Handgriffen anpassen.
			</p><p>
			Solltest Du Probleme mit einer Funktion haben, so konsultiere die Hilfe oder frage im Support-Forum nach.
			</p>
		</td>
	</tr>
	</table>
	';

	$this->setValue('title', 'Administration');
	$this->setValue('body', $body);
	}


}


?>