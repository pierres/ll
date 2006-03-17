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
				AdminFunctions::buildPositionMenu('position['.$cat['id'].']', $cats, $cat['position']).'
				<input type="text" name="name['.$cat['id'].']" size="74" value="'.$cat['name'].'" />
				<a href="?page=AdminForums;id='.$this->Board->getId().';cat='.$cat['id'].'"><span class="button">Foren</span></a>
				<a href="?page=AdminCatsDel;id='.$this->Board->getId().';cat='.$cat['id'].'"><span class="button" style="background-color:#CC0000">löschen</span></a>
				<br /><br />
				');
			}
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
	/** FIXME */
	}

protected function sendForm()
	{
	/** FIXME */
	$cats = $this->Io->getArray();

	foreach($cats as $cat => $value)
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
		$stm->bindInteger($value['position']);
		$stm->bindString(htmlspecialchars($value['name']));
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($cat);
		$stm->execute();
		}


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

		$stm->bindInteger($this->Io->isEmpty('position') ? 0 : $this->Io->getInt('newposition'));
		$stm->bindString($this->Io->getHtml('newname'));
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		}

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminCats');
	}

}


?>