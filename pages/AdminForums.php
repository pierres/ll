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

class AdminForums extends AdminForm {

private $cat = 0;

protected function setForm()
	{
	try
		{
		$this->cat = $this->Input->Get->getInt('cat');
		}
	catch (RequestException $e)
		{
		$this->showFailure('Keine Kategorie angegeben');
		}

	$this->setTitle('Foren');

	$this->add(new SubmitButtonElement('Speichern'));

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forums.id,
				forums.name,
				forums.description,
				forums.boardid,
				forums.mods,
				forum_cat.position
			FROM
				forums,
				forum_cat,
				cats
			WHERE
				forum_cat.forumid = forums.id
				AND forum_cat.catid = cats.id
				AND forum_cat.catid = ?
				AND cats.boardid = ?
			ORDER BY
				forum_cat.position
			');
		$stm->bindInteger($this->cat);
		$stm->bindInteger($this->Board->getId());
		$forums = $stm->getRowSet();
		$totalForums = $stm->getNumRows();
		}
	catch (DBNoDataException $e)
		{
		$forums = array();
		$totalForums = 0;
		}

	foreach ($forums as $forum)
		{
		if ($forum['boardid'] == $this->Board->getId())
			{
			$nameInput = new TextInputElement('forums['.$forum['id'].'][name]', $forum['name'], 'Name')
			$nameInput->setRequired(false);
			$nameInput->setMinLength(0);
			$this->add($nameInput);
			$descInput = new TextareaInputElement('forums['.$forum['id'].'][description]', $forum['description'], 'Beschreibung');
			$descInput->setRequired(false);
			$descInput->setMinLength(0);
			$this->add($descInput);

			$positionMenu = new SelectInputElement('forums['.$forum['id'].'][position]', 'Position');
			for ($i = 1; $i <= $totalForums; $i++)
				{
				$positionMenu->addOption($i, $i);
				}
			$positionMenu->setSelected($forum['position']);
			$positionMenu->setSize(1);
			$positionMenu->setRequired(false);
			$positionMenu->setMinLength(0);
			$this->add($positionMenu);

			$this->add(new LabeledElement
				('', '<a href="'.$this->Output->createUrl('AdminForumsMove', array('forum' => $forum['id'])).'"><span class="button">verschieben</span></a>
				<a href="'.$this->Output->createUrl('AdminForumsDel', array('forum' => $forum['id'])).'"><span class="button" style="background-color:#CC0000">löschen</span></a>'));

			if ($this->User->isLevel(User::ROOT))
				{
				$this->add(new LabeledElement
				('', '<a href="'.$this->Output->createUrl('AdminGlobalForumsMove', array('forum' => $forum['id'])).'"><span class="button" style="background-color:#CC6600">global verschieben</span></a>'));
				}

			$this->add(new LabeledElement
				('', '	'.$this->getMods($forum['mods']).'
					<br />
					<a href="'.$this->Output->createUrl('AdminForumsMods', array('forum' => $forum['id'])).'"><span class="button">Moderatoren</span></a>'));
			}
		else
			{
			$this->add(new LabeledElement('Name', $forum['name']));
			$this->add(new LabeledElement('Beschreibung', $forum['description']));

			$positionMenu = new SelectInputElement('forums['.$forum['id'].'][position]', 'Position');
			for ($i = 1; $i <= $totalForums; $i++)
				{
				$positionMenu->addOption($i, $i);
				}
			$positionMenu->setSelected($forum['position']);
			$positionMenu->setSize(1);
			$positionMenu->setRequired(false);
			$positionMenu->setMinLength(0);
			$this->add($positionMenu);

			$this->add(new LabeledElement
				('', '<a href="'.$this->Output->createUrl('AdminForumsMove', array('forum' => $forum['id'])).'"><span class="button">verschieben</span></a>
				<a href="'.$this->Output->createUrl('AdminForumsDelEx', array('forum' => $forum['id'])).'"><span class="button" style="background-color:#CC6600">löschen</span></a>'));


			$this->add(new LabeledElement
				('', '	'.$this->getMods($forum['mods']).' '));
			}
		}
	$stm->close();

	$nameInput = new TextInputElement('newname', '', 'Name');
	$nameInput->setRequired(false);
	$nameInput->setMinLength(0);
	$this->add($nameInput);
	
	$descInput = new TextareaInputElement('newdescription', '', 'Beschreibung');
	$descInput->setRequired(false);
	$descInput->setMinLength(0);
	$this->add($descInput);

	$positionMenu = new SelectInputElement('newposition', 'Position');
	for ($i = 1; $i <= $totalForums+1; $i++)
		{
		$positionMenu->addOption($i, $i);
		}
	$positionMenu->setSelected($totalForums+1);
	$positionMenu->setSize(1);
	$positionMenu->setRequired(false);
	$positionMenu->setMinLength(0);
	$this->add($positionMenu);

	$this->add(new LabeledElement
		('', '<a href="'.$this->Output->createUrl('AdminForumsEx', array('cat' => $this->cat)).'"><span class="button">externe Foren hinzufügen</span></a>'));

	$this->setParam('cat', $this->cat);
	}

private function getMods($group)
	{
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
		$stm->bindInteger($group);

		foreach($stm->getColumnSet() as $mod)
			{
			$mods[] = $mod;
			}

		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		}

	return implode('<br />',$mods);
	}

protected function checkForm()
	{
	/** TODO Eingaben genauer prüfen (vor allem auf LeerStrings) */
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				cats
			WHERE
				id = ?
				AND boardid = ?'
			);
		$stm->bindInteger($this->cat);
		$stm->bindInteger($this->Board->getId());
		$stm->getColumn();
		$stm->close();
		}
	catch(DBNoDataException $e)
		{
		$stm->close();
		$this->Output->redirect('AdminCats');
		}
	}

protected function sendForm()
	{
	try
		{
		$forums = $this->Input->Post->getArray('forums');
		}
	catch (RequestException $e)
		{
		if ($this->Input->Post->isEmptyString('newname'))
			{
			$this->redirect();
			}
		}

	if (!empty($forums))
		{
		$stm = $this->DB->prepare
			('
			UPDATE
				forums
			SET
				name = ?,
				description = ?
			WHERE
				boardid = ?
				AND id = ?'
			);

		$stm2 = $this->DB->prepare
			('
			UPDATE
				forum_cat
			SET
				position = ?
			WHERE
				forumid = ?
				AND catid = ?'
			);
		foreach($forums as $id => $forum)
			{
			if(isset($forum['name']) && isset($forum['description']) && isset($id))
				{
				$stm->bindString(htmlspecialchars($forum['name']));
				$stm->bindString(htmlspecialchars($forum['description']));
				$stm->bindInteger($this->Board->getId());
				$stm->bindInteger($id);
				$stm->execute();
				}

			$stm2->bindInteger($forum['position']);
			$stm2->bindInteger($id);
			$stm2->bindInteger($this->cat);
			$stm2->execute();
			}
		$stm->close();
		$stm2->close();
		}

	if (!$this->Input->Post->isEmptyString('newname'))
		{
		$stm = $this->DB->prepare
			('
			INSERT INTO
				forums
			SET
				name = ?,
				description = ?,
				boardid = ?'
			);
		$stm->bindString($this->Input->Post->getHtml('newname'));
		$stm->bindString($this->Input->Post->getHtml('newdescription'));
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();

		$stm = $this->DB->prepare
			('
			INSERT INTO
				forum_cat
			SET
				forumid = LAST_INSERT_ID(),
				position = ?,
				catid = ?'
			);
		$stm->bindInteger($this->Input->Post->getInt('newposition'));
		$stm->bindInteger($this->cat);
		$stm->execute();
		$stm->close();
		}

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('AdminForums', array('cat' => $this->cat));
	}


}


?>