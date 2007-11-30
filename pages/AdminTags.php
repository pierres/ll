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
class AdminTags extends AdminForm{

private $tags = array();

protected function setForm()
	{
	$this->setValue('title', 'Tags');

	$this->addSubmit('Speichern');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name
			FROM
				tags
			WHERE
				boardid = ?
			');
		$stm->bindInteger($this->Board->getId());

		foreach ($stm->getRowSet() as $tag)
			{
			$this->addOutput
				('
				<input type="text" name="tag['.$tag['id'].'][name]" size="74" value="'.$tag['name'].'" />
				<a href="?page=AdminTagsDel;id='.$this->Board->getId().';tag='.$tag['id'].'"><span class="button" style="background-color:#CC0000">löschen</span></a>
				<br /><br />
				');
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		}

	$this->addOutput('<input type="text" name="newname" size="74" value="" />');
	}

protected function checkForm()
	{
	try
		{
		$this->tags = $this->Io->getArray('tag');
		}
	catch (IoRequestException $e)
		{
		if ($this->Io->isEmpty('newname'))
			{
			$this->showWarning('Kein Tag angegeben.');
			}
		else
			{
			return;
			}
		}

	foreach($this->tags as $id => $tag)
		{
		if (empty($id))
			{
			$this->showWarning('Keine Tag-ID angegeben.');
			}

		if (empty($tag['name']))
			{
			$name = trim($tag['name']);
			$this->showWarning('Kein Tag-Name angegeben.');
			}
		else
			{
			$name = trim($tag['name']);
			if (empty($name))
				{
				$name = trim($tag['name']);
				$this->showWarning('Kein Tag-Name angegeben.');
				}
			}
		}
	}

protected function sendForm()
	{
	if (!empty($this->tags))
		{
		$stm = $this->DB->prepare
			('
			UPDATE
				tags
			SET
				name = ?
			WHERE
				boardid = ?
				AND id = ?'
			);

		foreach($this->tags as $id => $tag)
			{
			if (isset($tag['name']) && isset($id))
				{
				$stm->bindString(htmlspecialchars($tag['name']));
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
				tags
			SET
				name = ?,
				boardid = ?'
			);

		$stm->bindString($this->Io->getHtml('newname'));
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();
		}

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminTags');
	}

}


?>