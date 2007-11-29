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
class AdminCats extends AdminForm{

private $cats = array();

protected function setForm()
	{
	$this->setValue('title', 'Kategorien');

	$this->addSubmit('Speichern');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name,
				position
			FROM
				cats
			WHERE
				boardid = ?
			ORDER BY
				position
			');
		$stm->bindInteger($this->Board->getId());

		foreach ($stm->getRowSet() as $cat)
			{
			$cats = $stm->getNumRows();
			$this->addOutput
				(
				AdminFunctions::buildPositionMenu('category['.$cat['id'].'][position]', $cats, $cat['position']).'
				<input type="text" name="category['.$cat['id'].'][name]" size="74" value="'.$cat['name'].'" />
				<a href="?page=AdminForums;id='.$this->Board->getId().';cat='.$cat['id'].'"><span class="button">Foren</span></a>
				<a href="?page=AdminCatsDel;id='.$this->Board->getId().';cat='.$cat['id'].'"><span class="button" style="background-color:#CC0000">löschen</span></a>
				<br /><br />
				');
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$cats = 0;
		}

	$this->addOutput
		(
		AdminFunctions::buildPositionMenu('newposition', $cats+1, $cats+1).'
		<input type="text" name="newname" size="74" value="" />
		');
	}

protected function checkForm()
	{
	try
		{
		$this->cats = $this->Io->getArray('category');
		}
	catch (IoRequestException $e)
		{
		if ($this->Io->isEmpty('newname'))
			{
			$this->showWarning('Keine Kategorien angegeben.');
			}
		else
			{
			return;
			}
		}

	foreach($this->cats as $id => $cat)
		{
		if (empty($id))
			{
			$this->showWarning('Keine Kategorie-ID angegeben.');
			}

		if (empty($cat['position']))
			{
			$this->showWarning('Keine Kategorie-Position angegeben.');
			}

		if (empty($cat['name']))
			{
			$name = trim($cat['name']);
			$this->showWarning('Kein Kategorie-Name angegeben.');
			}
		else
			{
			$name = trim($cat['name']);
			if (empty($name))
				{
				$name = trim($cat['name']);
				$this->showWarning('Kein Kategorie-Name angegeben.');
				}
			}
		}
	}

protected function sendForm()
	{
	if (!empty($this->cats))
		{
		$stm = $this->DB->prepare
			('
			UPDATE
				cats
			SET
				position = ?,
				name = ?
			WHERE
				boardid = ?
				AND id = ?'
			);

		foreach($this->cats as $id => $cat)
			{
			if (isset($cat['position']) && isset($cat['name']) && isset($id))
				{
				$stm->bindInteger($cat['position']);
				$stm->bindString(htmlspecialchars($cat['name']));
				$stm->bindInteger($this->Board->getId());
				$stm->bindInteger($id);
				$stm->execute();
				}
			}
		$stm->close();
		}

	if (!$this->Io->isEmptyString('newname'))
		{
		$stm = $this->DB->prepare
			('
			INSERT INTO
				cats
			SET
				position = ?,
				name = ?,
				boardid = ?'
			);

		$stm->bindInteger($this->Io->isEmpty('newposition') ? 0 : $this->Io->getInt('newposition'));
		$stm->bindString($this->Io->getHtml('newname'));
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();
		}

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminCats');
	}

}


?>