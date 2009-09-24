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

class AdminForumsMods extends AdminForm {


private $forum = 0;
private $mods = array();
private $group;

protected function setForm()
	{
	$this->setTitle('Moderatoren');

	$this->add(new SubmitButtonElement('Speichern'));

	try
		{
		$this->forum = $this->Input->Get->getInt('forum');

		$stm = $this->DB->prepare
			('
			SELECT
				mods
			FROM
				forums
			WHERE
				id = ?
				AND boardid = ?'
			);
		$stm->bindInteger($this->forum);
		$stm->bindInteger($this->Board->getId());
		$this->group = $stm->getColumn();
		$stm->close();
		}
	catch(Exception $e)
		{
		$stm->close();
		$this->Output->redirect('AdminCats');
		}

	$this->setParam('forum', $this->forum);

	$mods = array();
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				users.name
			FROM
				users,
				user_group
			WHERE
				user_group.userid = users.id
				AND user_group.groupid = ?'
			);
		$stm->bindInteger($this->group);

		foreach($stm->getColumnSet() as $mod)
			{
			$mods[] = $mod;
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	$this->add(new TextareaInputElement('mods', implode("\n", $mods), 'Moderatoren'));
	}

protected function checkForm()
	{
	if(!$this->Input->Post->isEmptyString('mods'))
		{
		$mods = array_map('trim', explode("\n", $this->Input->Post->getString('mods')));

		foreach ($mods as $mod)
			{
			try
				{
				$this->mods[] = AdminFunctions::getUserId($mod);
				}
			catch (DBNoDataException $e)
				{
				$this->showWarning('Moderator "'.htmlspecialchars($mod).'" nicht gefunden');
				}
			}
		}
	}

protected function sendForm()
	{
	$this->updateMods();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('AdminForumsMods', array('forum' => $this->forum));
	}

private function updateMods()
	{
	if ($this->group == 0 && !empty($this->mods))
		{
		$this->DB->execute('LOCK TABLES user_group WRITE, forums WRITE');
		try
			{
			$groupid = $this->DB->getColumn('SELECT MAX(groupid) FROM user_group') + 1;
			}
		catch (DBNoDataException $e)
			{
			$groupid = 1;
			}

		$stm = $this->DB->prepare
			('
			UPDATE
				forums
			SET
				mods = ?
			WHERE
				boardid = ?
				AND id = ?'
			);
		$stm->bindInteger($groupid);
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->forum);
		$stm->execute();
		$stm->close();
		}
	else
		{
		$groupid = $this->group;

		$stm = $this->DB->prepare
			('
			DELETE FROM
				user_group
			WHERE
				groupid = ?'
			);
		$stm->bindInteger($groupid);
		$stm->execute();
		$stm->close();
		}

	$stm = $this->DB->prepare
		('
		INSERT INTO
			user_group
		SET
			groupid = ?,
			userid = ?'
		);

	foreach($this->mods as $mod)
		{
		$stm->bindInteger($groupid);
		$stm->bindInteger($mod);
		$stm->execute();
		}

	$stm->close();

	$this->DB->execute('UNLOCK TABLES');
	}

}

?>