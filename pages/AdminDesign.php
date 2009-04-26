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

class AdminDesign extends AdminPage {


public function prepare()
	{
	$body=
	'
	<table class="frame" style="width:80%">
	<tr>
		<td class="title" colspan="2">
			Layout &amp; Design
		</td>
	</tr>
	<tr>
		<td class="main">
			<img src="images/dev.png" />
		</td>
		<td class="main">
			<ul>
				<li style="margin:20px;">
				<a href="'.$this->Output->createUrl('AdminHtml').'"><span class="button">HTML-Vorlage</span></a>
				Hier kannst Du die HTML-Vorlage für das Forum bearbeiten. Achte auf XHTML 1.1-Kompatibilität!
				</li>
				<li style="margin:20px;">
				<a href="'.$this->Output->createUrl('AdminCss').'"><span class="button">CSS-Vorlage</span></a>
				Farben, Schriften, Bilder etc. werden mittels Stylesheet festgelegt.
				</li>
			</ul>
		</td>
	</tr>
	</table>
	';

	$this->setTitle('Layout &amp; Design');
	$this->setBody($body);
	}


}


?>