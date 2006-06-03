<?php

class AdminCats extends AdminForm{



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
	/** TODO Prüfung auf vernünftige Werte */
	}

protected function sendForm()
	{
	try
		{
		$cats = $this->Io->getArray('category');
		}
	catch (IoRequestException $e)
		{
		$this->showFailure($e->getMessage());
		}

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

	foreach($cats as $id => $cat)
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


	if (!$this->Io->isEmpty('newname'))
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