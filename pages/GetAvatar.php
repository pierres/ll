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
class GetAvatar extends GetFile{

private $user = 0;

protected function getParams()
	{
	try
		{
		$this->user = $this->Io->getInt('user');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('kein Benutzer angegeben');
		}
	}

public function show()
	{
	$this->initDB();
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				type,
				name,
				content,
				size
			FROM
				avatars
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->user);
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Io->setStatus(Io::NOT_FOUND);
		$this->showWarning('Datei nicht gefunden');
		}

	$this->sendInlineFile($data['type'], $data['name'], $data['size'], $data['content']);
	}
}

?>