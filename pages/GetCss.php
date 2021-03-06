<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/
class GetCss extends GetFile {


public function show()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				css
			FROM
				boards
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->Board->GetId());
		$css = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Output->setStatus(Output::NOT_FOUND);
		$this->showWarning('Datei nicht gefunden');
		}

	$this->compression = true;
	$this->sendInlineFile('text/css; charset=UTF-8', $this->Board->GetId().'.css', $css);
	}

}

?>