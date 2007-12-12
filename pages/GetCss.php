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
class GetCss extends GetFile{

public function prepare()
	{
	$this->exitIfCached();
	}

public function show()
	{
	if (!($css = $this->ObjectCache->getObject('LL:GetCss:Css:'.$this->Io->getInt('id'))))
		{
		$this->initDB();
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
			$this->Io->setStatus(Io::NOT_FOUND);
			$this->showWarning('Datei nicht gefunden');
			}
		$this->ObjectCache->addObject('LL:GetCss:Css:'.$this->Board->getId(), $css, 60*60);
		}

	$this->Io->setContentType('Content-Type: text/css; charset=UTF-8');
	$this->Io->out($css);
	}

}

?>