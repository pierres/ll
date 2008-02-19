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
class Impressum extends Page{


public function prepare()
	{
	$this->setValue('title', 'Impressum');

	$stm = $this->DB->prepare
		('
		SELECT
			admin_name,
			admin_email,
			admin_address,
			admin_tel,
			description
		FROM
			boards
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->Board->getId());
	$board = $stm->getRow();
	$stm->close();

	$body = '
		<table class="frame" style="width:50%;">
			<tr>
				<td class="title">
					Impressum
				</td>
			</tr>
			<tr>
				<td class="main">
					<table style="margin:10px;padding:20px;width:80%;">
						<tr>
							<td colspan="2" style="padding:10px;font-weight:bold;font-size:14px;text-align:center;">Inhaltlich verantwortlich</td>
						</tr>
						<tr>
							<td colspan="2" style="padding-bottom:10px;padding-right:20px;font-weight:bold;"><a href="?page=ShowUser;id='.$this->Board->getId().';user='.$this->Board->getAdmin().'" class="link">'.$board['admin_name'].'</a></td>
						</tr>
						<tr>
							<td style="padding-right:20px;vertical-align:top;font-weight:bold;">Adresse</td>
							<td>'.$board['admin_address'].'</td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">E-Mail</td>
							<td>'.$board['admin_email'].'</td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">Telefon</td>
							<td>'.$board['admin_tel'].'</td>
						</tr>

						<tr>
							<td colspan="2" style="padding:10px">'.$board['description'].'</td>
						</tr>
					
						<tr>
							<td colspan="2" style="padding:10px;font-weight:bold;font-size:14px;text-align:center;">Technik</td>
						</tr>
						<tr>
							<td colspan="2" style="padding-bottom:10px;padding-right:20px;font-weight:bold;"><a href="?page=ShowUser;id='.$this->Board->getId().';user=486" class="link">Pierre Schmitz</a></td>
						</tr>
						<tr>
							<td style="padding-right:20px;vertical-align:top;font-weight:bold;">Adresse</td>
							<td>Clemens-August-Straße 76<br />
							53115 Bonn</td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">E-Mail</td>
							<td>pschmitz&#64;laber-land.de</td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">Telefon</td>
							<td>0228&nbsp;9716608</td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">Mobil</td>
							<td>0160&nbsp;95269831</td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">Jabber</td>
							<td>pierre&#64;jabber.laber-land.de</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		';

	$this->setValue('body', $body);
	}
}


?>
