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

class AdminCats extends AdminForm {

private $cats = array();

protected function setForm()
	{
	$this->setTitle('Kategorien');

	$this->add(new SubmitButtonElement('Speichern'));

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
		$cats = $stm->getRowSet();
		$catnum = $stm->getNumRows();

		foreach ($cats as $cat)
			{
			$this->add(new TextInputElement('category['.$cat['id'].'][name]', $cat['name'], 'Name'));

			$positionMenu = new SelectInputElement('category['.$cat['id'].'][position]', 'Position');
			for ($i = 1; $i <= $catnum; $i++)
				{
				$positionMenu->addOption($i, $i);
				}
			$positionMenu->setSelected($cat['position']);
			$positionMenu->setSize(1);
			$this->add($positionMenu);

			$this->add(new LabeledElement
				('', '<a href="'.$this->Output->createUrl('AdminForums', array('cat' => $cat['id'])).'"><span class="button">Foren</span></a>
				<a href="'.$this->Output->createUrl('AdminCatsDel', array('cat' => $cat['id'])).'"><span class="button" style="background-color:#CC0000">löschen</span></a>'));

			$this->add(new DividerElement());
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$catnum = 0;
		}

	$this->add(new TextInputElement('newname', '', 'Neue Kategorie'));
	$positionMenu = new SelectInputElement('newposition', 'Position');
	for ($i = 1; $i <= $catnum+1; $i++)
		{
		$positionMenu->addOption($i, $i);
		}
	$positionMenu->setSelected($catnum+1);
	$positionMenu->setSize(1);
	$this->add($positionMenu);
	}

protected function checkForm()
	{
	try
		{
		$this->cats = $this->Input->Post->getArray('category');
		}
	catch (RequestException $e)
		{
		if ($this->Input->Post->isEmptyString('newname'))
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
			$this->showWarning('Kein Kategorie-Name angegeben.');
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

	if (!$this->Input->Post->isEmptyString('newname'))
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

		$stm->bindInteger($this->Input->Post->isEmptyString('newposition') ? 0 : $this->Input->Post->getInt('newposition'));
		$stm->bindString($this->Input->Post->getHtml('newname'));
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();
		}

	$this->Output->redirect('AdminCats');
	}

}


?>