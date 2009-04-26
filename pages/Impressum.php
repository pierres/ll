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
	$this->setTitle('Impressum');

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
		<div id="brd-main" class="main">
			<div class="main-head">
					Impressum
			</div>
			<div class="main-content frm paged-head">
				<div class="paging">
					<a href="'.$this->Output->createUrl('ShowUser', array('user' => $this->Board->getAdmin())).'" class="">'.$board['admin_name'].'</a><br />
					<br />
					<b>Adresse</b><br />
					'.$board['admin_address'].'<br />
					<br />
					<b>E-Mail</b><br />
					'.$board['admin_email'].'<br />
					<br />
					<b>Telefon</b><br />
					'.$board['admin_tel'].'<br />
					<br />
					<br />
					'.$board['description'].'
				</div>
			</div>		
		</div>
		';

	$this->setBody($body);
	}
}


?>
