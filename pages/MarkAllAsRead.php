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
/** FIXME: Nicht geschützt via Form */
class MarkAllAsRead extends Page{

/** TODO: Dies kann durch direkten Zugriff auf die DB noch optimiert werden */
public function prepare()
	{
	if ($this->User->isOnline())
		{
		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					id,
					lastdate
				FROM
					threads
				WHERE
					forumid != 0
					AND lastdate > ?'
				);
			$stm->bindInteger($this->Input->getTime() - $this->Settings->getValue('log_timeout'));

			foreach ($stm->getRowSet() as $thread)
				{
				$this->Log->insert($thread['id'], $thread['lastdate']);
				}
			$stm->close();
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			}
		}
	}

public function show()
	{
	$this->Output->redirect('Forums');
	}

}

?>